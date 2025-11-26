<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\StoreUserAccountRequest;
use App\Http\Requests\Auth\UpdateUserAccountRequest;
use App\Models\Tenant\Level;
use App\Models\Tenant\Role;
use App\Models\Tenant\UserAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:auth.users.view')->only('index');
        $this->middleware('permission:auth.users.manage')->except('index');
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $users = UserAccount::query()
            ->with('level')
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('auth.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('auth.users.create', [
            'levels' => $this->levels(),
        ]);
    }

    public function store(StoreUserAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = UserAccount::query()->create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
                'level_id' => $data['level_id'],
                'is_active' => $data['is_active'],
            ]);

            $this->syncRoleForUser($user, $data['level_id']);
        });

        return redirect()->route('auth.users.index')->with('status', 'Pengguna berhasil dibuat.');
    }

    public function edit(UserAccount $user): View
    {
        $user->load('level');

        return view('auth.users.edit', [
            'user' => $user,
            'levels' => $this->levels(),
        ]);
    }

    public function update(UpdateUserAccountRequest $request, UserAccount $user): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $user) {
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
        });

        return redirect()->route('auth.users.index')->with('status', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(UserAccount $user): RedirectResponse
    {
        DB::transaction(function () use ($user) {
            $user->syncRoles([]);
            $user->delete();
        });

        return redirect()->route('auth.users.index')->with('status', 'Pengguna berhasil dihapus.');
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
}
