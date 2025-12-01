<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $connection = 'sekolah_tenant';

    protected $guarded = [];

    protected $guard_name = 'web';

    public function menus(): BelongsToMany
    {
        $menu = new Menu;

        return $this->belongsToMany(
            Menu::class,
            $menu->resolveTenantTable('menu_role'),
            'role_id',
            'menu_id'
        );
    }
}
