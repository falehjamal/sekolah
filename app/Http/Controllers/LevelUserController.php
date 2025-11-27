<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\StoreLevelUserRequest;
use App\Http\Requests\Auth\UpdateLevelUserRequest;
use App\Models\Tenant\Level;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Support\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class LevelUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:auth.roles.view')->only(['index', 'create', 'edit']);
        $this->middleware('permission:auth.roles.manage')->only(['store', 'update', 'destroy']);
    }

    public function index(): View
    {
        $levels = Level::query()
            ->withCount('users')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::query()
            ->whereIn('name', $levels->pluck('slug'))
            ->with('permissions')
            ->get()
            ->keyBy('name');

        return view('auth.levels.index', [
            'levels' => $levels,
            'roles' => $roles,
        ]);
    }

    public function create(): View
    {
        return view('auth.levels.create', [
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function store(StoreLevelUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $level = $this->persistLevel(new Level, $data);
            $this->syncRole($level, $data['permissions'] ?? []);
        });

        return redirect()->route('auth.levels.index')->with('status', 'Level user berhasil dibuat.');
    }

    public function edit(Level $level): View
    {
        $role = $this->findOrCreateRoleForLevel($level);

        return view('auth.levels.edit', [
            'level' => $level,
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => $role?->permissions->pluck('name')->all() ?? [],
        ]);
    }

    public function update(UpdateLevelUserRequest $request, Level $level): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $level) {
            $originalSlug = $level->slug;
            $this->persistLevel($level, $data);
            $this->syncRole($level, $data['permissions'] ?? [], $originalSlug);
        });

        return redirect()->route('auth.levels.index')->with('status', 'Level user berhasil diperbarui.');
    }

    public function destroy(Level $level): RedirectResponse
    {
        if ($level->is_default) {
            return back()->withErrors(['level' => 'Level default tidak dapat dihapus.']);
        }

        if ($level->users()->exists()) {
            return back()->withErrors(['level' => 'Level masih digunakan oleh pengguna aktif.']);
        }

        DB::transaction(function () use ($level) {
            $role = Role::query()->firstWhere('name', $level->slug);
            $level->delete();
            $role?->delete();
        });

        return redirect()->route('auth.levels.index')->with('status', 'Level user berhasil dihapus.');
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
        if (($data['is_default'] ?? false) === true) {
            Level::query()
                ->when($level->exists, fn ($query) => $query->whereKeyNot($level->getKey()))
                ->update(['is_default' => false]);
        }

        $level->fill([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_default' => $data['is_default'] ?? false,
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
}
