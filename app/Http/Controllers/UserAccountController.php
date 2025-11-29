<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\StoreUserAccountRequest;
use App\Http\Requests\Auth\UpdateUserAccountRequest;
use App\Models\Tenant\Level;
use App\Models\Tenant\Role;
use App\Models\Tenant\UserAccount;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class UserAccountController extends Controller
{
    public function __construct(protected TenantConnectionManager $tenantConnection)
    {
        $this->middleware(function ($request, $next) {
            if (session()->has('tenant_connection')) {
                $this->tenantConnection->connectFromSession();
            }

            return $next($request);
        });

        $this->middleware('permission:auth.users.view')->only('index');
        $this->middleware('permission:auth.users.manage')->only(['store', 'update', 'destroy', 'show']);
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->datatable();
        }

        return view('auth.users.index', [
            'levels' => $this->levels(),
            'canManage' => $this->canManageUsers(),
        ]);
    }

    public function datatable(): JsonResponse
    {
        $users = UserAccount::query()
            ->with(['level', 'roles'])
            ->orderByDesc('created_at');

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('info_card', function (UserAccount $row): string {
                $statusClass = $row->is_active ? 'bg-label-success' : 'bg-label-secondary';
                $statusLabel = $row->is_active ? 'Aktif' : 'Nonaktif';

                return sprintf(
                    '<div class="table-card">
                        <div class="table-avatar avatar-indigo">%s</div>
                        <div class="table-card__body">
                            <div class="table-card__title">%s</div>
                            <div class="text-muted small">%s</div>
                            <span class="badge %s mt-1">%s</span>
                        </div>
                    </div>',
                    strtoupper(mb_substr($row->name, 0, 1)),
                    e($row->name),
                    e($row->username),
                    $statusClass,
                    $statusLabel
                );
            })
            ->addColumn('detail_card', function (UserAccount $row): string {
                $email = $row->email ?: '-';
                $levelName = $row->level?->name ?? 'Belum diatur';
                $lastLogin = $row->last_login_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah';

                return sprintf(
                    '<div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Email</span>%s</li>
                            <li><span>Level</span>%s</li>
                            <li><span>Login Terakhir</span>%s</li>
                        </ul>
                    </div>',
                    e($email),
                    e($levelName),
                    e($lastLogin)
                );
            })
            ->addColumn('role_badges', function (UserAccount $row): string {
                $roles = $row->roles->pluck('name');

                if ($roles->isEmpty()) {
                    return '<span class="text-muted small">Belum ada role</span>';
                }

                $badges = $roles->map(function ($role) {
                    return '<span class="badge bg-label-info mb-1 me-1">'.e($role).'</span>';
                })->implode('');

                return '<div class="d-flex flex-wrap">'.$badges.'</div>';
            })
            ->addColumn('status_badge', function (UserAccount $row): string {
                $class = $row->is_active ? 'bg-label-success' : 'bg-label-secondary';
                $label = $row->is_active ? 'Aktif' : 'Nonaktif';

                return '<span class="badge '.$class.'">'.$label.'</span>';
            })
            ->addColumn('action', function (UserAccount $row) {
                if (! $this->canManageUsers()) {
                    return '<span class="text-muted small">Tidak ada akses</span>';
                }

                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editUser('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusUser('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['info_card', 'detail_card', 'role_badges', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(StoreUserAccountRequest $request): JsonResponse
    {
        $data = $request->validated();
        $connection = (new UserAccount)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $user = UserAccount::query()->create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
                'level_id' => $data['level_id'],
                'is_active' => $data['is_active'],
            ]);

            $this->syncRoleForUser($user, $data['level_id']);

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dibuat.',
                'data' => $user,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan pengguna.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(UserAccount $user): JsonResponse
    {
        $user->load('level', 'roles');

        return response()->json([
            'success' => true,
            'data' => $user,
            'roles' => $user->roles->pluck('name'),
        ]);
    }

    public function update(UpdateUserAccountRequest $request, UserAccount $user): JsonResponse
    {
        $data = $request->validated();
        $connection = $user->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $payload = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'level_id' => $data['level_id'],
                'is_active' => $data['is_active'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $user->update($payload);
            $this->syncRoleForUser($user, $data['level_id']);

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil diperbarui.',
                'data' => $user,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui pengguna.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(UserAccount $user): JsonResponse
    {
        $connection = $user->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $user->syncRoles([]);
            $user->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus.',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus pengguna.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    protected function levels()
    {
        return Level::query()->orderBy('name')->get();
    }

    protected function syncRoleForUser(UserAccount $user, int $levelId): void
    {
        $level = Level::query()->findOrFail($levelId);

        $role = Role::query()->firstOrCreate(
            ['name' => $level->slug],
            ['guard_name' => 'web']
        );

        $user->syncRoles([$role->name]);
    }

    protected function canManageUsers(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->can('auth.users.manage') ?? false;
    }
}
