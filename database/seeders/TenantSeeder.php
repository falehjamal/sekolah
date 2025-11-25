<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => 'SMA Negeri 1 Jakarta',
                'db_host' => '127.0.0.1',
                'port' => 3306,
                'db_name' => 'sekolah_1',
                'db_user' => env('TENANT_DB_USERNAME', 'root'),
                'db_pass' => env('TENANT_DB_PASSWORD', ''),
                'status' => true,
            ]
        );
    }
}
