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
        Schema::create($this->tableName('siswa_1'), function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->comment('Nomor Induk Siswa');
            $table->string('nisn', 20)->comment('Nomor Induk Siswa Nasional');
            $table->string('nama', 255);
            $table->enum('jk', ['l', 'p'])->comment('Jenis Kelamin: l=Laki-laki, p=Perempuan');
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->unsignedInteger('kelas_id');
            $table->unsignedInteger('jurusan_id');
            $table->unsignedInteger('orangtua_id');
            $table->string('no_hp', 20)->nullable();
            $table->enum('status', ['aktif', 'alumni', 'keluar'])->default('aktif');
            $table->timestamps();

            // Indexes
            $table->index('nis', 'idx_nis');
            $table->index('nisn', 'idx_nisn');
            $table->index('nama', 'idx_nama');
            $table->index('kelas_id', 'idx_kelas_id');
            $table->index('jurusan_id', 'idx_jurusan_id');
            $table->index('orangtua_id', 'idx_orangtua_id');
            $table->index('status', 'idx_status');

            // Foreign keys - uncomment jika tabel referensi sudah ada
            // $table->foreign('kelas_id')->references('id')->on($this->tableName('kelas'))->restrictOnDelete()->cascadeOnUpdate();
            // $table->foreign('jurusan_id')->references('id')->on($this->tableName('jurusan'))->restrictOnDelete()->cascadeOnUpdate();
            // $table->foreign('orangtua_id')->references('id')->on($this->tableName('orangtua'))->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('siswa_1'));
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

