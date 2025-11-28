<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreOrangtuaRequest;
use App\Http\Requests\Tenant\UpdateOrangtuaRequest;
use App\Models\Tenant\Orangtua;
use App\Models\Tenant\Siswa;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class OrangtuaController extends Controller
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

        $siswaList = Siswa::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis']);

        return view('tenant.orangtua.index', [
            'siswaList' => $siswaList,
        ]);
    }

    public function datatable(): JsonResponse
    {
        $orangtua = Orangtua::query()->with('siswa');

        return DataTables::of($orangtua)
            ->addIndexColumn()
            ->addColumn('siswa_nama', function (Orangtua $row): string {
                if (! $row->siswa) {
                    return '-';
                }

                return $row->siswa->nama.' ('.$row->siswa->nis.')';
            })
            ->addColumn('hubungan_label', function (Orangtua $row): string {
                return match ($row->hubungan) {
                    'ayah' => 'Ayah',
                    'ibu' => 'Ibu',
                    'wali' => 'Wali',
                    default => ucfirst($row->hubungan),
                };
            })
            ->addColumn('action', function (Orangtua $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editOrangtua('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusOrangtua('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(StoreOrangtuaRequest $request): JsonResponse
    {
        $connection = (new Orangtua)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $orangtua = Orangtua::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data orang tua berhasil ditambahkan',
                'data' => $orangtua,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data orang tua',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(Orangtua $orangtua): JsonResponse
    {
        $orangtua->load('siswa');

        return response()->json([
            'success' => true,
            'data' => $orangtua,
        ]);
    }

    public function update(UpdateOrangtuaRequest $request, Orangtua $orangtua): JsonResponse
    {
        $connection = $orangtua->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $orangtua->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data orang tua berhasil diperbarui',
                'data' => $orangtua,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data orang tua',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Orangtua $orangtua): JsonResponse
    {
        $connection = $orangtua->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $orangtua->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data orang tua berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data orang tua',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
