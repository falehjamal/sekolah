<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardSiswaController extends Controller
{
    public function __invoke(): View
    {
        $profile = [
            'name' => 'Rafi Alfaruq',
            'nis' => '2025.10.001',
            'kelas' => 'XI IPA 1',
            'jurusan' => 'Sains Terapan',
            'email' => 'rafi.alfaruq@example.test',
            'phone' => '0812 8899 1020',
        ];

        $attendance = [
            'summary' => [
                'hadir' => 82,
                'izin' => 4,
                'sakit' => 2,
                'alpha' => 1,
            ],
            'history' => [
                ['tanggal' => 'Sen, 27 Nov 2025', 'status' => 'Hadir', 'catatan' => 'Masuk tepat waktu'],
                ['tanggal' => 'Sel, 28 Nov 2025', 'status' => 'Hadir', 'catatan' => 'Ekskul basket sore'],
                ['tanggal' => 'Rab, 29 Nov 2025', 'status' => 'Izin', 'catatan' => 'Kontrol kesehatan'],
                ['tanggal' => 'Kam, 30 Nov 2025', 'status' => 'Hadir', 'catatan' => 'Presentasi kelompok'],
            ],
        ];

        $invoices = [
            ['bulan' => 'November 2025', 'kode' => 'SPP-1125-01', 'nominal' => 350000, 'jatuh_tempo' => '10 Nov 2025', 'status' => 'Lunas'],
            ['bulan' => 'Desember 2025', 'kode' => 'SPP-1225-02', 'nominal' => 350000, 'jatuh_tempo' => '10 Des 2025', 'status' => 'Menunggu'],
            ['bulan' => 'Januari 2026', 'kode' => 'SPP-0126-03', 'nominal' => 350000, 'jatuh_tempo' => '10 Jan 2026', 'status' => 'Belum Dibuat'],
        ];

        return view('dashboard.siswa', compact('profile', 'attendance', 'invoices'));
    }
}
