<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreJurusanRequest;
use App\Http\Requests\Tenant\UpdateJurusanRequest;
use App\Models\Tenant\Jurusan;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class JurusanController extends Controller
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

        return view('tenant.jurusan.index');
    }

    public function datatable(): JsonResponse
    {
        $jurusan = Jurusan::query();

        return DataTables::of($jurusan)
            ->addIndexColumn()
            ->addColumn('deskripsi_ringkas', function (Jurusan $row): string {
                return Str::limit($row->deskripsi ?? '-', 60);
            })
            ->addColumn('action', function (Jurusan $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editJurusan('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusJurusan('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(StoreJurusanRequest $request): JsonResponse
    {
        $connection = (new Jurusan)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $jurusan = Jurusan::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data jurusan berhasil ditambahkan',
                'data' => $jurusan,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data jurusan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(Jurusan $jurusan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $jurusan,
        ]);
    }

    public function update(UpdateJurusanRequest $request, Jurusan $jurusan): JsonResponse
    {
        $connection = $jurusan->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $jurusan->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data jurusan berhasil diperbarui',
                'data' => $jurusan,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data jurusan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Jurusan $jurusan): JsonResponse
    {
        $connection = $jurusan->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $jurusan->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data jurusan berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data jurusan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
