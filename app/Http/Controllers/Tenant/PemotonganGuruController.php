<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePemotonganGuruRequest;
use App\Http\Requests\Tenant\UpdatePemotonganGuruRequest;
use App\Models\Tenant\Guru;
use App\Models\Tenant\PemotonganGuru;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class PemotonganGuruController extends Controller
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

        return view('tenant.pemotongan.index', compact('guruList'));
    }

    protected function datatable(): JsonResponse
    {
        $pemotongan = PemotonganGuru::with('guru');

        return DataTables::of($pemotongan)
            ->addIndexColumn()
            ->addColumn('guru_info', function (PemotonganGuru $row): string {
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
            ->addColumn('jenis_pemotongan_display', fn (PemotonganGuru $row): string => '<span class="fw-medium">' . e($row->jenis_pemotongan) . '</span>')
            ->addColumn('nominal_pemotongan_display', fn (PemotonganGuru $row): string => '<span class="fw-semibold">' . e($row->nominal_pemotongan_format) . '</span>')
            ->addColumn('waktu_display', fn (PemotonganGuru $row): string => $row->waktu_format)
            ->addColumn('action', function (PemotonganGuru $row): string {
                $detailBtn = '<button type="button" class="btn btn-sm btn-icon btn-info" onclick="detailData(' . $row->id . ')"><i class="bx bx-show"></i></button>';
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData(' . $row->id . ')"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData(' . $row->id . ')"><i class="bx bx-trash"></i></button>';

                return $detailBtn . ' ' . $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['guru_info', 'jenis_pemotongan_display', 'nominal_pemotongan_display', 'waktu_display', 'action'])
            ->make(true);
    }

    public function store(StorePemotonganGuruRequest $request): JsonResponse
    {
        $connection = (new PemotonganGuru())->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $pemotongan = PemotonganGuru::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pemotongan berhasil ditambahkan',
                'data' => $pemotongan,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data pemotongan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(PemotonganGuru $pemotongan): JsonResponse
    {
        $pemotongan->load('guru');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pemotongan->id,
                'guru_id' => $pemotongan->guru_id,
                'guru_nama' => $pemotongan->guru?->nama ?? '-',
                'guru_nip' => $pemotongan->guru?->nip ?? '-',
                'nama_pemotongan' => $pemotongan->nama_pemotongan,
                'nominal_pemotongan' => $pemotongan->nominal_pemotongan,
                'nominal_pemotongan_format' => $pemotongan->nominal_pemotongan_format,
                'waktu' => $pemotongan->waktu?->format('Y-m-d\\TH:i'),
                'waktu_format' => $pemotongan->waktu_format,
                'jenis_pemotongan' => $pemotongan->jenis_pemotongan,
            ],
        ]);
    }

    public function update(UpdatePemotonganGuruRequest $request, PemotonganGuru $pemotongan): JsonResponse
    {
        $connection = $pemotongan->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $pemotongan->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pemotongan berhasil diperbarui',
                'data' => $pemotongan,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data pemotongan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(PemotonganGuru $pemotongan): JsonResponse
    {
        $connection = $pemotongan->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $pemotongan->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pemotongan berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data pemotongan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
