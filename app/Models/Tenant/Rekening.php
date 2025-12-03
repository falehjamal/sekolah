<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'rekening';

    protected $fillable = [
        'bank',
        'no_rekening',
        'nama_rekening',
    ];

    public function getLabelAttribute(): string
    {
        return $this->bank . ' - ' . $this->no_rekening . ' (' . $this->nama_rekening . ')';
    }
}
