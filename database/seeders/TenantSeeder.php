<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tenants')->insert([
            [
                'name'    => 'SMA Negeri 1 Jakarta',
                'db_host' => '127.0.0.1',
                'port'    => 3306,
                'db_name' => 'sekolah_sman1',
                'db_user' => 'sman1_user',
                'db_pass' => 'secret_sman1',
                'status'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'    => 'SMP Islam Al-Azhar',
                'db_host' => '127.0.0.1',
                'port'    => 3306,
                'db_name' => 'sekolah_al_azhar',
                'db_user' => 'alazhar_user',
                'db_pass' => 'secret_alazhar',
                'status'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'    => 'SD Kristen 2 Bandung',
                'db_host' => '192.168.10.20',
                'port'    => 3307,
                'db_name' => 'sekolah_sdk2',
                'db_user' => 'sdk2_user',
                'db_pass' => 'secret_sdk2',
                'status'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
