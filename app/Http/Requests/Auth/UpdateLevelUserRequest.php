<?php

namespace App\Http\Requests\Auth;

use App\Models\Tenant\Level;
use App\Models\Tenant\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateLevelUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeLevel = $this->route('level');
        $levelId = $routeLevel ? $routeLevel->getKey() : null;

        $levelModel = new Level;
        $levelTable = $levelModel->getTable();
        $levelConnection = $levelModel->getConnectionName();
        $qualifiedLevelTable = $levelConnection ? $levelConnection.'.'.$levelTable : $levelTable;

        $menuModel = new Menu;
        $menuTable = $menuModel->getTable();
        $menuConnection = $menuModel->getConnectionName();
        $qualifiedMenuTable = $menuConnection ? $menuConnection.'.'.$menuTable : $menuTable;

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:150',
                Rule::unique($qualifiedLevelTable, 'slug')->ignore($levelId),
            ],
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
