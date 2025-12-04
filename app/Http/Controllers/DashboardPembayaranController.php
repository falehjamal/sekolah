<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Siswa;
use App\Models\Tenant\TagihanSpp;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardPembayaranController extends Controller
{
    public function __construct(protected TenantConnectionManager $tenantConnection)
    {
        $this->middleware(['auth', 'permission:dashboard.view']);
        $this->middleware(function ($request, $next) {
            if (session()->has('tenant_connection')) {
                $this->tenantConnection->connectFromSession();
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        $stats = $this->getStats();
        $chartData = $this->getChartData();

        return view('dashboard-pembayaran', [
            'stats' => $stats,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Get dashboard statistics from database
     */
    private function getStats(): array
    {
        $now = Carbon::now();
        $bulanIni = $now->format('Y-m');
        $hariIni = $now->toDateString();

        // Get all active students with their SPP
        $siswaAktif = Siswa::query()
            ->with('spp')
            ->where('status', 'aktif')
            ->get();

        // Calculate total tagihan (all active students * their SPP nominal for current month)
        $totalTagihanJumlah = $siswaAktif->count();
        $totalTagihanNominal = $siswaAktif->sum(fn ($siswa) => $siswa->spp?->nominal ?? 0);

        // Get all payments for current month
        $pembayaranBulanIni = TagihanSpp::query()
            ->where('bulan', $bulanIni)
            ->get();

        // Calculate SPP statistics
        $sppLunasJumlah = 0;
        $sppLunasNominal = 0;
        $sppBelumLunasJumlah = 0;
        $sppBelumLunasNominal = 0;

        foreach ($siswaAktif as $siswa) {
            $nominalSpp = $siswa->spp?->nominal ?? 0;
            $totalDibayar = $pembayaranBulanIni
                ->where('siswa_id', $siswa->id)
                ->sum('nominal');

            if ($nominalSpp > 0 && $totalDibayar >= $nominalSpp) {
                // Lunas
                $sppLunasJumlah++;
                $sppLunasNominal += $nominalSpp;
            } else {
                // Belum lunas
                $sppBelumLunasJumlah++;
                $sppBelumLunasNominal += max(0, $nominalSpp - $totalDibayar);
            }
        }

        // Pemasukan SPP bulan ini (total pembayaran bulan ini)
        $pemasukanSppBulanIni = $pembayaranBulanIni->sum('nominal');

        // Pemasukan SPP hari ini
        $pemasukanSppHariIni = TagihanSpp::query()
            ->whereDate('tanggal_bayar', $hariIni)
            ->sum('nominal');

        // Total deposit (untuk sementara 0, sesuaikan jika ada tabel deposit)
        $totalDeposit = 0;

        // Pengeluaran bulan ini (untuk sementara 0, sesuaikan jika ada tabel pengeluaran)
        $pengeluaranBulanIni = 0;

        // Balance bulan ini
        $balanceBulanIni = $pemasukanSppBulanIni - $pengeluaranBulanIni;

        return [
            'total_tagihan' => [
                'jumlah' => $totalTagihanJumlah,
                'nominal' => $totalTagihanNominal,
            ],
            'spp_lunas' => [
                'jumlah' => $sppLunasJumlah,
                'nominal' => $sppLunasNominal,
            ],
            'spp_belum_lunas' => [
                'jumlah' => $sppBelumLunasJumlah,
                'nominal' => $sppBelumLunasNominal,
            ],
            'pemasukan_spp_bulan_ini' => $pemasukanSppBulanIni,
            'pemasukan_spp_hari_ini' => $pemasukanSppHariIni,
            'pengeluaran_bulan_ini' => $pengeluaranBulanIni,
            'balance_bulan_ini' => $balanceBulanIni,
            'total_deposit' => $totalDeposit,
        ];
    }

    /**
     * Get chart data for yearly income
     */
    private function getChartData(): array
    {
        $tahun = Carbon::now()->year;
        $categories = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data = [];

        // Get monthly income for each month of the year
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $bulanFormatted = sprintf('%04d-%02d', $tahun, $bulan);

            $totalPemasukan = TagihanSpp::query()
                ->where('bulan', $bulanFormatted)
                ->sum('nominal');

            $data[] = (float) $totalPemasukan;
        }

        return [
            'categories' => $categories,
            'data' => $data,
        ];
    }
}
