<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesTenantTableSuffix;

class TunjanganGuru extends Model
{
    use HasFactory, UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'tunjangan_guru';

    protected $fillable = [
        'guru_id',
        'nama_tunjangan',
        'nominal_tunjangan',
        'waktu',
    ];

    protected $casts = [
        'nominal_tunjangan' => 'decimal:2',
        'waktu' => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function getNominalTunjanganFormatAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->nominal_tunjangan, 0, ',', '.');
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
