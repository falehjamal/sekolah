<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Jurusan;
use App\Models\Tenant\Siswa;
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

        return view('tenant.siswa.index', [
            'jurusanList' => $jurusanList,
        ]);
    }

    public function datatable(): JsonResponse
    {
        $siswa = Siswa::query()->with('jurusan');

        return DataTables::of($siswa)
            ->addIndexColumn()
            ->addColumn('siswa_info', function (Siswa $row): string {
                return sprintf('%s - %s (%s)', $row->nis, $row->nama, $row->jk_lengkap);
            })
            ->addColumn('ttl_info', function (Siswa $row): string {
                $tanggal = $row->tanggal_lahir
                    ? Carbon::parse($row->tanggal_lahir)->translatedFormat('d F Y')
                    : '-';

                return sprintf('%s, %s', $row->tempat_lahir, $tanggal);
            })
            ->addColumn('kelas_info', function (Siswa $row): string {
                $jurusanName = $row->jurusan?->nama_jurusan ?? 'Belum diatur';

                return sprintf('Kelas %s - %s', $row->kelas_id, $jurusanName);
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('action', function ($row) {
                $detailUrl = route('siswa.detail', $row->id);
                $detailBtn = '<a href="'.$detailUrl.'" class="btn btn-sm btn-icon btn-info" title="Detail"><i class="bx bx-show"></i></a>';
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $detailBtn.' '.$editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $siswa = new Siswa;
        $tableName = $siswa->getTable();
        $connection = $siswa->getConnectionName();

        $jurusanTable = (new Jurusan)->getTable();

        $validator = Validator::make($request->all(), [
            'nis' => 'required|string|max:20|unique:'.$connection.'.'.$tableName.',nis',
            'nisn' => 'required|string|max:20|unique:'.$connection.'.'.$tableName.',nisn',
            'nama' => 'required|string|max:255',
            'jk' => 'required|in:l,p',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'kelas_id' => 'required|integer',
            'jurusan_id' => 'required|integer|exists:'.$connection.'.'.$jurusanTable.',id',
            'orangtua_id' => 'required|integer',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:aktif,alumni,keluar',
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
            'jurusan_id.required' => 'Jurusan harus dipilih',
            'jurusan_id.exists' => 'Jurusan tidak valid',
            'orangtua_id.required' => 'Orang tua harus dipilih',
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak valid',
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

            $siswa = Siswa::create($request->all());

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
            $siswa = Siswa::with('jurusan')->findOrFail($id);

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
        $siswa->load('jurusan');

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

            $validator = Validator::make($request->all(), [
                'nis' => 'required|string|max:20|unique:'.$connection.'.'.$tableName.',nis,'.$id,
                'nisn' => 'required|string|max:20|unique:'.$connection.'.'.$tableName.',nisn,'.$id,
                'nama' => 'required|string|max:255',
                'jk' => 'required|in:l,p',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'alamat' => 'required|string',
                'kelas_id' => 'required|integer',
                'jurusan_id' => 'required|integer|exists:'.$connection.'.'.$jurusanTable.',id',
                'orangtua_id' => 'required|integer',
                'no_hp' => 'nullable|string|max:20',
                'status' => 'required|in:aktif,alumni,keluar',
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
                'jurusan_id.required' => 'Jurusan harus dipilih',
                'jurusan_id.exists' => 'Jurusan tidak valid',
                'orangtua_id.required' => 'Orang tua harus dipilih',
                'status.required' => 'Status harus dipilih',
                'status.in' => 'Status tidak valid',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::connection($connection)->beginTransaction();

            $siswa->update($request->all());

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
}
