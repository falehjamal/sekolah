<?php

namespace App\Http\Controllers;

use App\Services\Tenant\TenantConnectionManager;
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
        // Data statis untuk sementara
        $stats = [
            'total_tagihan' => [
                'jumlah' => 150,
                'nominal' => 52500000,
            ],
            'spp_lunas' => [
                'jumlah' => 120,
                'nominal' => 42000000,
            ],
            'spp_belum_lunas' => [
                'jumlah' => 30,
                'nominal' => 10500000,
            ],
            'pemasukan_spp_bulan_ini' => 15000000,
            'pemasukan_spp_hari_ini' => 500000,
            'pengeluaran_bulan_ini' => 5000000,
            'balance_bulan_ini' => 10000000,
            'total_deposit' => 25000000,
        ];

        // Data grafik pemasukan setahun (12 bulan)
        $chartData = [
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'data' => [12000000, 15000000, 18000000, 14000000, 16000000, 19000000, 17000000, 20000000, 18000000, 16000000, 15000000, 17000000],
        ];

        return view('dashboard-pembayaran', [
            'stats' => $stats,
            'chartData' => $chartData,
        ]);
    }
}
