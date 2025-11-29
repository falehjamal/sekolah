<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\StoreLevelUserRequest;
use App\Http\Requests\Auth\UpdateLevelUserRequest;
use App\Models\Tenant\Level;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Services\Tenant\TenantConnectionManager;
use App\Support\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class LevelUserController extends Controller
{
    public function __construct(protected TenantConnectionManager $tenantConnection)
    {
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
            'permissionGroups' => $this->permissionGroups(),
            'canManage' => $this->canManageRoles(),
        ]);
    }

    public function datatable(): JsonResponse
    {
        $levels = Level::query()
            ->withCount('users')
            ->with(['role.permissions'])
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
            ->addColumn('permission_badges', function (Level $row): string {
                $permissions = $row->role?->permissions ?? collect();

                if ($permissions->isEmpty()) {
                    return '<span class="text-muted small">Belum ada permission</span>';
                }

                $badges = $permissions->map(function ($permission) {
                    return '<span class="badge bg-label-primary mb-1 me-1">'.e(Str::headline($permission->name)).'</span>';
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
            ->rawColumns(['info_card', 'detail_card', 'permission_badges', 'action'])
            ->make(true);
    }

    public function store(StoreLevelUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $connection = (new Level)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $level = $this->persistLevel(new Level, $data);
            $this->syncRole($level, $data['permissions'] ?? []);

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

        return response()->json([
            'success' => true,
            'data' => $level,
            'permissions' => $role?->permissions->pluck('name') ?? collect(),
        ]);
    }

    public function update(UpdateLevelUserRequest $request, Level $level): JsonResponse
    {
        $data = $request->validated();
        $connection = $level->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $originalSlug = $level->slug;
            $this->persistLevel($level, $data);
            $this->syncRole($level, $data['permissions'] ?? [], $originalSlug);

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

    protected function permissionGroups(): Collection
    {
        $tenantKey = TenantContext::id() ?? session('tenant_id') ?? 'central';

        return Cache::remember(
            sprintf('tenant:%s:permission-groups', $tenantKey),
            now()->addHours(12),
            function () {
                return Permission::query()
                    ->orderBy('name')
                    ->get()
                    ->groupBy(function (Permission $permission) {
                        $prefix = Str::of($permission->name)->before('.');

                        return Str::headline($prefix->isNotEmpty() ? $prefix->__toString() : $permission->name);
                    });
            }
        );
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

    protected function syncRole(Level $level, array $permissions, ?string $originalSlug = null): void
    {
        $role = $this->findOrCreateRoleForLevel($level, $originalSlug);

        if ($role->name !== $level->slug) {
            $role->name = $level->slug;
            $role->save();
        }

        $role->syncPermissions($permissions);
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
