<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Guru;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePemotonganGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'nama_pemotongan' => ['required', 'string', 'max:150'],
            'nominal_pemotongan' => ['required', 'numeric', 'min:0'],
            'waktu' => ['required', 'date'],
            'jenis_pemotongan' => ['required', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nominal_pemotongan' => $this->sanitizeNominal($this->nominal_pemotongan),
        ]);
    }

    private function sanitizeNominal(mixed $value): string
    {
        $numeric = preg_replace('/[^0-9.,]/', '', (string) ($value ?? ''));
        $numeric = str_replace('.', '', $numeric);

        return str_replace(',', '.', $numeric);
    }

    public function attributes(): array
    {
        return [
            'guru_id' => 'guru',
            'nama_pemotongan' => 'nama pemotongan',
            'nominal_pemotongan' => 'nominal pemotongan',
            'waktu' => 'waktu',
            'jenis_pemotongan' => 'jenis pemotongan',
        ];
    }

    public function messages(): array
    {
        return [
            'guru_id.required' => 'Guru harus dipilih',
            'guru_id.exists' => 'Guru tidak valid',
            'nama_pemotongan.required' => 'Nama pemotongan harus diisi',
            'nama_pemotongan.max' => 'Nama pemotongan maksimal 150 karakter',
            'nominal_pemotongan.required' => 'Nominal pemotongan harus diisi',
            'nominal_pemotongan.numeric' => 'Nominal pemotongan harus berupa angka',
            'nominal_pemotongan.min' => 'Nominal pemotongan minimal 0',
            'waktu.required' => 'Waktu harus diisi',
            'waktu.date' => 'Format waktu tidak valid',
            'jenis_pemotongan.required' => 'Jenis pemotongan harus diisi',
            'jenis_pemotongan.max' => 'Jenis pemotongan maksimal 100 karakter',
        ];
    }
}
