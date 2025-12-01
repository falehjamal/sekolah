<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Orangtua extends Model
{
    /** @use HasFactory<\Database\Factories\Tenant\OrangtuaFactory> */
    use HasFactory;

    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'orangtua';

    protected $fillable = [
        'siswa_id',
        'user_id',
        'nama',
        'hubungan',
        'no_hp',
        'pekerjaan',
        'alamat',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }
}
