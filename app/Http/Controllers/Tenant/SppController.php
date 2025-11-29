<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSppRequest;
use App\Http\Requests\Tenant\UpdateSppRequest;
use App\Models\Tenant\Spp;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class SppController extends Controller
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

        return view('tenant.spp.index');
    }

    public function datatable(): JsonResponse
    {
        $spp = Spp::query();

        return DataTables::of($spp)
            ->addIndexColumn()
            ->addColumn('info_card', function (Spp $row): string {
                $initial = strtoupper(mb_substr($row->nama ?? 'S', 0, 1));
                $statusClass = $row->status === 'aktif' ? 'badge bg-label-success' : 'badge bg-label-secondary';
                $statusLabel = ucfirst($row->status);

                return '
                    <div class="table-card">
                        <div class="table-avatar avatar-indigo">'.$initial.'</div>
                        <div class="table-card__body">
                            <div class="table-card__title">'.($row->nama ?? 'SPP').'</div>
                            <span class="'.$statusClass.'">'.$statusLabel.'</span>
                        </div>
                    </div>';
            })
            ->addColumn('detail_card', function (Spp $row): string {
                $nominal = number_format((float) $row->nominal, 2, ',', '.');
                $keterangan = $row->keterangan ? e($row->keterangan) : 'Belum ada keterangan';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>Nominal</span>Rp '.$nominal.'</li>
                            <li><span>Keterangan</span>'.$keterangan.'</li>
                            <li><span>Dibuat</span>'.$row->created_at?->translatedFormat('d M Y').'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('action', function (Spp $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editSpp('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusSpp('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['info_card', 'detail_card', 'action'])
            ->make(true);
    }

    public function store(StoreSppRequest $request): JsonResponse
    {
        $connection = (new Spp)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $spp = Spp::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data SPP berhasil ditambahkan',
                'data' => $spp,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data SPP',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(Spp $spp): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $spp,
        ]);
    }

    public function update(UpdateSppRequest $request, Spp $spp): JsonResponse
    {
        $connection = $spp->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $spp->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data SPP berhasil diperbarui',
                'data' => $spp,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data SPP',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Spp $spp): JsonResponse
    {
        $connection = $spp->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $spp->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data SPP berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data SPP',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
