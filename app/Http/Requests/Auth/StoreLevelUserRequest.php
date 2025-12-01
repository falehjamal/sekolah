<?php

namespace App\Http\Requests\Auth;

use App\Models\Tenant\Level;
use App\Models\Tenant\Menu;
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

        $menu = new Menu;
        $menuTable = $menu->getTable();
        $menuConnection = $menu->getConnectionName();
        $qualifiedMenuTable = $menuConnection ? $menuConnection.'.'.$menuTable : $menuTable;

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique($qualifiedLevelTable, 'slug')],
            'description' => ['nullable', 'string'],
            'menu_ids' => ['nullable', 'array'],
            'menu_ids.*' => ['integer', Rule::exists($qualifiedMenuTable, 'id')],
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
