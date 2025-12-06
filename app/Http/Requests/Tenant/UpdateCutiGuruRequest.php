<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCutiGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guru_id' => 'required|integer',
            'jenis_cuti' => 'required|string|max:100',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'status_approval' => 'required|in:pending,approved,rejected',
        ];
    }

    public function messages(): array
    {
        return [
            'guru_id.required' => 'Guru harus dipilih',
            'guru_id.integer' => 'Guru tidak valid',
            'jenis_cuti.required' => 'Jenis cuti harus diisi',
            'jenis_cuti.max' => 'Jenis cuti maksimal 100 karakter',
            'tanggal_awal.required' => 'Tanggal awal harus diisi',
            'tanggal_awal.date' => 'Format tanggal awal tidak valid',
            'tanggal_akhir.required' => 'Tanggal akhir harus diisi',
            'tanggal_akhir.date' => 'Format tanggal akhir tidak valid',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal',
            'status_approval.required' => 'Status approval harus dipilih',
            'status_approval.in' => 'Status approval tidak valid',
        ];
    }
}
