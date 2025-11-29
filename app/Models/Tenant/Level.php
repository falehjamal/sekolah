<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Level extends Model
{
    use HasFactory;
    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'tb_level';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(UserAccount::class, 'level_id');
    }

    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'name', 'slug');
    }
}
