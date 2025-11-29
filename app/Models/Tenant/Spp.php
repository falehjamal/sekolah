<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spp extends Model
{
    /** @use HasFactory<\Database\Factories\Tenant\SppFactory> */
    use HasFactory;

    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'spp';

    protected $fillable = [
        'nama',
        'nominal',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'spp_id');
    }
}
