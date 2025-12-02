<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Guru;
use App\Models\Tenant\UserAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class StoreGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        $guruTable = $this->qualifiedTable(new Guru);

        return [
            'user_id' => $this->userRule(),
            'nip' => 'nullable|string|max:50|unique:'.$guruTable.',nip',
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => 'nullable|in:L,P',
            'no_hp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama guru wajib diisi',
            'nama.max' => 'Nama maksimal 150 karakter',
            'nip.max' => 'NIP maksimal 50 karakter',
            'nip.unique' => 'NIP sudah terdaftar',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid',
            'no_hp.max' => 'No HP maksimal 50 karakter',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
            'user_id.exists' => 'Akun user tidak valid',
            'user_id.unique' => 'Akun user sudah terhubung ke guru lain',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->filled('user_id') ? (int) $this->input('user_id') : null,
            'jenis_kelamin' => $this->filled('jenis_kelamin')
                ? strtoupper((string) $this->input('jenis_kelamin'))
                : null,
            'status' => $this->filled('status') ? $this->input('status') : 'aktif',
        ]);
    }

    protected function userRule(?int $ignoreId = null): string
    {
        $userTable = $this->qualifiedTable(new UserAccount);
        $guruTable = $this->qualifiedTable(new Guru);

        $uniqueRule = 'unique:'.$guruTable.',user_id';

        if ($ignoreId !== null) {
            $uniqueRule .= ','.$ignoreId;
        }

        return implode('|', [
            'nullable',
            'integer',
            'exists:'.$userTable.',id',
            $uniqueRule,
        ]);
    }

    protected function qualifiedTable(Model $model): string
    {
        $table = $model->getTable();
        $connection = $model->getConnectionName();

        return $connection ? $connection.'.'.$table : $table;
    }
}
