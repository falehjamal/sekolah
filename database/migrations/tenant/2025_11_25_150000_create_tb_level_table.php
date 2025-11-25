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
        Schema::create($this->tableName('tb_level'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('tb_level'));
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
