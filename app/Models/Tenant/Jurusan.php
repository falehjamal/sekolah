<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends Model
{
    /** @use HasFactory<\Database\Factories\Tenant\JurusanFactory> */
    use HasFactory;

    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'jurusan';

    protected $fillable = [
        'kode',
        'nama_jurusan',
        'deskripsi',
    ];

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }
}
