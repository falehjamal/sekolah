<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreMataPelajaranRequest;
use App\Http\Requests\Tenant\UpdateMataPelajaranRequest;
use App\Models\Tenant\MataPelajaran;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class MataPelajaranController extends Controller
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

        return view('tenant.mata-pelajaran.index');
    }

    public function datatable(): JsonResponse
    {
        $mataPelajaran = MataPelajaran::query();

        return DataTables::of($mataPelajaran)
            ->addIndexColumn()
            ->addColumn('info_card', function (MataPelajaran $row): string {
                $initial = strtoupper(Str::substr($row->nama_mapel, 0, 1));

                return '
                    <div class="table-card">
                        <div class="table-avatar avatar-cyan">'.$initial.'</div>
                        <div class="table-card__body">
                            <div class="table-card__title">'.$row->nama_mapel.'</div>
                            <ul class="table-meta">
                                <li><span>Kode</span>'.$row->kode.'</li>
                                <li><span>Dibuat</span>'.$row->created_at?->translatedFormat('d M Y').'</li>
                            </ul>
                        </div>
                    </div>';
            })
            ->addColumn('detail_card', function (MataPelajaran $row): string {
                $kurikulum = $row->kurikulum ?: 'Belum ditentukan';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Kurikulum</span>'.$kurikulum.'</li>
                            <li><span>Diperbarui</span>'.$row->updated_at?->translatedFormat('d M Y').'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('status_badge', function (MataPelajaran $row): string {
                $class = $row->status === 'aktif' ? 'bg-label-success' : 'bg-label-secondary';
                $label = ucfirst($row->status);

                return '<span class="badge '.$class.'">'.$label.'</span>';
            })
            ->addColumn('action', function (MataPelajaran $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editMapel('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusMapel('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['info_card', 'detail_card', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(StoreMataPelajaranRequest $request): JsonResponse
    {
        $connection = (new MataPelajaran)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $mataPelajaran = MataPelajaran::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil ditambahkan',
                'data' => $mataPelajaran,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data mata pelajaran',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(MataPelajaran $mataPelajaran): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $mataPelajaran,
        ]);
    }

    public function update(UpdateMataPelajaranRequest $request, MataPelajaran $mataPelajaran): JsonResponse
    {
        $connection = $mataPelajaran->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $mataPelajaran->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil diperbarui',
                'data' => $mataPelajaran,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data mata pelajaran',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(MataPelajaran $mataPelajaran): JsonResponse
    {
        $connection = $mataPelajaran->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $mataPelajaran->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data mata pelajaran',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
