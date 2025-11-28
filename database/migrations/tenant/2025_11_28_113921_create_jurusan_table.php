<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Facades\Tenancy;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->tableName('jurusan'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode', 20);
            $table->string('nama_jurusan', 150);
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique('kode', 'jurusan_kode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('jurusan'));
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
