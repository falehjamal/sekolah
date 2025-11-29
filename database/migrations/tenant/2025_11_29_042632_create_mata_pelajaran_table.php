<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Facades\Tenancy;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->tableName('mata_pelajaran'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode', 20);
            $table->string('nama_mapel', 150);
            $table->string('kurikulum', 50)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->unique('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('mata_pelajaran'));
    }

    protected function tableName(string $base): string
    {
        $tenantId = Tenancy::getTenant()?->getTenantKey();

        if (! $tenantId) {
            throw new \RuntimeException('Tenant belum disiapkan untuk migrasi.');
        }

        return sprintf('%s_%s', $base, $tenantId);
    }
};
