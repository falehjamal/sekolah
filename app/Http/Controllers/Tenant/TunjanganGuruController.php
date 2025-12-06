<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTunjanganGuruRequest;
use App\Http\Requests\Tenant\UpdateTunjanganGuruRequest;
use App\Models\Tenant\Guru;
use App\Models\Tenant\TunjanganGuru;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class TunjanganGuruController extends Controller
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

        $guruList = Guru::where('status', 'aktif')->orderBy('nama')->get();

        return view('tenant.tunjangan.index', compact('guruList'));
    }

    protected function datatable(): JsonResponse
    {
        $tunjangan = TunjanganGuru::with('guru');

        return DataTables::of($tunjangan)
            ->addIndexColumn()
            ->addColumn('guru_info', function (TunjanganGuru $row): string {
                $guru = $row->guru;
                if (!$guru) {
                    return '<span class="text-muted">-</span>';
                }

                $initial = strtoupper(mb_substr($guru->nama, 0, 2));
                $nip = $guru->nip ?: '-';

                return '<div class="table-card">
                    <div class="table-avatar">' . e($initial) . '</div>
                    <div class="table-card__body">
                        <div class="table-card__title">' . e($guru->nama) . '</div>
                        <ul class="table-meta">
                            <li><span>NIP</span>' . e($nip) . '</li>
                        </ul>
                    </div>
                </div>';
            })
            ->addColumn('nominal_tunjangan_display', fn (TunjanganGuru $row): string => '<span class="fw-semibold">' . e($row->nominal_tunjangan_format) . '</span>')
            ->addColumn('waktu_display', fn (TunjanganGuru $row): string => $row->waktu_format)
            ->addColumn('action', function (TunjanganGuru $row): string {
                $detailBtn = '<button type="button" class="btn btn-sm btn-icon btn-info" onclick="detailData(' . $row->id . ')"><i class="bx bx-show"></i></button>';
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData(' . $row->id . ')"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData(' . $row->id . ')"><i class="bx bx-trash"></i></button>';

                return $detailBtn . ' ' . $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['guru_info', 'nominal_tunjangan_display', 'waktu_display', 'action'])
            ->make(true);
    }

    public function store(StoreTunjanganGuruRequest $request): JsonResponse
    {
        $connection = (new TunjanganGuru())->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $tunjangan = TunjanganGuru::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data tunjangan berhasil ditambahkan',
                'data' => $tunjangan,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data tunjangan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(TunjanganGuru $tunjangan): JsonResponse
    {
        $tunjangan->load('guru');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tunjangan->id,
                'guru_id' => $tunjangan->guru_id,
                'guru_nama' => $tunjangan->guru?->nama ?? '-',
                'guru_nip' => $tunjangan->guru?->nip ?? '-',
                'nama_tunjangan' => $tunjangan->nama_tunjangan,
                'nominal_tunjangan' => $tunjangan->nominal_tunjangan,
                'nominal_tunjangan_format' => $tunjangan->nominal_tunjangan_format,
                'waktu' => $tunjangan->waktu?->format('Y-m-d\\TH:i'),
                'waktu_format' => $tunjangan->waktu_format,
            ],
        ]);
    }

    public function update(UpdateTunjanganGuruRequest $request, TunjanganGuru $tunjangan): JsonResponse
    {
        $connection = $tunjangan->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $tunjangan->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data tunjangan berhasil diperbarui',
                'data' => $tunjangan,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data tunjangan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(TunjanganGuru $tunjangan): JsonResponse
    {
        $connection = $tunjangan->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $tunjangan->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data tunjangan berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data tunjangan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
