<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiutangGuru extends Model
{
    use HasFactory, UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'piutang_guru';

    protected $fillable = [
        'guru_id',
        'keterangan_hutang',
        'nominal_hutang',
        'waktu_hutang',
        'input_ke_pemotongan',
        'waktu_pemotongan',
    ];

    protected $casts = [
        'nominal_hutang' => 'decimal:2',
        'waktu_hutang' => 'datetime',
        'waktu_pemotongan' => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function getNominalHutangFormatAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->nominal_hutang, 0, ',', '.');
    }

    public function getWaktuHutangFormatAttribute(): string
    {
        return $this->waktu_hutang ? $this->waktu_hutang->format('d/m/Y H:i') : '-';
    }

    public function getWaktuPemotonganFormatAttribute(): string
    {
        return $this->waktu_pemotongan ? $this->waktu_pemotongan->format('d/m/Y H:i') : '-';
    }

    public function getInputKePemotonganBadgeAttribute(): string
    {
        return match ($this->input_ke_pemotongan) {
            'ya' => '<span class="badge bg-label-success">Ya</span>',
            'tidak' => '<span class="badge bg-label-secondary">Tidak</span>',
            default => '<span class="badge bg-label-secondary">-</span>',
        };
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
