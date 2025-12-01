<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Orangtua;
use App\Models\Tenant\Siswa;
use App\Models\Tenant\UserAccount;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrangtuaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orangtua = new Orangtua;
        $connection = $orangtua->getConnectionName();
        $siswaTable = (new Siswa)->getTable();
        $siswaQualified = $connection
            ? $connection.'.'.$siswaTable
            : $siswaTable;

        $userTable = (new UserAccount)->getTable();
        $userConnection = (new UserAccount)->getConnectionName();
        $qualifiedUserTable = $userConnection
            ? $userConnection.'.'.$userTable
            : $userTable;

        return [
            'siswa_id' => 'required|integer|exists:'.$siswaQualified.',id',
            'user_id' => 'nullable|integer|exists:'.$qualifiedUserTable.',id',
            'nama' => 'required|string|max:150',
            'hubungan' => 'required|in:ayah,ibu,wali',
            'no_hp' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'siswa_id.required' => 'Siswa wajib dipilih',
            'siswa_id.exists' => 'Siswa tidak valid',
            'nama.required' => 'Nama orang tua wajib diisi',
            'nama.max' => 'Nama maksimal 150 karakter',
            'hubungan.required' => 'Hubungan wajib dipilih',
            'hubungan.in' => 'Hubungan tidak valid',
            'no_hp.max' => 'No HP maksimal 50 karakter',
            'pekerjaan.max' => 'Pekerjaan maksimal 100 karakter',
            'user_id.exists' => 'Akun user tidak valid',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->filled('user_id') ? $this->input('user_id') : null,
        ]);
    }
}
