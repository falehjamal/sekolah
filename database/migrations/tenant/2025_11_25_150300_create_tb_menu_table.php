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
        Schema::create($this->tableName('tb_menu'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('route_name')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('permission_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('guard_name')->default('web');
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on($this->tableName('tb_menu'))
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('tb_menu'));
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
