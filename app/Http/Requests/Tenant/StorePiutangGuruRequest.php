<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Guru;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePiutangGuruRequest extends FormRequest
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
            'keterangan_hutang' => ['required', 'string', 'max:255'],
            'nominal_hutang' => ['required', 'numeric', 'min:0'],
            'waktu_hutang' => ['required', 'date'],
            'input_ke_pemotongan' => ['required', Rule::in(['ya', 'tidak'])],
            'waktu_pemotongan' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nominal_hutang' => $this->sanitizeNominal($this->nominal_hutang),
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
            'keterangan_hutang' => 'keterangan hutang',
            'nominal_hutang' => 'nominal hutang',
            'waktu_hutang' => 'waktu hutang',
            'input_ke_pemotongan' => 'input ke pemotongan',
            'waktu_pemotongan' => 'waktu pemotongan',
        ];
    }

    public function messages(): array
    {
        return [
            'guru_id.required' => 'Guru harus dipilih',
            'guru_id.exists' => 'Guru tidak valid',
            'keterangan_hutang.required' => 'Keterangan hutang harus diisi',
            'keterangan_hutang.max' => 'Keterangan hutang maksimal 255 karakter',
            'nominal_hutang.required' => 'Nominal hutang harus diisi',
            'nominal_hutang.numeric' => 'Nominal hutang harus berupa angka',
            'nominal_hutang.min' => 'Nominal hutang minimal 0',
            'waktu_hutang.required' => 'Waktu hutang harus diisi',
            'waktu_hutang.date' => 'Format waktu hutang tidak valid',
            'input_ke_pemotongan.required' => 'Input ke pemotongan harus dipilih',
            'input_ke_pemotongan.in' => 'Input ke pemotongan harus ya atau tidak',
            'waktu_pemotongan.date' => 'Format waktu pemotongan tidak valid',
        ];
    }
}
