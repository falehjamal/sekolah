<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;
    use UsesTenantTableSuffix;

    protected $connection = 'sekolah_tenant';

    protected string $baseTable = 'tb_menu';

    protected $fillable = [
        'parent_id',
        'name',
        'route_name',
        'icon',
        'sort_order',
        'is_active',
        'permission_name',
        'guard_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            $this->resolveTenantTable('tb_menu_role'),
            'menu_id',
            'role_id'
        );
    }
}
