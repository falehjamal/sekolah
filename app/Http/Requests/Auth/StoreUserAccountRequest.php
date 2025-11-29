<?php

namespace App\Http\Requests\Auth;

use App\Models\Tenant\Level;
use App\Models\Tenant\UserAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = new UserAccount;
        $userTable = $user->getTable();
        $userConnection = $user->getConnectionName();
        $qualifiedUserTable = $userConnection ? $userConnection.'.'.$userTable : $userTable;

        $level = new Level;
        $levelTable = $level->getTable();
        $levelConnection = $level->getConnectionName();
        $qualifiedLevelTable = $levelConnection ? $levelConnection.'.'.$levelTable : $levelTable;

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', Rule::unique($qualifiedUserTable, 'username')],
            'email' => ['nullable', 'email', 'max:150', Rule::unique($qualifiedUserTable, 'email')],
            'level_id' => ['required', Rule::exists($qualifiedLevelTable, 'id')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
