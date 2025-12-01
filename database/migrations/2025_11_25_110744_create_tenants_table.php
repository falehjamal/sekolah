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
            $table->string('db_host')->default('127.0.0.1')->after('status');
            $table->unsignedSmallInteger('port')->default(3306)->after('db_host');
            $table->string('db_name')->after('port');
            $table->string('db_user')->after('db_name');
            $table->string('db_pass')->after('db_user');
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
                'db_host',
                'port',
                'db_name',
                'db_user',
                'db_pass',
            ]);
        });
    }
};
