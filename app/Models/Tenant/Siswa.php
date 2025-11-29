<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    /** @use HasFactory<\Database\Factories\Tenant\SiswaFactory> */
    use HasFactory;

    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'siswa';

    protected $fillable = [
        'nis',
        'nisn',
        'nama',
        'jk',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'kelas_id',
        'jurusan_id',
        'spp_id',
        'no_hp',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function orangtua(): HasMany
    {
        return $this->hasMany(Orangtua::class, 'siswa_id');
    }

    public function spp(): BelongsTo
    {
        return $this->belongsTo(Spp::class, 'spp_id');
    }

    public function getJkLengkapAttribute(): string
    {
        return $this->jk === 'l' ? 'Laki-laki' : 'Perempuan';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'aktif' => '<span class="badge bg-label-success">Aktif</span>',
            'alumni' => '<span class="badge bg-label-info">Alumni</span>',
            'keluar' => '<span class="badge bg-label-danger">Keluar</span>',
            default => '<span class="badge bg-label-secondary">-</span>',
        };
    }
}
