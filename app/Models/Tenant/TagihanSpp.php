<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagihanSpp extends Model
{
    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'tagihan_spp';

    protected $fillable = [
        'siswa_id',
        'bulan',
        'nominal',
        'diskon',
        'tanggal_bayar',
        'metode_pembayaran_id',
        'rekening_id',
        'petugas_id',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'tanggal_bayar' => 'date',
    ];

    /**
     * Get total bayar (nominal + diskon)
     */
    public function getTotalBayarAttribute(): float
    {
        return (float) $this->nominal + (float) $this->diskon;
    }

    public function getDiskonFormatAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->diskon, 0, ',', '.');
    }

    public function getTotalBayarFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_bayar, 0, ',', '.');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function metodePembayaran(): BelongsTo
    {
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'rekening_id');
    }

    public function getBulanFormatAttribute(): string
    {
        if (!$this->bulan) {
            return '-';
        }

        $parts = explode('-', $this->bulan);
        if (count($parts) !== 2) {
            return $this->bulan;
        }

        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        return ($months[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
    }

    public function getNominalFormatAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->nominal, 0, ',', '.');
    }

    public function getMetodeBadgeAttribute(): string
    {
        $metodeName = strtolower($this->metodePembayaran?->nama ?? '');

        return match ($metodeName) {
            'tunai', 'cash' => '<span class="badge bg-label-success">Tunai</span>',
            'transfer' => '<span class="badge bg-label-primary">Transfer</span>',
            'qris' => '<span class="badge bg-label-info">QRIS</span>',
            'debit' => '<span class="badge bg-label-warning">Debit</span>',
            default => '<span class="badge bg-label-secondary">' . ($this->metodePembayaran?->nama ?? '-') . '</span>',
        };
    }
}
