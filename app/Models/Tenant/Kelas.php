<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    /** @use HasFactory<\Database\Factories\Tenant\KelasFactory> */
    use HasFactory;

    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan_id',
    ];

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}
