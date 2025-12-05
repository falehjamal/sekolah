<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTagihanManualRequest;
use App\Http\Requests\Tenant\UpdateTagihanManualRequest;
use App\Models\Tenant\MetodePembayaran;
use App\Models\Tenant\Rekening;
use App\Models\Tenant\Siswa;
use App\Models\Tenant\TagihanSpp;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class TagihanManualController extends Controller
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
            return $this->datatable();
        }

        $siswaList = Siswa::query()
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nis', 'nama']);

        $metodePembayaranList = MetodePembayaran::query()
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $rekeningList = Rekening::query()
            ->orderBy('bank')
            ->get(['id', 'bank', 'no_rekening', 'nama_rekening']);

        return view('tenant.tagihan-manual.index', [
            'siswaList' => $siswaList,
            'metodePembayaranList' => $metodePembayaranList,
            'rekeningList' => $rekeningList,
        ]);
    }

    /**
     * Get SPP info for a student in a specific month
     */
    public function getSppInfo(Request $request): JsonResponse
    {
        $siswaId = $request->input('siswa_id');
        $bulan = $request->input('bulan');
        $tagihanId = $request->input('tagihan_id'); // untuk exclude tagihan yang sedang diedit

        if (!$siswaId || !$bulan) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa dan bulan harus diisi',
            ], 400);
        }

        $siswa = Siswa::with('spp')->find($siswaId);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan',
            ], 404);
        }

        if (!$siswa->spp) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa belum memiliki data SPP',
            ], 400);
        }

        $nominalSpp = (float) $siswa->spp->nominal;

        // Hitung total yang sudah dibayar untuk bulan tersebut (nominal + diskon)
        $query = TagihanSpp::query()
            ->where('siswa_id', $siswaId)
            ->where('bulan', $bulan);

        // Exclude tagihan yang sedang diedit
        if ($tagihanId) {
            $query->where('id', '!=', $tagihanId);
        }

        $result = $query
            ->selectRaw('COALESCE(SUM(nominal), 0) as total_nominal, COALESCE(SUM(diskon), 0) as total_diskon')
            ->first();

        $totalNominal = (float) ($result->total_nominal ?? 0);
        $totalDiskon = (float) ($result->total_diskon ?? 0);
        $totalDibayar = $totalNominal + $totalDiskon;
        $sisaKekurangan = $nominalSpp - $totalDibayar;
        $isLunas = $sisaKekurangan <= 0;

        return response()->json([
            'success' => true,
            'data' => [
                'siswa_nama' => $siswa->nama,
                'spp_nama' => $siswa->spp->nama,
                'nominal_spp' => $nominalSpp,
                'nominal_spp_format' => 'Rp ' . number_format($nominalSpp, 0, ',', '.'),
                'total_nominal' => $totalNominal,
                'total_nominal_format' => 'Rp ' . number_format($totalNominal, 0, ',', '.'),
                'total_diskon' => $totalDiskon,
                'total_diskon_format' => 'Rp ' . number_format($totalDiskon, 0, ',', '.'),
                'total_dibayar' => $totalDibayar,
                'total_dibayar_format' => 'Rp ' . number_format($totalDibayar, 0, ',', '.'),
                'sisa_kekurangan' => max(0, $sisaKekurangan),
                'sisa_kekurangan_format' => 'Rp ' . number_format(max(0, $sisaKekurangan), 0, ',', '.'),
                'is_lunas' => $isLunas,
            ],
        ]);
    }

    public function datatable(): JsonResponse
    {
        $tagihan = TagihanSpp::query()->with(['siswa', 'metodePembayaran', 'rekening']);

        return DataTables::of($tagihan)
            ->addIndexColumn()
            ->addColumn('siswa_info', function (TagihanSpp $row): string {
                $siswa = $row->siswa;
                if (!$siswa) {
                    return '<span class="text-muted">Siswa tidak ditemukan</span>';
                }

                $initial = strtoupper(mb_substr($siswa->nama, 0, 1));

                return '
                    <div class="table-card">
                        <div class="table-avatar">'.$initial.'</div>
                        <div class="table-card__body">
                            <div class="table-card__title">'.$siswa->nama.'</div>
                            <ul class="table-meta">
                                <li><span>NIS</span>'.$siswa->nis.'</li>
                            </ul>
                        </div>
                    </div>';
            })
            ->addColumn('bulan_format', function (TagihanSpp $row): string {
                return $row->bulan_format;
            })
            ->addColumn('nominal_format', function (TagihanSpp $row): string {
                return '<span class="fw-semibold text-success">'.$row->nominal_format.'</span>';
            })
            ->addColumn('diskon_format', function (TagihanSpp $row): string {
                $diskon = (float) $row->diskon;
                if ($diskon > 0) {
                    return '<span class="fw-semibold text-info">'.$row->diskon_format.'</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('total_bayar_format', function (TagihanSpp $row): string {
                return '<span class="fw-semibold text-primary">'.$row->total_bayar_format.'</span>';
            })
            ->addColumn('tanggal_bayar_format', function (TagihanSpp $row): string {
                return $row->tanggal_bayar
                    ? Carbon::parse($row->tanggal_bayar)->translatedFormat('d M Y')
                    : '-';
            })
            ->addColumn('metode_badge', function (TagihanSpp $row): string {
                return '<div class="status-pill">'.$row->metode_badge.'</div>';
            })
            ->addColumn('rekening_info', function (TagihanSpp $row): string {
                if (!$row->rekening) {
                    return '<span class="text-muted">-</span>';
                }
                return '<small>'.$row->rekening->bank.'<br><span class="text-muted">'.$row->rekening->no_rekening.'</span></small>';
            })
            ->addColumn('keterangan_text', function (TagihanSpp $row): string {
                $keterangan = $row->keterangan ?? '-';
                if (strlen($keterangan) > 30) {
                    $keterangan = substr($keterangan, 0, 30) . '...';
                }
                return '<span class="text-muted small">'.$keterangan.'</span>';
            })
            ->addColumn('action', function (TagihanSpp $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['siswa_info', 'nominal_format', 'diskon_format', 'total_bayar_format', 'metode_badge', 'rekening_info', 'keterangan_text', 'action'])
            ->make(true);
    }

    /**
     * Get payment history for a student
     */
    public function history(Request $request): JsonResponse
    {
        $siswaId = $request->input('siswa_id');
        $excludeId = $request->input('exclude_id');

        if (!$siswaId) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa harus dipilih',
                'data' => [],
            ]);
        }

        $query = TagihanSpp::query()
            ->with(['metodePembayaran', 'rekening'])
            ->where('siswa_id', $siswaId)
            ->orderBy('created_at', 'desc');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $history = $query->get();

        $data = $history->map(function (TagihanSpp $item) {
            return [
                'id' => $item->id,
                'bulan' => $item->bulan,
                'bulan_format' => $item->bulan_format,
                'nominal' => $item->nominal,
                'nominal_format' => $item->nominal_format,
                'diskon' => $item->diskon,
                'diskon_format' => $item->diskon_format,
                'total_bayar' => $item->total_bayar,
                'total_bayar_format' => $item->total_bayar_format,
                'tanggal_bayar' => $item->tanggal_bayar
                    ? Carbon::parse($item->tanggal_bayar)->format('Y-m-d')
                    : null,
                'tanggal_bayar_format' => $item->tanggal_bayar
                    ? Carbon::parse($item->tanggal_bayar)->translatedFormat('d M Y')
                    : '-',
                'metode_nama' => $item->metodePembayaran?->nama,
                'rekening_info' => $item->rekening
                    ? $item->rekening->bank . ' - ' . $item->rekening->no_rekening
                    : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(StoreTagihanManualRequest $request): JsonResponse
    {
        $connection = (new TagihanSpp)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $tagihan = TagihanSpp::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data tagihan berhasil ditambahkan',
                'data' => $tagihan->load(['siswa', 'metodePembayaran', 'rekening']),
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(TagihanSpp $tagihanManual): JsonResponse
    {
        $tagihanManual->load(['siswa', 'metodePembayaran', 'rekening']);

        return response()->json([
            'success' => true,
            'data' => $tagihanManual,
        ]);
    }

    public function update(UpdateTagihanManualRequest $request, TagihanSpp $tagihanManual): JsonResponse
    {
        $connection = $tagihanManual->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $tagihanManual->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data tagihan berhasil diperbarui',
                'data' => $tagihanManual->load(['siswa', 'metodePembayaran', 'rekening']),
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(TagihanSpp $tagihanManual): JsonResponse
    {
        $connection = $tagihanManual->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $tagihanManual->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data tagihan berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
