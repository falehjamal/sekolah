<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Jurusan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJurusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jurusan = new Jurusan;
        $table = $jurusan->getTable();
        $connection = $jurusan->getConnectionName();
        $qualifiedTable = $connection ? $connection.'.'.$table : $table;
        $currentId = $this->route('jurusan')?->getKey();

        return [
            'kode' => 'required|string|max:20|unique:'.$qualifiedTable.',kode,'.$currentId,
            'nama_jurusan' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode jurusan wajib diisi',
            'kode.unique' => 'Kode jurusan sudah digunakan',
            'nama_jurusan.required' => 'Nama jurusan wajib diisi',
            'nama_jurusan.max' => 'Nama jurusan maksimal 150 karakter',
        ];
    }
}
