<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GajiGuru extends Model
{
    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'gaji_guru';

    protected $fillable = [
        'guru_id',
        'tempat_lahir',
        'tanggal_lahir',
        'tanggal_bergabung',
        'jenis_gaji',
        'gaji_pokok',
        'uang_makan',
        'uang_transport',
        'tunjangan_jabatan',
        'tunjangan_lain',
        'status',
    ];

    protected $casts = [
        'guru_id' => 'integer',
        'tanggal_lahir' => 'datetime',
        'tanggal_bergabung' => 'datetime',
        'gaji_pokok' => 'float',
        'uang_makan' => 'float',
        'uang_transport' => 'float',
        'tunjangan_jabatan' => 'float',
        'tunjangan_lain' => 'float',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function getJenisGajiLabelAttribute(): string
    {
        return match ($this->jenis_gaji) {
            'harian' => 'Harian',
            'bulanan' => 'Bulanan',
            default => '-',
        };
    }

    public function getJenisGajiBadgeAttribute(): string
    {
        return match ($this->jenis_gaji) {
            'harian' => '<span class="badge bg-label-info">Harian</span>',
            'bulanan' => '<span class="badge bg-label-primary">Bulanan</span>',
            default => '<span class="badge bg-label-secondary">-</span>',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'aktif' => '<span class="badge bg-label-success">Aktif</span>',
            'nonaktif' => '<span class="badge bg-label-secondary">Nonaktif</span>',
            default => '<span class="badge bg-label-secondary">-</span>',
        };
    }

    public function getTotalGajiAttribute(): float
    {
        return (float) $this->gaji_pokok +
               (float) $this->uang_makan +
               (float) $this->uang_transport +
               (float) $this->tunjangan_jabatan +
               (float) $this->tunjangan_lain;
    }

    public function getTotalGajiFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_gaji, 0, ',', '.');
    }

    public function getGajiPokokFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->gaji_pokok, 0, ',', '.');
    }

    public function getUangMakanFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->uang_makan, 0, ',', '.');
    }

    public function getUangTransportFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->uang_transport, 0, ',', '.');
    }

    public function getTunjanganJabatanFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->tunjangan_jabatan, 0, ',', '.');
    }

    public function getTunjanganLainFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->tunjangan_lain, 0, ',', '.');
    }

    public function getTanggalLahirFormatAttribute(): string
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->format('d/m/Y') : '-';
    }

    public function getTanggalBergabungFormatAttribute(): string
    {
        return $this->tanggal_bergabung ? $this->tanggal_bergabung->format('d/m/Y') : '-';
    }
}
