<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Orangtua;
use App\Models\Tenant\Siswa;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrangtuaRequest extends FormRequest
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
        $siswaQualified = $connection ? $connection.'.'.$siswaTable : $siswaTable;

        return [
            'siswa_id' => 'required|integer|exists:'.$siswaQualified.',id',
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
        ];
    }
}
