<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'metode_pembayaran';

    protected $fillable = [
        'nama',
    ];
}
