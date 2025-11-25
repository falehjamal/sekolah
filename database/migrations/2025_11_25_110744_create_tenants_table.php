<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->boolean('status')->default(true)->after('name');
            $table->string('tenancy_db_host')->default('127.0.0.1')->after('status');
            $table->unsignedSmallInteger('tenancy_db_port')->default(3306)->after('tenancy_db_host');
            $table->string('tenancy_db_name')->after('tenancy_db_port');
            $table->string('tenancy_db_username')->after('tenancy_db_name');
            $table->string('tenancy_db_password')->after('tenancy_db_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'status',
                'tenancy_db_host',
                'tenancy_db_port',
                'tenancy_db_name',
                'tenancy_db_username',
                'tenancy_db_password',
            ]);
        });
    }
};
