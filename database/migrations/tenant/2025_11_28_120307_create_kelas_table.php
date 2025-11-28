<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Facades\Tenancy;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->tableName('kelas'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_kelas', 100);
            $table->unsignedTinyInteger('tingkat');
            $table->unsignedBigInteger('jurusan_id')->nullable();
            $table->timestamps();

            $table->index('nama_kelas', 'kelas_nama_index');
            $table->index('tingkat', 'kelas_tingkat_index');
            $table->index('jurusan_id', 'kelas_jurusan_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('kelas'));
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
