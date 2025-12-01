<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantUserExampleSeeder extends Seeder
{
    public function run(): void
    {
        $centralConnection = config('database.central_connection', 'sekolah_gateway');
        $baseConfig = Config::get("database.connections.{$centralConnection}");

        Config::set('database.connections.tenant_seed_1', array_merge($baseConfig, [
            'database' => 'sekolah_1',
        ]));

        $connection = DB::connection('tenant_seed_1');

        $connection->table('level_1')->updateOrInsert(
            ['slug' => 'administrator'],
            [
                'name' => 'Administrator',
                'description' => 'Level default tenant 1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $levelId = $connection->table('level_1')
            ->where('slug', 'administrator')
            ->value('id');

        if (! $levelId) {
            $this->command?->warn('Gagal mendapatkan ID level administrator di tenant 1.');

            return;
        }

        $connection->table('user_1')->updateOrInsert(
            ['username' => 'admin'],
            [
                'level_id' => $levelId,
                'name' => 'Admin Tenant 1',
                'email' => 'admin@tenant1.test',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
