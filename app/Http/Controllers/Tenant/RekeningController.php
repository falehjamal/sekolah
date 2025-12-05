<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreRekeningRequest;
use App\Http\Requests\Tenant\UpdateRekeningRequest;
use App\Models\Tenant\Rekening;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class RekeningController extends Controller
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

        return view('tenant.rekening.index');
    }

    public function datatable(): JsonResponse
    {
        $rekening = Rekening::query()->orderBy('bank');

        return DataTables::of($rekening)
            ->addIndexColumn()
            ->addColumn('bank_info', function (Rekening $row): string {
                $initial = strtoupper(mb_substr($row->bank, 0, 2));

                return '<div class="table-card"><div class="table-avatar">' . $initial . '</div><div class="table-card__body"><div class="table-card__title">' . $row->bank . '</div><ul class="table-meta"><li><span>No. Rek</span>' . $row->no_rekening . '</li></ul></div></div>';
            })
            ->addColumn('nama_rekening_display', function (Rekening $row): string {
                return '<span class="fw-medium">' . $row->nama_rekening . '</span>';
            })
            ->addColumn('no_rekening_display', function (Rekening $row): string {
                return '<code class="text-primary">' . $row->no_rekening . '</code>';
            })
            ->addColumn('action', function (Rekening $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData(' . $row->id . ')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData(' . $row->id . ')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['bank_info', 'nama_rekening_display', 'no_rekening_display', 'action'])
            ->make(true);
    }

    public function store(StoreRekeningRequest $request): JsonResponse
    {
        $connection = (new Rekening)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $rekening = Rekening::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data rekening berhasil ditambahkan',
                'data' => $rekening,
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

    public function show(Rekening $rekening): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $rekening,
        ]);
    }

    public function update(UpdateRekeningRequest $request, Rekening $rekening): JsonResponse
    {
        $connection = $rekening->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $rekening->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data rekening berhasil diperbarui',
                'data' => $rekening,
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

    public function destroy(Rekening $rekening): JsonResponse
    {
        $connection = $rekening->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $rekening->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data rekening berhasil dihapus',
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
