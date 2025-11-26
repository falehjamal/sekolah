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
        $user = $this->route('user');
        $userTable = (new UserAccount)->getTable();
        $levelTable = (new Level)->getTable();

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique($userTable, 'username')->ignore($user?->getKey()),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique($userTable, 'email')->ignore($user?->getKey()),
            ],
            'level_id' => ['required', Rule::exists($levelTable, 'id')],
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
