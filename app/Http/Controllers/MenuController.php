<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\StoreMenuRequest;
use App\Http\Requests\Auth\UpdateMenuRequest;
use App\Models\Tenant\Menu;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Services\Menu\MenuTreeBuilder;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class MenuController extends Controller
{
    public function __construct(
        protected TenantConnectionManager $tenantConnection,
        protected MenuTreeBuilder $menuTreeBuilder
    ) {
        $this->middleware(function ($request, $next) {
            if (session()->has('tenant_connection')) {
                $this->tenantConnection->connectFromSession();
            }

            return $next($request);
        });

        $this->middleware('permission:auth.menus.view')->only('index');
        $this->middleware('permission:auth.menus.manage')->only(['store', 'update', 'destroy', 'show']);
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->datatable();
        }

        return view('auth.menu.index', [
            'menuOptions' => Menu::query()->orderBy('name')->get(['id', 'name']),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'permissions' => Permission::query()->orderBy('name')->get(['name']),
            'canManage' => $this->canManageMenus(),
        ]);
    }

    public function datatable(): JsonResponse
    {
        $menus = Menu::query()
            ->with(['parent', 'roles'])
            ->orderBy('sort_order')
            ->orderBy('name');

        return DataTables::of($menus)
            ->addIndexColumn()
            ->addColumn('info_card', function (Menu $row): string {
                $icon = $row->icon ?: 'bx bx-radio-circle';

                return sprintf(
                    '<div class="table-card">
                        <div class="table-avatar avatar-teal"><i class="%s text-white"></i></div>
                        <div class="table-card__body">
                            <div class="table-card__title">%s</div>
                            <div class="text-muted small">%s</div>
                        </div>
                    </div>',
                    e($icon),
                    e($row->name),
                    $row->route_name ? e($row->route_name) : 'Tidak memiliki route'
                );
            })
            ->addColumn('detail_card', function (Menu $row): string {
                $parent = $row->parent?->name ?? '-';

                return sprintf(
                    '<div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Parent</span>%s</li>
                            <li><span>Sort Order</span>%s</li>
                        </ul>
                    </div>',
                    e($parent),
                    $row->sort_order
                );
            })
            ->addColumn('permission_card', function (Menu $row): string {
                $permission = $row->permission_name ?: '-';
                $roles = $row->roles->pluck('name');

                $roleBadges = $roles->isEmpty()
                    ? '<span class="text-muted small">Belum diatur</span>'
                    : $roles->map(fn ($role) => '<span class="badge bg-label-info mb-1 me-1">'.e($role).'</span>')->implode('');

                return sprintf(
                    '<div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Permission</span>%s</li>
                        </ul>
                        <div class="d-flex flex-wrap mt-2">%s</div>
                    </div>',
                    e($permission),
                    $roleBadges
                );
            })
            ->addColumn('status_badge', function (Menu $row): string {
                $statusClass = $row->is_active ? 'bg-label-success' : 'bg-label-secondary';
                $statusLabel = $row->is_active ? 'Aktif' : 'Nonaktif';

                return sprintf(
                    '<div class="d-flex flex-column gap-1">
                        <span class="badge %s">%s</span>
                        <small class="text-muted">Guard: %s</small>
                    </div>',
                    $statusClass,
                    $statusLabel,
                    e($row->guard_name ?? 'web')
                );
            })
            ->addColumn('action', function (Menu $row) {
                if (! $this->canManageMenus()) {
                    return '<span class="text-muted small">Tidak ada akses</span>';
                }

                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editMenu('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusMenu('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['info_card', 'detail_card', 'permission_card', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(StoreMenuRequest $request): JsonResponse
    {
        $connection = (new Menu)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $menu = Menu::query()->create($request->validated());
            $menu->roles()->sync($request->input('role_ids', []));
            $this->flushMenuCache();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil ditambahkan.',
                'data' => $menu,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan menu.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(Menu $menu): JsonResponse
    {
        $menu->load('roles');

        return response()->json([
            'success' => true,
            'data' => $menu,
            'role_ids' => $menu->roles->pluck('id'),
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        if ((int) $request->input('parent_id') === $menu->getKey()) {
            return response()->json([
                'success' => false,
                'message' => 'Menu induk tidak boleh sama dengan menu yang diedit.',
            ], 422);
        }

        $connection = $menu->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $menu->update($request->validated());
            $menu->roles()->sync($request->input('role_ids', []));
            $this->flushMenuCache();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil diperbarui.',
                'data' => $menu,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui menu.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Menu $menu): JsonResponse
    {
        if ($menu->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Menu masih memiliki submenu. Hapus submenu terlebih dahulu.',
            ], 422);
        }

        $connection = $menu->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $menu->roles()->detach();
            $menu->delete();
            $this->flushMenuCache();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil dihapus.',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus menu.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    protected function canManageMenus(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->can('auth.menus.manage') ?? false;
    }

    protected function flushMenuCache(): void
    {
        $this->menuTreeBuilder->flushTenantCache();
    }
}
