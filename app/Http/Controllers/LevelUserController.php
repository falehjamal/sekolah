<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\StoreLevelUserRequest;
use App\Http\Requests\Auth\UpdateLevelUserRequest;
use App\Models\Tenant\Level;
use App\Models\Tenant\Menu;
use App\Models\Tenant\Role;
use App\Services\Menu\MenuTreeBuilder;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class LevelUserController extends Controller
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

        $this->middleware('permission:auth.roles.view')->only('index');
        $this->middleware('permission:auth.roles.manage')->only(['store', 'update', 'destroy', 'show']);
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->datatable();
        }

        return view('auth.levels.index', [
            'canManage' => $this->canManageRoles(),
            'menuTree' => $this->menuTreeOptions(),
        ]);
    }

    public function datatable(): JsonResponse
    {
        $levels = Level::query()
            ->withCount('users')
            ->with(['role.menus'])
            ->orderBy('name');

        return DataTables::of($levels)
            ->addIndexColumn()
            ->addColumn('info_card', function (Level $row): string {
                return sprintf(
                    '<div class="table-card">
                        <div class="table-avatar avatar-purple">%s</div>
                        <div class="table-card__body">
                            <div class="table-card__title">%s</div>
                            <ul class="table-meta">
                                <li><span>Slug</span>%s</li>
                                <li><span>Pengguna</span>%s</li>
                            </ul>
                        </div>
                    </div>',
                    strtoupper(Str::substr($row->name, 0, 1)),
                    e($row->name),
                    e($row->slug),
                    $row->users_count
                );
            })
            ->addColumn('detail_card', function (Level $row): string {
                $description = $row->description ?: 'Belum ada deskripsi';

                return sprintf(
                    '<div class="table-stack">
                        <p class="mb-0">%s</p>
                    </div>',
                    e($description)
                );
            })
            ->addColumn('menu_badges', function (Level $row): string {
                $menus = ($row->role?->menus ?? collect())
                    ->filter(static fn (Menu $menu) => $menu->parent_id !== null);

                if ($menus->isEmpty()) {
                    return '<span class="text-muted small">Belum ada menu anak</span>';
                }

                $badges = $menus->map(function ($menu) {
                    return sprintf(
                        '<div class="badge bg-label-primary mb-1 me-1">%s</div>',
                        e($menu->name)
                    );
                })->implode('');

                return '<div class="d-flex flex-wrap">'.$badges.'</div>';
            })
            ->addColumn('action', function (Level $row) {
                if (! $this->canManageRoles()) {
                    return '<span class="text-muted small">Tidak ada akses</span>';
                }

                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editLevel('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusLevel('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['info_card', 'detail_card', 'menu_badges', 'action'])
            ->make(true);
    }

    public function store(StoreLevelUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $connection = (new Level)->getConnectionName();
        $menuIds = $this->normalizeMenuIds($data['menu_ids'] ?? []);
        $permissions = $this->extractPermissionsFromMenus($menuIds);

        DB::connection($connection)->beginTransaction();

        try {
            $level = $this->persistLevel(new Level, $data);
            $role = $this->syncRole($level, $permissions);
            $this->syncMenuAccess($role, $menuIds);

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Level user berhasil dibuat.',
                'data' => $level,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan level user.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(Level $level): JsonResponse
    {
        $role = $this->findOrCreateRoleForLevel($level);
        $role?->loadMissing('permissions:name', 'menus:id');

        return response()->json([
            'success' => true,
            'data' => $level,
            'menus' => $role?->menus->pluck('id') ?? collect(),
        ]);
    }

    public function update(UpdateLevelUserRequest $request, Level $level): JsonResponse
    {
        $data = $request->validated();
        $connection = $level->getConnectionName();
        $menuIds = $this->normalizeMenuIds($data['menu_ids'] ?? []);
        $permissions = $this->extractPermissionsFromMenus($menuIds);

        DB::connection($connection)->beginTransaction();

        try {
            $originalSlug = $level->slug;
            $this->persistLevel($level, $data);
            $role = $this->syncRole($level, $permissions, $originalSlug);
            $this->syncMenuAccess($role, $menuIds);

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Level user berhasil diperbarui.',
                'data' => $level,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui level user.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Level $level): JsonResponse
    {
        if ($level->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Level masih digunakan oleh pengguna aktif.',
            ], 422);
        }

        $connection = $level->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $role = Role::query()->firstWhere('name', $level->slug);
            $level->delete();
            $role?->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Level user berhasil dihapus.',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus level user.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    protected function menuTreeOptions(): Collection
    {
        $menus = Menu::query()
            ->select(['id', 'parent_id', 'name', 'route_name', 'icon', 'sort_order'])
            ->where('is_active', true)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($menus->isEmpty()) {
            return collect();
        }

        $grouped = $menus->groupBy('parent_id');

        return $this->mapMenuOptions($grouped, null);
    }

    protected function mapMenuOptions(Collection $grouped, ?int $parentId): Collection
    {
        return $grouped
            ->get($parentId, collect())
            ->sortBy(function (Menu $menu) {
                $order = str_pad((string) $menu->sort_order, 5, '0', STR_PAD_LEFT);

                return $order.'-'.$menu->name;
            })
            ->map(function (Menu $menu) use ($grouped) {
                return [
                    'id' => (int) $menu->id,
                    'name' => $menu->name,
                    'route_name' => $menu->route_name,
                    'icon' => $menu->icon ?: null,
                    'children' => $this->mapMenuOptions($grouped, $menu->id)->values()->all(),
                ];
            })
            ->values();
    }

    protected function normalizeMenuIds(array $menuIds): array
    {
        return collect($menuIds)
            ->filter(static fn ($id) => is_numeric($id))
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function extractPermissionsFromMenus(array $menuIds): array
    {
        if (empty($menuIds)) {
            return [];
        }

        return Menu::query()
            ->whereIn('id', $menuIds)
            ->whereNotNull('permission_name')
            ->pluck('permission_name')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function persistLevel(Level $level, array $data): Level
    {
        $level->fill([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
        ])->save();

        return $level;
    }

    protected function syncRole(Level $level, array $permissions, ?string $originalSlug = null): Role
    {
        $role = $this->findOrCreateRoleForLevel($level, $originalSlug);

        if ($role->name !== $level->slug) {
            $role->name = $level->slug;
            $role->save();
        }

        $role->syncPermissions($permissions);

        return $role;
    }

    protected function syncMenuAccess(Role $role, array $menuIds): void
    {
        $role->menus()->sync($menuIds);
        $this->menuTreeBuilder->flushTenantCache();
    }

    protected function findOrCreateRoleForLevel(Level $level, ?string $previousSlug = null): Role
    {
        $role = null;

        if ($previousSlug !== null && $previousSlug !== $level->slug) {
            $role = Role::query()->firstWhere('name', $previousSlug);
        }

        if (! $role) {
            $role = Role::query()->firstWhere('name', $level->slug);
        }

        if (! $role) {
            $role = Role::query()->create([
                'name' => $level->slug,
                'guard_name' => 'web',
            ]);
        }

        return $role;
    }

    protected function canManageRoles(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->can('auth.roles.manage') ?? false;
    }
}
