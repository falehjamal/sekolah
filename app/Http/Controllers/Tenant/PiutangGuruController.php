<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePiutangGuruRequest;
use App\Http\Requests\Tenant\UpdatePiutangGuruRequest;
use App\Models\Tenant\Guru;
use App\Models\Tenant\PiutangGuru;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class PiutangGuruController extends Controller
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

        return view('tenant.piutang.index', compact('guruList'));
    }

    protected function datatable(): JsonResponse
    {
        $piutang = PiutangGuru::with('guru');

        return DataTables::of($piutang)
            ->addIndexColumn()
            ->addColumn('guru_info', function (PiutangGuru $row): string {
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
            ->addColumn('nominal_hutang_display', fn (PiutangGuru $row): string => '<span class="fw-semibold">' . e($row->nominal_hutang_format) . '</span>')
            ->addColumn('waktu_hutang_display', fn (PiutangGuru $row): string => $row->waktu_hutang_format)
            ->addColumn('input_pemotongan_badge', fn (PiutangGuru $row): string => $row->input_ke_pemotongan_badge)
            ->addColumn('action', function (PiutangGuru $row): string {
                $detailBtn = '<button type="button" class="btn btn-sm btn-icon btn-info" onclick="detailData(' . $row->id . ')"><i class="bx bx-show"></i></button>';
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData(' . $row->id . ')"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData(' . $row->id . ')"><i class="bx bx-trash"></i></button>';

                return $detailBtn . ' ' . $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['guru_info', 'nominal_hutang_display', 'waktu_hutang_display', 'input_pemotongan_badge', 'action'])
            ->make(true);
    }

    public function store(StorePiutangGuruRequest $request): JsonResponse
    {
        $connection = (new PiutangGuru())->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $piutang = PiutangGuru::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data piutang berhasil ditambahkan',
                'data' => $piutang,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data piutang',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(PiutangGuru $piutang): JsonResponse
    {
        $piutang->load('guru');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $piutang->id,
                'guru_id' => $piutang->guru_id,
                'guru_nama' => $piutang->guru?->nama ?? '-',
                'guru_nip' => $piutang->guru?->nip ?? '-',
                'keterangan_hutang' => $piutang->keterangan_hutang,
                'nominal_hutang' => $piutang->nominal_hutang,
                'nominal_hutang_format' => $piutang->nominal_hutang_format,
                'waktu_hutang' => $piutang->waktu_hutang?->format('Y-m-d\\TH:i'),
                'waktu_hutang_format' => $piutang->waktu_hutang_format,
                'input_ke_pemotongan' => $piutang->input_ke_pemotongan,
                'input_ke_pemotongan_badge' => $piutang->input_ke_pemotongan_badge,
                'waktu_pemotongan' => $piutang->waktu_pemotongan?->format('Y-m-d\\TH:i'),
                'waktu_pemotongan_format' => $piutang->waktu_pemotongan_format,
            ],
        ]);
    }

    public function update(UpdatePiutangGuruRequest $request, PiutangGuru $piutang): JsonResponse
    {
        $connection = $piutang->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $piutang->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data piutang berhasil diperbarui',
                'data' => $piutang,
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data piutang',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(PiutangGuru $piutang): JsonResponse
    {
        $connection = $piutang->getConnectionName();
        DB::connection($connection)->beginTransaction();

        try {
            $piutang->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data piutang berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data piutang',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
