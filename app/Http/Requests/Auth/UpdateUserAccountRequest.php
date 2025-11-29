<?php

namespace App\Http\Requests\Auth;

use App\Models\Tenant\Level;
use App\Models\Tenant\UserAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeUser = $this->route('user');

        $userModel = new UserAccount;
        $userTable = $userModel->getTable();
        $userConnection = $userModel->getConnectionName();
        $qualifiedUserTable = $userConnection ? $userConnection.'.'.$userTable : $userTable;

        $levelModel = new Level;
        $levelTable = $levelModel->getTable();
        $levelConnection = $levelModel->getConnectionName();
        $qualifiedLevelTable = $levelConnection ? $levelConnection.'.'.$levelTable : $levelTable;

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique($qualifiedUserTable, 'username')->ignore($routeUser?->getKey()),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique($qualifiedUserTable, 'email')->ignore($routeUser?->getKey()),
            ],
            'level_id' => ['required', Rule::exists($qualifiedLevelTable, 'id')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
