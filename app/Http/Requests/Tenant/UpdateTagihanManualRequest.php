<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Siswa;
use App\Models\Tenant\TagihanSpp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateTagihanManualRequest extends FormRequest
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

        return [
            'siswa_id' => 'required|integer|exists:'.$siswaTable.',id',
            'bulan' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'nominal' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
            'metode_bayar' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'siswa_id.required' => 'Siswa harus dipilih',
            'siswa_id.exists' => 'Siswa tidak valid',
            'bulan.required' => 'Bulan harus diisi',
            'bulan.regex' => 'Format bulan tidak valid (gunakan YYYY-MM)',
            'nominal.required' => 'Nominal harus diisi',
            'nominal.numeric' => 'Nominal harus berupa angka',
            'nominal.min' => 'Nominal harus lebih dari 0',
            'tanggal_bayar.required' => 'Tanggal bayar harus diisi',
            'tanggal_bayar.date' => 'Format tanggal bayar tidak valid',
            'metode_bayar.max' => 'Metode bayar maksimal 50 karakter',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateSppLimit($validator);
        });
    }

    /**
     * Validate that payment doesn't exceed SPP limit
     */
    protected function validateSppLimit(Validator $validator): void
    {
        $siswaId = $this->input('siswa_id');
        $bulan = $this->input('bulan');
        $nominal = (float) $this->input('nominal');
        $tagihanId = $this->route('tagihan_manual')?->id;

        if (!$siswaId || !$bulan || $nominal <= 0) {
            return;
        }

        $siswa = Siswa::with('spp')->find($siswaId);

        if (!$siswa || !$siswa->spp) {
            $validator->errors()->add('siswa_id', 'Siswa belum memiliki data SPP');
            return;
        }

        $nominalSpp = (float) $siswa->spp->nominal;

        // Hitung total yang sudah dibayar untuk bulan tersebut (exclude current tagihan)
        $query = TagihanSpp::query()
            ->where('siswa_id', $siswaId)
            ->where('bulan', $bulan);

        if ($tagihanId) {
            $query->where('id', '!=', $tagihanId);
        }

        $totalDibayar = (float) $query->sum('nominal');

        $sisaKekurangan = $nominalSpp - $totalDibayar;

        if ($sisaKekurangan <= 0) {
            $validator->errors()->add('nominal', 'SPP untuk bulan ini sudah lunas');
            return;
        }

        if ($nominal > $sisaKekurangan) {
            $validator->errors()->add('nominal', 'Nominal pembayaran melebihi sisa kekurangan (Rp ' . number_format($sisaKekurangan, 0, ',', '.') . ')');
        }
    }

    protected function qualifiedTable(Model $model): string
    {
        $table = $model->getTable();
        $connection = $model->getConnectionName();

        return $connection ? $connection.'.'.$table : $table;
    }
}
