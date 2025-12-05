<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRekeningRequest extends FormRequest
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
        return [
            'bank' => 'required|string|max:100',
            'no_rekening' => 'required|string|max:50',
            'nama_rekening' => 'required|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bank.required' => 'Nama bank harus diisi',
            'bank.max' => 'Nama bank maksimal 100 karakter',
            'no_rekening.required' => 'Nomor rekening harus diisi',
            'no_rekening.max' => 'Nomor rekening maksimal 50 karakter',
            'nama_rekening.required' => 'Nama pemilik rekening harus diisi',
            'nama_rekening.max' => 'Nama pemilik rekening maksimal 255 karakter',
        ];
    }
}
