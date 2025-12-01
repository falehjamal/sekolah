<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Validator;
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

    public function index(Request $request)
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
                $tanggal = $row->tanggal_lahir
                    ? Carbon::parse($row->tanggal_lahir)->translatedFormat('d F Y')
                    : '-';

                $contact = $row->no_hp ?: '-';
                $sppName = $row->spp?->nama ?? 'Belum diatur';
                $sppNominal = $row->spp ? 'Rp '.number_format((float) $row->spp->nominal, 2, ',', '.') : '-';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Tempat</span>'.$row->tempat_lahir.'</li>
                            <li><span>Tanggal</span>'.$tanggal.'</li>
                            <li><span>Kontak</span>'.$contact.'</li>
                            <li><span>SPP</span>'.$sppName.'</li>
                            <li><span>Nominal</span>'.$sppNominal.'</li>
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

    public function store(Request $request): JsonResponse
    {
        $siswa = new Siswa;
        $tableName = $siswa->getTable();
        $connection = $siswa->getConnectionName();

        $jurusanTable = (new Jurusan)->getTable();
        $kelasTable = (new Kelas)->getTable();
        $sppTable = (new Spp)->getTable();

        $payloadFields = [
            'user_id',
            'nis',
            'nisn',
            'nama',
            'jk',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'kelas_id',
            'jurusan_id',
            'spp_id',
            'no_hp',
            'status',
        ];

        $request->merge([
            'user_id' => $request->filled('user_id') ? $request->input('user_id') : null,
        ]);

        $validator = Validator::make($request->all(), [
            'nis' => 'required|string|max:20|unique:'.$connection.'.'.$tableName.',nis',
            'nisn' => 'required|string|max:20|unique:'.$connection.'.'.$tableName.',nisn',
            'nama' => 'required|string|max:255',
            'jk' => 'required|in:l,p',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'kelas_id' => 'required|integer|exists:'.$connection.'.'.$kelasTable.',id',
            'jurusan_id' => 'required|integer|exists:'.$connection.'.'.$jurusanTable.',id',
            'spp_id' => 'required|integer|exists:'.$connection.'.'.$sppTable.',id',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:aktif,alumni,keluar',
            'user_id' => $this->userRule(),
        ], [
            'nis.required' => 'NIS harus diisi',
            'nis.unique' => 'NIS sudah terdaftar',
            'nisn.required' => 'NISN harus diisi',
            'nisn.unique' => 'NISN sudah terdaftar',
            'nama.required' => 'Nama harus diisi',
            'jk.required' => 'Jenis kelamin harus dipilih',
            'jk.in' => 'Jenis kelamin tidak valid',
            'tempat_lahir.required' => 'Tempat lahir harus diisi',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'alamat.required' => 'Alamat harus diisi',
            'kelas_id.required' => 'Kelas harus dipilih',
            'kelas_id.exists' => 'Kelas tidak valid',
            'jurusan_id.required' => 'Jurusan harus dipilih',
            'jurusan_id.exists' => 'Jurusan tidak valid',
            'spp_id.required' => 'SPP harus dipilih',
            'spp_id.exists' => 'SPP tidak valid',
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak valid',
            'user_id.exists' => 'Akun user tidak valid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::connection($connection)->beginTransaction();

            $siswa = Siswa::create($request->only($payloadFields));

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil ditambahkan',
                'data' => $siswa,
            ]);
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $siswa = Siswa::with(['jurusan', 'kelas', 'spp', 'user'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $siswa,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan',
            ], 404);
        }
    }

    public function detail(Siswa $siswa): View
    {
        $siswa->load(['jurusan', 'kelas', 'spp', 'orangtua', 'user']);

        return view('tenant.siswa.show', [
            'siswa' => $siswa,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $connection = (new Siswa)->getConnectionName();

        try {
            $siswa = Siswa::findOrFail($id);
            $tableName = $siswa->getTable();
            $jurusanTable = (new Jurusan)->getTable();

            $kelasTable = (new Kelas)->getTable();
            $sppTable = (new Spp)->getTable();

            $payloadFields = [
                'user_id',
                'nis',
                'nisn',
                'nama',
                'jk',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'kelas_id',
                'jurusan_id',
                'spp_id',
                'no_hp',
                'status',
            ];

            $request->merge([
                'user_id' => $request->filled('user_id') ? $request->input('user_id') : null,
            ]);

            $validator = Validator::make($request->all(), [
                'nis' => 'required|string|max:20|unique:'.$connection.'.'.$tableName.',nis,'.$id,
                'nisn' => 'required|string|max:20|unique:'.$connection.'.'.$tableName.',nisn,'.$id,
                'nama' => 'required|string|max:255',
                'jk' => 'required|in:l,p',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'alamat' => 'required|string',
                'kelas_id' => 'required|integer|exists:'.$connection.'.'.$kelasTable.',id',
                'jurusan_id' => 'required|integer|exists:'.$connection.'.'.$jurusanTable.',id',
                'spp_id' => 'required|integer|exists:'.$connection.'.'.$sppTable.',id',
                'no_hp' => 'nullable|string|max:20',
                'status' => 'required|in:aktif,alumni,keluar',
                'user_id' => $this->userRule(),
            ], [
                'nis.required' => 'NIS harus diisi',
                'nis.unique' => 'NIS sudah terdaftar',
                'nisn.required' => 'NISN harus diisi',
                'nisn.unique' => 'NISN sudah terdaftar',
                'nama.required' => 'Nama harus diisi',
                'jk.required' => 'Jenis kelamin harus dipilih',
                'jk.in' => 'Jenis kelamin tidak valid',
                'tempat_lahir.required' => 'Tempat lahir harus diisi',
                'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
                'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
                'alamat.required' => 'Alamat harus diisi',
                'kelas_id.required' => 'Kelas harus dipilih',
                'kelas_id.exists' => 'Kelas tidak valid',
                'jurusan_id.required' => 'Jurusan harus dipilih',
                'jurusan_id.exists' => 'Jurusan tidak valid',
                'spp_id.required' => 'SPP harus dipilih',
                'spp_id.exists' => 'SPP tidak valid',
                'status.required' => 'Status harus dipilih',
                'status.in' => 'Status tidak valid',
                'user_id.exists' => 'Akun user tidak valid',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::connection($connection)->beginTransaction();

            $siswa->update($request->only($payloadFields));

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil diperbarui',
                'data' => $siswa,
            ]);
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $connection = (new Siswa)->getConnectionName();

        try {
            $siswa = Siswa::findOrFail($id);

            DB::connection($connection)->beginTransaction();

            $siswa->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function userRule(): string
    {
        $user = new UserAccount;
        $userTable = $user->getTable();
        $userConnection = $user->getConnectionName();
        $qualifiedUserTable = $userConnection ? $userConnection.'.'.$userTable : $userTable;

        return 'nullable|integer|exists:'.$qualifiedUserTable.',id';
    }
}
