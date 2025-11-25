<?php

namespace App\Models\Tenant;

use App\Models\Concerns\UsesTenantTableSuffix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            $this->resolveTenantTable('tb_menu_role'),
            'menu_id',
            'role_id'
        );
    }
}
