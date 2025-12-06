<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Guru;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTunjanganGuruRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guru = new Guru();
        $guruTable = $guru->getTable();
        $guruConnection = $guru->getConnectionName();

        return [
            'guru_id' => [
                'required',
                'integer',
                Rule::exists($guruConnection . '.' . $guruTable, 'id'),
            ],
            'nama_tunjangan' => ['required', 'string', 'max:150'],
            'nominal_tunjangan' => ['required', 'numeric', 'min:0'],
            'waktu' => ['required', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nominal_tunjangan' => $this->sanitizeNominal($this->nominal_tunjangan),
        ]);
    }

    private function sanitizeNominal(mixed $value): string
    {
        $numeric = preg_replace('/[^0-9.,]/', '', (string) ($value ?? ''));
        $numeric = str_replace('.', '', $numeric);

        return str_replace(',', '.', $numeric);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'guru_id' => 'guru',
            'nama_tunjangan' => 'nama tunjangan',
            'nominal_tunjangan' => 'nominal tunjangan',
            'waktu' => 'waktu',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'guru_id.required' => 'Guru harus dipilih',
            'guru_id.exists' => 'Guru tidak valid',
            'nama_tunjangan.required' => 'Nama tunjangan harus diisi',
            'nama_tunjangan.max' => 'Nama tunjangan maksimal 150 karakter',
            'nominal_tunjangan.required' => 'Nominal tunjangan harus diisi',
            'nominal_tunjangan.numeric' => 'Nominal tunjangan harus berupa angka',
            'nominal_tunjangan.min' => 'Nominal tunjangan minimal 0',
            'waktu.required' => 'Waktu harus diisi',
            'waktu.date' => 'Format waktu tidak valid',
        ];
    }
}
