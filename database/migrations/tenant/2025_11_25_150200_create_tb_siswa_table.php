<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Stancl\Tenancy\tenant;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->tableName('tb_siswa'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('nis')->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('name');
            $table->string('kelas')->nullable();
            $table->enum('level', ['parent', 'siswa'])->default('siswa');
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on($this->tableName('tb_user'))
                ->nullOnDelete();

            $table->foreign('parent_id')
                ->references('id')
                ->on($this->tableName('tb_user'))
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('tb_siswa'));
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
