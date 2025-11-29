<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\MataPelajaran;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMataPelajaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mataPelajaran = new MataPelajaran;
        $table = $mataPelajaran->getTable();
        $connection = $mataPelajaran->getConnectionName();
        $qualifiedTable = $connection ? $connection.'.'.$table : $table;
        $currentId = $this->route('mata_pelajaran')?->getKey();

        return [
            'kode' => 'required|string|max:20|unique:'.$qualifiedTable.',kode,'.$currentId,
            'nama_mapel' => 'required|string|max:150',
            'kurikulum' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode mata pelajaran wajib diisi',
            'kode.unique' => 'Kode mata pelajaran sudah digunakan',
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi',
            'nama_mapel.max' => 'Nama mata pelajaran maksimal 150 karakter',
            'kurikulum.max' => 'Kurikulum maksimal 50 karakter',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
        ];
    }
}
