<?php

namespace App\Http\Requests\Auth;

use App\Models\Tenant\Level;
use App\Models\Tenant\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreLevelUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $level = new Level;
        $levelTable = $level->getTable();
        $levelConnection = $level->getConnectionName();
        $qualifiedLevelTable = $levelConnection ? $levelConnection.'.'.$levelTable : $levelTable;

        $permission = new Permission;
        $permissionTable = $permission->getTable();
        $permissionConnection = $permission->getConnectionName();
        $qualifiedPermissionTable = $permissionConnection ? $permissionConnection.'.'.$permissionTable : $permissionTable;

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique($qualifiedLevelTable, 'slug')],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists($qualifiedPermissionTable, 'name')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slugSource = $this->input('slug') ?: $this->input('name');

        $this->merge([
            'slug' => $slugSource ? Str::slug($slugSource) : null,
        ]);
    }
}
