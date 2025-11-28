<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Jurusan;
use App\Models\Tenant\Kelas;
use Illuminate\Foundation\Http\FormRequest;

class StoreKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kelas = new Kelas;
        $kelasTable = $kelas->getTable();
        $jurusanTable = (new Jurusan)->getTable();
        $connection = $kelas->getConnectionName();
        $kelasQualified = $connection ? $connection.'.'.$kelasTable : $kelasTable;
        $jurusanQualified = $connection ? $connection.'.'.$jurusanTable : $jurusanTable;

        return [
            'nama_kelas' => 'required|string|max:100|unique:'.$kelasQualified.',nama_kelas',
            'tingkat' => 'required|integer|min:1|max:12',
            'jurusan_id' => 'nullable|integer|exists:'.$jurusanQualified.',id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kelas.required' => 'Nama kelas wajib diisi',
            'nama_kelas.unique' => 'Nama kelas sudah digunakan',
            'tingkat.required' => 'Tingkat kelas wajib diisi',
            'tingkat.integer' => 'Tingkat harus berupa angka',
            'tingkat.min' => 'Tingkat minimal 1',
            'tingkat.max' => 'Tingkat maksimal 12',
            'jurusan_id.exists' => 'Jurusan tidak valid',
        ];
    }
}
