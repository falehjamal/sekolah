<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemotonganGuru extends Model
{
    use HasFactory, UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'pemotongan_guru';

    protected $fillable = [
        'guru_id',
        'nama_pemotongan',
        'nominal_pemotongan',
        'waktu',
        'jenis_pemotongan',
    ];

    protected $casts = [
        'nominal_pemotongan' => 'decimal:2',
        'waktu' => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function getNominalPemotonganFormatAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->nominal_pemotongan, 0, ',', '.');
    }

    public function getWaktuFormatAttribute(): string
    {
        return $this->waktu ? $this->waktu->format('d/m/Y H:i') : '-';
    }

    public function getGuruNamaAttribute(): string
    {
        return $this->guru?->nama ?? '-';
    }

    public function getGuruNipAttribute(): string
    {
        return $this->guru?->nip ?? '-';
    }
}
