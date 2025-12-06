<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiGuru extends Model
{
    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'cuti_guru';

    protected $fillable = [
        'guru_id',
        'jenis_cuti',
        'tanggal_awal',
        'tanggal_akhir',
        'waktu_entry',
        'status_approval',
        'petugas_id',
        'waktu_approval',
    ];

    protected $casts = [
        'guru_id' => 'integer',
        'tanggal_awal' => 'datetime',
        'tanggal_akhir' => 'datetime',
        'waktu_entry' => 'datetime',
        'petugas_id' => 'integer',
        'waktu_approval' => 'datetime',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'petugas_id');
    }

    public function getStatusApprovalBadgeAttribute(): string
    {
        return match ($this->status_approval) {
            'pending' => '<span class="badge bg-label-warning">Pending</span>',
            'approved' => '<span class="badge bg-label-success">Approved</span>',
            'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
            default => '<span class="badge bg-label-secondary">-</span>',
        };
    }

    public function getStatusApprovalLabelAttribute(): string
    {
        return match ($this->status_approval) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => '-',
        };
    }

    public function getTanggalAwalFormatAttribute(): string
    {
        return $this->tanggal_awal ? $this->tanggal_awal->format('d/m/Y') : '-';
    }

    public function getTanggalAkhirFormatAttribute(): string
    {
        return $this->tanggal_akhir ? $this->tanggal_akhir->format('d/m/Y') : '-';
    }

    public function getWaktuEntryFormatAttribute(): string
    {
        return $this->waktu_entry ? $this->waktu_entry->format('d/m/Y H:i') : '-';
    }

    public function getWaktuApprovalFormatAttribute(): string
    {
        return $this->waktu_approval ? $this->waktu_approval->format('d/m/Y H:i') : '-';
    }

    public function getDurasiHariAttribute(): int
    {
        if (!$this->tanggal_awal || !$this->tanggal_akhir) {
            return 0;
        }

        return $this->tanggal_awal->diffInDays($this->tanggal_akhir) + 1;
    }

    public function getPeriodeCutiAttribute(): string
    {
        return $this->tanggal_awal_format . ' - ' . $this->tanggal_akhir_format . ' (' . $this->durasi_hari . ' hari)';
    }
}
