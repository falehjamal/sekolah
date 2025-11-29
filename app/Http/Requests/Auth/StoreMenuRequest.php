<?php

namespace App\Http\Requests\Auth;

use App\Models\Tenant\Menu;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $menu = new Menu;
        $menuTable = $menu->getTable();
        $menuConnection = $menu->getConnectionName();
        $qualifiedMenuTable = $menuConnection ? $menuConnection.'.'.$menuTable : $menuTable;

        $role = new Role;
        $roleTable = $role->getTable();
        $roleConnection = $role->getConnectionName();
        $qualifiedRoleTable = $roleConnection ? $roleConnection.'.'.$roleTable : $roleTable;

        $permission = new Permission;
        $permissionTable = $permission->getTable();
        $permissionConnection = $permission->getConnectionName();
        $qualifiedPermissionTable = $permissionConnection ? $permissionConnection.'.'.$permissionTable : $permissionTable;

        return [
            'parent_id' => ['nullable', 'integer', 'exists:'.$qualifiedMenuTable.',id'],
            'name' => ['required', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:255', Rule::unique($qualifiedMenuTable, 'route_name')],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'permission_name' => ['nullable', 'string', 'max:255', Rule::exists($qualifiedPermissionTable, 'name')],
            'guard_name' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:'.$qualifiedRoleTable.',id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'guard_name' => $this->input('guard_name') ?: 'web',
            'sort_order' => $this->filled('sort_order') ? (int) $this->input('sort_order') : 0,
        ]);
    }
}
