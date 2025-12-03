<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\MetodePembayaran;
use App\Models\Tenant\Rekening;
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
        $metodeTable = $this->qualifiedTable(new MetodePembayaran);
        $rekeningTable = $this->qualifiedTable(new Rekening);

        return [
            'siswa_id' => 'required|integer|exists:'.$siswaTable.',id',
            'bulan' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'nominal' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
            'metode_pembayaran_id' => 'required|integer|exists:'.$metodeTable.',id',
            'rekening_id' => 'nullable|integer|exists:'.$rekeningTable.',id',
            'petugas_id' => 'required|integer',
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
            'metode_pembayaran_id.required' => 'Metode pembayaran harus dipilih',
            'metode_pembayaran_id.exists' => 'Metode pembayaran tidak valid',
            'rekening_id.exists' => 'Rekening tidak valid',
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
            $this->validateRekeningForTransfer($validator);
        });
    }

    /**
     * Validate that rekening is required for transfer payment
     */
    protected function validateRekeningForTransfer(Validator $validator): void
    {
        $metodePembayaranId = $this->input('metode_pembayaran_id');
        $rekeningId = $this->input('rekening_id');

        if (!$metodePembayaranId) {
            return;
        }

        $metode = MetodePembayaran::find($metodePembayaranId);

        if ($metode && strtolower($metode->nama) === 'transfer' && empty($rekeningId)) {
            $validator->errors()->add('rekening_id', 'Rekening harus dipilih untuk pembayaran transfer');
        }
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
