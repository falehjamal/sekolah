<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSiswaRequest;
use App\Http\Requests\Tenant\UpdateSiswaRequest;
use App\Models\Tenant\Jurusan;
use App\Models\Tenant\Kelas;
use App\Models\Tenant\Siswa;
use App\Models\Tenant\Spp;
use App\Models\Tenant\UserAccount;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class SiswaController extends Controller
{
    public function __construct(protected TenantConnectionManager $tenantConnection)
    {
        // Pastikan tenant connection sudah aktif sebelum semua method
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

        $kelasList = Kelas::query()
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat']);

        $sppList = Spp::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'nominal', 'status']);

        $userAccounts = UserAccount::query()
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('tenant.siswa.index', [
            'jurusanList' => $jurusanList,
            'kelasList' => $kelasList,
            'sppList' => $sppList,
            'userAccounts' => $userAccounts,
        ]);
    }

    public function datatable(): JsonResponse
    {
        $siswa = Siswa::query()->with(['jurusan', 'kelas', 'spp', 'user']);

        return DataTables::of($siswa)
            ->addIndexColumn()
            ->addColumn('siswa_info', function (Siswa $row): string {
                $initial = strtoupper(mb_substr($row->nama, 0, 1));

                return '
                    <div class="table-card">
                        <div class="table-avatar">'.$initial.'</div>
                        <div class="table-card__body">
                            <div class="table-card__title">'.$row->nama.'</div>
                            <ul class="table-meta">
                                <li><span>NIS</span>'.$row->nis.'</li>
                                <li><span>NISN</span>'.$row->nisn.'</li>
                                <li><span>JK</span>'.$row->jk_lengkap.'</li>
                            </ul>
                        </div>
                    </div>';
            })
            ->addColumn('detail_info', function (Siswa $row): string {
                $tanggalLahir = $row->tanggal_lahir
                    ? Carbon::parse($row->tanggal_lahir)->translatedFormat('d F Y')
                    : '-';

                $tanggalMasuk = $row->tanggal_masuk
                    ? Carbon::parse($row->tanggal_masuk)->translatedFormat('d M Y')
                    : '-';

                $contact = $row->no_hp ?: '-';
                $sppName = $row->spp?->nama ?? 'Belum diatur';
                $sppNominal = $row->spp ? 'Rp '.number_format((float) $row->spp->nominal, 0, ',', '.') : '-';
                $userName = $row->user?->name;
                $username = $row->user?->username;
                $userInfo = $row->user
                    ? sprintf('%s (%s)', $userName, $username ?? '-')
                    : 'Belum terhubung';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>TTL</span>'.$row->tempat_lahir.', '.$tanggalLahir.'</li>
                            <li><span>Masuk</span>'.$tanggalMasuk.'</li>
                            <li><span>Kontak</span>'.$contact.'</li>
                            <li><span>SPP</span>'.$sppName.' ('.$sppNominal.')</li>
                            <li><span>Akun</span>'.$userInfo.'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('kelas_info', function (Siswa $row): string {
                $kelasName = $row->kelas?->nama_kelas ?? 'Belum diatur';
                $kelasLevel = $row->kelas?->tingkat ? 'Tingkat '.$row->kelas->tingkat : '-';
                $jurusanName = $row->jurusan?->nama_jurusan ?? 'Belum diatur';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Kelas</span>'.$kelasName.'</li>
                            <li><span>Level</span>'.$kelasLevel.'</li>
                            <li><span>Jurusan</span>'.$jurusanName.'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('status_badge', function ($row) {
                return '<div class="status-pill">'.$row->status_badge.'</div>';
            })
            ->addColumn('action', function ($row) {
                $detailUrl = route('siswa.detail', $row->id);
                $detailBtn = '<a href="'.$detailUrl.'" class="btn btn-sm btn-icon btn-info" title="Detail"><i class="bx bx-show"></i></a>';
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $detailBtn.' '.$editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['siswa_info', 'detail_info', 'kelas_info', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(StoreSiswaRequest $request): JsonResponse
    {
        $connection = (new Siswa)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $siswa = Siswa::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil ditambahkan',
                'data' => $siswa->load(['jurusan', 'kelas', 'spp', 'user']),
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

    public function show(Siswa $siswa): JsonResponse
    {
        $siswa->load(['jurusan', 'kelas', 'spp', 'user']);

        return response()->json([
            'success' => true,
            'data' => $siswa,
        ]);
    }

    public function detail(Siswa $siswa): View
    {
        $siswa->load(['jurusan', 'kelas', 'spp', 'orangtua', 'user']);

        return view('tenant.siswa.show', [
            'siswa' => $siswa,
        ]);
    }

    public function update(UpdateSiswaRequest $request, Siswa $siswa): JsonResponse
    {
        $connection = $siswa->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $siswa->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil diperbarui',
                'data' => $siswa->load(['jurusan', 'kelas', 'spp', 'user']),
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

    public function destroy(Siswa $siswa): JsonResponse
    {
        $connection = $siswa->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $siswa->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil dihapus',
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
