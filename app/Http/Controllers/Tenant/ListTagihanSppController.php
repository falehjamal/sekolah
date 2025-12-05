<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Kelas;
use App\Models\Tenant\Siswa;
use App\Models\Tenant\TagihanSpp;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class ListTagihanSppController extends Controller
{
    public function __construct(protected TenantConnectionManager $tenantConnection)
    {
        $this->middleware(function ($request, $next) {
            if (session()->has('tenant_connection')) {
                $this->tenantConnection->connectFromSession();
            }

            return $next($request);
        });
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() && $request->has('draw')) {
            return $this->datatable($request);
        }

        $kelasList = Kelas::query()
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat']);

        $siswaList = Siswa::query()
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nis', 'nama']);

        // Default filter values
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Generate year options (5 years back to current year)
        $years = range($currentYear - 5, $currentYear);

        return view('tenant.list-tagihan-spp.index', [
            'kelasList' => $kelasList,
            'siswaList' => $siswaList,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'years' => $years,
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);
        $siswaId = $request->input('siswa_id');
        $kelasId = $request->input('kelas_id');

        // Format bulan untuk query (YYYY-MM)
        $bulanFormatted = sprintf('%04d-%02d', $tahun, $bulan);

        // Batas tanggal masuk: akhir bulan filter
        $batasTanggalMasuk = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        // Query siswa aktif yang tanggal_masuk <= batas
        $query = Siswa::query()
            ->with(['kelas', 'jurusan', 'spp'])
            ->where('status', 'aktif')
            ->whereNotNull('tanggal_masuk')
            ->whereDate('tanggal_masuk', '<=', $batasTanggalMasuk);

        // Filter by siswa
        if ($siswaId) {
            $query->where('id', $siswaId);
        }

        // Filter by kelas
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        // Get siswa data
        $siswaData = $query->get();

        // Prepare data with tagihan sum
        $data = $siswaData->map(function (Siswa $siswa) use ($bulanFormatted) {
            // Hitung total tagihan yang sudah dibayar untuk bulan tersebut (nominal + diskon)
            $result = TagihanSpp::query()
                ->where('siswa_id', $siswa->id)
                ->where('bulan', $bulanFormatted)
                ->selectRaw('COALESCE(SUM(nominal), 0) as total_nominal, COALESCE(SUM(diskon), 0) as total_diskon')
                ->first();

            $totalNominal = (float) ($result->total_nominal ?? 0);
            $totalDiskon = (float) ($result->total_diskon ?? 0);
            $totalDibayar = $totalNominal + $totalDiskon;

            $nominalSpp = $siswa->spp?->nominal ?? 0;
            $sisaTagihan = max(0, $nominalSpp - $totalDibayar);
            $statusLunas = $totalDibayar >= $nominalSpp && $nominalSpp > 0;

            return [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas?->nama_kelas ?? '-',
                'jurusan' => $siswa->jurusan?->nama_jurusan ?? '-',
                'nominal_spp' => $nominalSpp,
                'nominal_spp_format' => 'Rp ' . number_format($nominalSpp, 0, ',', '.'),
                'total_nominal' => $totalNominal,
                'total_nominal_format' => 'Rp ' . number_format($totalNominal, 0, ',', '.'),
                'total_diskon' => $totalDiskon,
                'total_diskon_format' => 'Rp ' . number_format($totalDiskon, 0, ',', '.'),
                'total_dibayar' => $totalDibayar,
                'total_dibayar_format' => 'Rp ' . number_format($totalDibayar, 0, ',', '.'),
                'sisa_tagihan' => $sisaTagihan,
                'sisa_tagihan_format' => 'Rp ' . number_format($sisaTagihan, 0, ',', '.'),
                'status_lunas' => $statusLunas,
            ];
        });

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('siswa_info', function ($row): string {
                $initial = strtoupper(mb_substr($row['nama'], 0, 1));

                return '
                    <div class="table-card">
                        <div class="table-avatar">' . $initial . '</div>
                        <div class="table-card__body">
                            <div class="table-card__title">' . $row['nama'] . '</div>
                            <ul class="table-meta">
                                <li><span>NIS</span>' . $row['nis'] . '</li>
                            </ul>
                        </div>
                    </div>';
            })
            ->addColumn('kelas_info', function ($row): string {
                return '<span class="fw-medium">' . $row['kelas'] . '</span><br>
                        <small class="text-muted">' . $row['jurusan'] . '</small>';
            })
            ->addColumn('nominal_spp_display', function ($row): string {
                return '<span class="fw-semibold">' . $row['nominal_spp_format'] . '</span>';
            })
            ->addColumn('total_dibayar_display', function ($row): string {
                $class = $row['total_dibayar'] > 0 ? 'text-success' : 'text-muted';
                return '<span class="fw-semibold ' . $class . '">' . $row['total_dibayar_format'] . '</span>';
            })
            ->addColumn('sisa_tagihan_display', function ($row): string {
                $class = $row['sisa_tagihan'] > 0 ? 'text-danger' : 'text-success';
                return '<span class="fw-semibold ' . $class . '">' . $row['sisa_tagihan_format'] . '</span>';
            })
            ->addColumn('status_badge', function ($row): string {
                if ($row['status_lunas']) {
                    return '<span class="badge bg-label-success rounded-pill px-3">Lunas</span>';
                }

                if ($row['total_dibayar'] > 0) {
                    return '<span class="badge bg-label-warning rounded-pill px-3">Sebagian</span>';
                }

                return '<span class="badge bg-label-danger rounded-pill px-3">Belum Bayar</span>';
            })
            ->rawColumns(['siswa_info', 'kelas_info', 'nominal_spp_display', 'total_dibayar_display', 'sisa_tagihan_display', 'status_badge'])
            ->make(true);
    }
}
