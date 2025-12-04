<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Jurusan;
use App\Models\Tenant\Kelas;
use App\Models\Tenant\Siswa;
use App\Models\Tenant\Spp;
use App\Models\Tenant\UserAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaRequest extends FormRequest
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
        $siswaTable = $this->qualifiedTable(new Siswa);
        $jurusanTable = $this->qualifiedTable(new Jurusan);
        $kelasTable = $this->qualifiedTable(new Kelas);
        $sppTable = $this->qualifiedTable(new Spp);

        return [
            'user_id' => $this->userRule(),
            'nis' => 'required|string|max:20|unique:'.$siswaTable.',nis',
            'nisn' => 'required|string|max:20|unique:'.$siswaTable.',nisn',
            'nama' => 'required|string|max:255',
            'jk' => 'required|in:l,p',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'kelas_id' => 'required|integer|exists:'.$kelasTable.',id',
            'jurusan_id' => 'required|integer|exists:'.$jurusanTable.',id',
            'spp_id' => 'required|integer|exists:'.$sppTable.',id',
            'no_hp' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
            'status' => 'required|in:aktif,alumni,keluar',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nis.required' => 'NIS harus diisi',
            'nis.unique' => 'NIS sudah terdaftar',
            'nisn.required' => 'NISN harus diisi',
            'nisn.unique' => 'NISN sudah terdaftar',
            'nama.required' => 'Nama harus diisi',
            'jk.required' => 'Jenis kelamin harus dipilih',
            'jk.in' => 'Jenis kelamin tidak valid',
            'tempat_lahir.required' => 'Tempat lahir harus diisi',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'alamat.required' => 'Alamat harus diisi',
            'kelas_id.required' => 'Kelas harus dipilih',
            'kelas_id.exists' => 'Kelas tidak valid',
            'jurusan_id.required' => 'Jurusan harus dipilih',
            'jurusan_id.exists' => 'Jurusan tidak valid',
            'spp_id.required' => 'SPP harus dipilih',
            'spp_id.exists' => 'SPP tidak valid',
            'tanggal_masuk.required' => 'Tanggal masuk harus diisi',
            'tanggal_masuk.date' => 'Format tanggal masuk tidak valid',
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak valid',
            'user_id.exists' => 'Akun user tidak valid',
            'user_id.unique' => 'Akun user sudah terhubung dengan siswa lain',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->filled('user_id') ? (int) $this->input('user_id') : null,
        ]);
    }

    protected function userRule(?int $ignoreId = null): string
    {
        $userTable = $this->qualifiedTable(new UserAccount);
        $siswaTable = $this->qualifiedTable(new Siswa);

        $uniqueRule = 'unique:'.$siswaTable.',user_id';

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
