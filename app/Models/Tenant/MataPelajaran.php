<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\Tenant\MataPelajaranFactory> */
    use HasFactory;

    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'mata_pelajaran';

    protected $fillable = [
        'kode',
        'nama_mapel',
        'kurikulum',
        'status',
    ];
}
