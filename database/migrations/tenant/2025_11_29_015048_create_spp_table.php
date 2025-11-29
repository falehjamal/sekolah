<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Facades\Tenancy;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->tableName('spp'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 30)->nullable();
            $table->decimal('nominal', 12, 2);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('spp'));
    }

    protected function tableName(string $base): string
    {
        $tenantId = Tenancy::getTenant()?->getTenantKey();

        if (! $tenantId) {
            throw new RuntimeException('Tenant belum disiapkan untuk migrasi.');
        }

        return sprintf('%s_%s', $base, $tenantId);
    }
};
