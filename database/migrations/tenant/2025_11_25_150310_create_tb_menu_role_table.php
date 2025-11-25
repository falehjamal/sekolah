<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

use function Stancl\Tenancy\tenant;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->tableName('tb_menu_role'), function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->primary(['menu_id', 'role_id'], 'tb_menu_role_primary');

            $table->foreign('menu_id')
                ->references('id')
                ->on($this->tableName('tb_menu'))
                ->cascadeOnDelete();

            $table->foreign('role_id')
                ->references('id')
                ->on(config('permission.table_names.roles'))
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('tb_menu_role'));
    }

    protected function tableName(string $base): string
    {
        $tenantId = tenant('id');

        if (! $tenantId) {
            throw new RuntimeException('Tenant belum disiapkan untuk migrasi.');
        }

        return sprintf('%s_%s', $base, $tenantId);
    }
};
