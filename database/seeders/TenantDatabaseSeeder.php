<?php

namespace Database\Seeders;

use App\Models\Tenant\Level;
use App\Models\Tenant\Menu;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Models\Tenant\Student;
use App\Models\Tenant\UserAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $levels = $this->seedLevels();
        $roles = $this->seedRoles();
        $this->seedMenus($roles);
        $users = $this->seedUsers($levels, $roles);
        $this->seedStudents($users);
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
            [
                'name' => 'Guru',
                'slug' => 'guru',
                'description' => 'Pengajar dan wali kelas.',
                'is_default' => false,
            ],
            [
                'name' => 'Staf Akademik',
                'slug' => 'staf-akademik',
                'description' => 'Pengelola administrasi akademik.',
                'is_default' => false,
            ],
            [
                'name' => 'Orang Tua',
                'slug' => 'orang-tua',
                'description' => 'Akun pendamping murid.',
                'is_default' => false,
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
            'siswa.manage',
            'guru.manage',
            'menu.manage',
        ])->map(function (string $name) {
            return Permission::query()->firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web']
            );
        });

        $roles = [
            'super-admin' => Role::query()->firstOrCreate(['name' => 'super-admin'], ['guard_name' => 'web']),
            'guru' => Role::query()->firstOrCreate(['name' => 'guru'], ['guard_name' => 'web']),
            'orang-tua' => Role::query()->firstOrCreate(['name' => 'orang-tua'], ['guard_name' => 'web']),
        ];

        $roles['super-admin']->syncPermissions($permissions);
        $roles['guru']->syncPermissions($permissions->only(['dashboard.view', 'siswa.manage']));
        $roles['orang-tua']->syncPermissions($permissions->only(['dashboard.view']));

        return $roles;
    }

    protected function seedMenus(array $roles): void
    {
        $menus = [
            [
                'name' => 'Dashboard',
                'route_name' => 'dashboard',
                'icon' => 'bi-speedometer2',
                'sort_order' => 1,
                'permission_name' => 'dashboard.view',
            ],
            [
                'name' => 'Data Siswa',
                'route_name' => 'students.index',
                'icon' => 'bi-people',
                'sort_order' => 2,
                'permission_name' => 'siswa.manage',
            ],
            [
                'name' => 'Manajemen Menu',
                'route_name' => 'menus.index',
                'icon' => 'bi-list-check',
                'sort_order' => 3,
                'permission_name' => 'menu.manage',
            ],
        ];

        foreach ($menus as $data) {
            $menu = Menu::query()->updateOrCreate(
                ['route_name' => $data['route_name']],
                $data
            );

            $permittedRoles = match ($data['permission_name']) {
                'menu.manage' => ['super-admin'],
                'siswa.manage' => ['super-admin', 'guru'],
                default => ['super-admin', 'guru', 'orang-tua'],
            };

            $menu->roles()->syncWithoutDetaching(
                collect($permittedRoles)->map(fn ($key) => $roles[$key]->id)->all()
            );
        }
    }

    protected function seedUsers(array $levels, array $roles): array
    {
        $users = [
            'admin' => UserAccount::query()->updateOrCreate(
                ['username' => 'admin'],
                [
                    'name' => 'Administrator',
                    'email' => 'admin@example.com',
                    'level_id' => $levels['administrator']->id,
                    'password' => Hash::make('admin123'),
                    'is_active' => true,
                ]
            ),
            'guru' => UserAccount::query()->updateOrCreate(
                ['username' => 'guru01'],
                [
                    'name' => 'Guru Matematika',
                    'email' => 'guru@example.com',
                    'level_id' => $levels['guru']->id,
                    'password' => Hash::make('guru123'),
                    'is_active' => true,
                ]
            ),
            'orang_tua' => UserAccount::query()->updateOrCreate(
                ['username' => 'ortu01'],
                [
                    'name' => 'Orang Tua Andi',
                    'email' => 'ortu@example.com',
                    'level_id' => $levels['orang-tua']->id,
                    'password' => Hash::make('ortu123'),
                    'is_active' => true,
                ]
            ),
        ];

        $users['admin']->syncRoles($roles['super-admin']);
        $users['guru']->syncRoles($roles['guru']);
        $users['orang_tua']->syncRoles($roles['orang-tua']);

        return $users;
    }

    protected function seedStudents(array $users): void
    {
        Student::query()->updateOrCreate(
            ['nis' => '2025-001'],
            [
                'user_id' => $users['guru']->id,
                'parent_id' => $users['orang_tua']->id,
                'nisn' => '9988776655',
                'name' => 'Andi Pratama',
                'kelas' => 'XI IPA 1',
                'level' => 'siswa',
                'gender' => 'L',
                'tanggal_lahir' => now()->subYears(16)->startOfYear(),
                'telepon' => '08123456789',
                'alamat' => 'Jl. Pendidikan No. 45, Jakarta',
            ]
        );
    }
}
