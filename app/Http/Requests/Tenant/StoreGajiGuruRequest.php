<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreGajiGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guru_id' => 'required|integer',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'tanggal_bergabung' => 'nullable|date',
            'jenis_gaji' => 'required|in:harian,bulanan',
            'gaji_pokok' => 'required|numeric|min:0',
            'uang_makan' => 'nullable|numeric|min:0',
            'uang_transport' => 'nullable|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'tunjangan_lain' => 'nullable|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    public function messages(): array
    {
        return [
            'guru_id.required' => 'Guru harus dipilih',
            'guru_id.integer' => 'Guru tidak valid',
            'tempat_lahir.max' => 'Tempat lahir maksimal 100 karakter',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'tanggal_bergabung.date' => 'Format tanggal bergabung tidak valid',
            'jenis_gaji.required' => 'Jenis gaji harus dipilih',
            'jenis_gaji.in' => 'Jenis gaji tidak valid',
            'gaji_pokok.required' => 'Gaji pokok harus diisi',
            'gaji_pokok.numeric' => 'Gaji pokok harus berupa angka',
            'gaji_pokok.min' => 'Gaji pokok tidak boleh negatif',
            'uang_makan.numeric' => 'Uang makan harus berupa angka',
            'uang_makan.min' => 'Uang makan tidak boleh negatif',
            'uang_transport.numeric' => 'Uang transport harus berupa angka',
            'uang_transport.min' => 'Uang transport tidak boleh negatif',
            'tunjangan_jabatan.numeric' => 'Tunjangan jabatan harus berupa angka',
            'tunjangan_jabatan.min' => 'Tunjangan jabatan tidak boleh negatif',
            'tunjangan_lain.numeric' => 'Tunjangan lain harus berupa angka',
            'tunjangan_lain.min' => 'Tunjangan lain tidak boleh negatif',
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak valid',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'uang_makan' => $this->uang_makan ?? 0,
            'uang_transport' => $this->uang_transport ?? 0,
            'tunjangan_jabatan' => $this->tunjangan_jabatan ?? 0,
            'tunjangan_lain' => $this->tunjangan_lain ?? 0,
        ]);
    }
}
