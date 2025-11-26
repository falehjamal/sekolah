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
        $levelTable = (new Level)->getTable();
        $permissionTable = (new Permission)->getTable();

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique($levelTable, 'slug')],
            'description' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists($permissionTable, 'name')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slugSource = $this->input('slug') ?: $this->input('name');

        $this->merge([
            'slug' => $slugSource ? Str::slug($slugSource) : null,
            'is_default' => $this->boolean('is_default'),
        ]);
    }
}
