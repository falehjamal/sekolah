<?php

namespace Database\Seeders;

use App\Models\Tenant\Level;
use App\Models\Tenant\Menu;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Models\Tenant\UserAccount;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        TenantContext::set((int) tenant('id'));

        $levels = $this->seedLevels();
        $roles = $this->seedRoles();
        $this->seedMenus($roles);
        $this->seedUsers($levels, $roles);
    }

    protected function seedLevels(): array
    {
        $payload = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Memiliki seluruh akses modul.',
                'is_default' => true,
            ],
        ];

        $levels = [];

        foreach ($payload as $data) {
            $levels[$data['slug']] = Level::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        return $levels;
    }

    protected function seedRoles(): array
    {
        $permissions = collect([
            'dashboard.view',
            'auth.roles.view',
            'auth.roles.manage',
            'auth.users.view',
            'auth.users.manage',
        ])->map(function (string $name) {
            return Permission::query()->firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web']
            );
        });

        $roles = [
            'administrator' => Role::query()->firstOrCreate(['name' => 'administrator'], ['guard_name' => 'web']),
        ];

        $roles['administrator']->syncPermissions($permissions);

        return $roles;
    }

    protected function seedMenus(array $roles): void
    {
        $dashboard = Menu::query()->updateOrCreate(
            ['route_name' => 'dashboard'],
            [
                'name' => 'Dashboard',
                'icon' => 'bx bx-home-circle',
                'sort_order' => 1,
                'permission_name' => 'dashboard.view',
            ]
        );

        $dashboard->roles()->syncWithoutDetaching([$roles['administrator']->id]);

        $authGroup = Menu::query()->updateOrCreate(
            ['name' => 'Autentikasi', 'route_name' => null],
            [
                'icon' => 'bx bx-lock-alt',
                'sort_order' => 2,
                'permission_name' => null,
            ]
        );

        $authMenus = [
            [
                'name' => 'Level User',
                'route_name' => 'auth.levels.index',
                'icon' => 'bx bx-shield-quarter',
                'permission_name' => 'auth.roles.view',
                'sort_order' => 1,
            ],
            [
                'name' => 'User',
                'route_name' => 'auth.users.index',
                'icon' => 'bx bx-user',
                'permission_name' => 'auth.users.view',
                'sort_order' => 2,
            ],
        ];

        foreach ($authMenus as $data) {
            $menu = Menu::query()->updateOrCreate(
                ['route_name' => $data['route_name']],
                array_merge($data, ['parent_id' => $authGroup->id])
            );

            $menu->roles()->syncWithoutDetaching([$roles['administrator']->id]);
        }
    }

    protected function seedUsers(array $levels, array $roles): void
    {
        $admin = UserAccount::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'level_id' => $levels['administrator']->id,
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]
        );

        $admin->syncRoles($roles['administrator']);
    }
}
