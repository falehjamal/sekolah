<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreKelasRequest;
use App\Http\Requests\Tenant\UpdateKelasRequest;
use App\Models\Tenant\Jurusan;
use App\Models\Tenant\Kelas;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class KelasController extends Controller
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
        if ($request->ajax()) {
            return $this->datatable();
        }

        $jurusanList = Jurusan::query()
            ->orderBy('nama_jurusan')
            ->get(['id', 'kode', 'nama_jurusan']);

        return view('tenant.kelas.index', [
            'jurusanList' => $jurusanList,
        ]);
    }

    public function datatable(): JsonResponse
    {
        $kelas = Kelas::query()->with('jurusan');

        return DataTables::of($kelas)
            ->addIndexColumn()
            ->addColumn('info_card', function (Kelas $row): string {
                $initial = strtoupper(mb_substr($row->nama_kelas, 0, 1));
                $jurusan = $row->jurusan ? $row->jurusan->nama_jurusan : 'Belum diatur';

                return '
                    <div class="table-card">
                        <div class="table-avatar avatar-indigo">'.$initial.'</div>
                        <div class="table-card__body">
                            <div class="table-card__title">'.$row->nama_kelas.'</div>
                            <ul class="table-meta">
                                <li><span>Tingkat</span>'.$row->tingkat.'</li>
                                <li><span>Jurusan</span>'.$jurusan.'</li>
                            </ul>
                        </div>
                    </div>';
            })
            ->addColumn('detail_card', function (Kelas $row): string {
                $jurusanKode = $row->jurusan?->kode ?? '-';
                $jurusanNama = $row->jurusan?->nama_jurusan ?? 'Belum diatur';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Kode Jurusan</span>'.$jurusanKode.'</li>
                            <li><span>Nama Jurusan</span>'.$jurusanNama.'</li>
                            <li><span>Dibuat</span>'.$row->created_at?->translatedFormat('d M Y').'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('action', function (Kelas $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editKelas('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusKelas('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['info_card', 'detail_card', 'action'])
            ->make(true);
    }

    public function store(StoreKelasRequest $request): JsonResponse
    {
        $connection = (new Kelas)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $kelas = Kelas::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kelas berhasil ditambahkan',
                'data' => $kelas,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data kelas',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(Kelas $kelas): JsonResponse
    {
        $kelas->load('jurusan');

        return response()->json([
            'success' => true,
            'data' => $kelas,
        ]);
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas): JsonResponse
    {
        $connection = $kelas->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $kelas->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kelas berhasil diperbarui',
                'data' => $kelas,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data kelas',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Kelas $kelas): JsonResponse
    {
        $connection = $kelas->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $kelas->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kelas berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data kelas',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
