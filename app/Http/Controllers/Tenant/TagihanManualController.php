<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTagihanManualRequest;
use App\Http\Requests\Tenant\UpdateTagihanManualRequest;
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

        return view('tenant.tagihan-manual.index', [
            'siswaList' => $siswaList,
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

        // Hitung total yang sudah dibayar untuk bulan tersebut
        $query = TagihanSpp::query()
            ->where('siswa_id', $siswaId)
            ->where('bulan', $bulan);

        // Exclude tagihan yang sedang diedit
        if ($tagihanId) {
            $query->where('id', '!=', $tagihanId);
        }

        $totalDibayar = (float) $query->sum('nominal');
        $sisaKekurangan = $nominalSpp - $totalDibayar;
        $isLunas = $sisaKekurangan <= 0;

        return response()->json([
            'success' => true,
            'data' => [
                'siswa_nama' => $siswa->nama,
                'spp_nama' => $siswa->spp->nama,
                'nominal_spp' => $nominalSpp,
                'nominal_spp_format' => 'Rp ' . number_format($nominalSpp, 0, ',', '.'),
                'total_dibayar' => $totalDibayar,
                'total_dibayar_format' => 'Rp ' . number_format($totalDibayar, 0, ',', '.'),
                'sisa_kekurangan' => $sisaKekurangan,
                'sisa_kekurangan_format' => 'Rp ' . number_format(max(0, $sisaKekurangan), 0, ',', '.'),
                'is_lunas' => $isLunas,
            ],
        ]);
    }

    public function datatable(): JsonResponse
    {
        $tagihan = TagihanSpp::query()->with(['siswa']);

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
            ->addColumn('tagihan_info', function (TagihanSpp $row): string {
                $tanggalBayar = $row->tanggal_bayar
                    ? Carbon::parse($row->tanggal_bayar)->translatedFormat('d F Y')
                    : '-';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Bulan</span>'.$row->bulan_format.'</li>
                            <li><span>Nominal</span>'.$row->nominal_format.'</li>
                            <li><span>Tgl Bayar</span>'.$tanggalBayar.'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('metode_badge', function (TagihanSpp $row): string {
                return '<div class="status-pill">'.$row->metode_badge.'</div>';
            })
            ->addColumn('keterangan_text', function (TagihanSpp $row): string {
                $keterangan = $row->keterangan ?? '-';
                if (strlen($keterangan) > 50) {
                    $keterangan = substr($keterangan, 0, 50) . '...';
                }
                return '<span class="text-muted">'.$keterangan.'</span>';
            })
            ->addColumn('action', function (TagihanSpp $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['siswa_info', 'tagihan_info', 'metode_badge', 'keterangan_text', 'action'])
            ->make(true);
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
                'data' => $tagihan->load(['siswa']),
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
        $tagihanManual->load(['siswa']);

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
                'data' => $tagihanManual->load(['siswa']),
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
