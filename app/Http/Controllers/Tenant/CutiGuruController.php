<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCutiGuruRequest;
use App\Http\Requests\Tenant\UpdateCutiGuruRequest;
use App\Models\Tenant\CutiGuru;
use App\Models\Tenant\Guru;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class CutiGuruController extends Controller
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

        return view('tenant.cuti.index', compact('guruList'));
    }

    public function datatable(): JsonResponse
    {
        $cuti = CutiGuru::with('guru');

        return DataTables::of($cuti)
            ->addIndexColumn()
            ->addColumn('guru_info', function (CutiGuru $row): string {
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
            ->addColumn('jenis_cuti_display', function (CutiGuru $row): string {
                return '<span class="fw-medium">' . e($row->jenis_cuti) . '</span>';
            })
            ->addColumn('periode_display', function (CutiGuru $row): string {
                return '<div>' . $row->tanggal_awal_format . ' s/d ' . $row->tanggal_akhir_format . '</div>
                        <small class="text-muted">' . $row->durasi_hari . ' hari</small>';
            })
            ->addColumn('waktu_entry_display', function (CutiGuru $row): string {
                return $row->waktu_entry_format;
            })
            ->addColumn('status_badge', function (CutiGuru $row): string {
                return $row->status_approval_badge;
            })
            ->addColumn('action', function (CutiGuru $row): string {
                $detailBtn = '<button type="button" class="btn btn-sm btn-icon btn-info" onclick="detailData(' . $row->id . ')" title="Detail"><i class="bx bx-show"></i></button>';
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData(' . $row->id . ')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData(' . $row->id . ')" title="Hapus"><i class="bx bx-trash"></i></button>';

                // Approval buttons only for pending status
                $approvalBtns = '';
                if ($row->status_approval === 'pending') {
                    $approvalBtns = '<button type="button" class="btn btn-sm btn-icon btn-success" onclick="approveData(' . $row->id . ')" title="Approve"><i class="bx bx-check"></i></button>';
                    $approvalBtns .= ' <button type="button" class="btn btn-sm btn-icon btn-danger" onclick="rejectData(' . $row->id . ')" title="Reject"><i class="bx bx-x"></i></button>';
                }

                return $detailBtn . ' ' . $editBtn . ' ' . $deleteBtn . ' ' . $approvalBtns;
            })
            ->rawColumns(['guru_info', 'jenis_cuti_display', 'periode_display', 'waktu_entry_display', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(StoreCutiGuruRequest $request): JsonResponse
    {
        $connection = (new CutiGuru)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $data = $request->validated();
            $data['waktu_entry'] = now();

            $cuti = CutiGuru::create($data);

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data cuti guru berhasil ditambahkan',
                'data' => $cuti,
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

    public function show(CutiGuru $cuti): JsonResponse
    {
        $cuti->load(['guru', 'petugas']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cuti->id,
                'guru_id' => $cuti->guru_id,
                'guru_nama' => $cuti->guru?->nama ?? '-',
                'guru_nip' => $cuti->guru?->nip ?? '-',
                'jenis_cuti' => $cuti->jenis_cuti,
                'tanggal_awal' => $cuti->tanggal_awal?->format('Y-m-d'),
                'tanggal_awal_format' => $cuti->tanggal_awal_format,
                'tanggal_akhir' => $cuti->tanggal_akhir?->format('Y-m-d'),
                'tanggal_akhir_format' => $cuti->tanggal_akhir_format,
                'durasi_hari' => $cuti->durasi_hari,
                'periode_cuti' => $cuti->periode_cuti,
                'waktu_entry' => $cuti->waktu_entry_format,
                'status_approval' => $cuti->status_approval,
                'status_approval_label' => $cuti->status_approval_label,
                'status_approval_badge' => $cuti->status_approval_badge,
                'petugas_nama' => $cuti->petugas?->name ?? '-',
                'waktu_approval' => $cuti->waktu_approval_format,
            ],
        ]);
    }

    public function update(UpdateCutiGuruRequest $request, CutiGuru $cuti): JsonResponse
    {
        $connection = $cuti->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $cuti->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data cuti guru berhasil diperbarui',
                'data' => $cuti,
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

    public function destroy(CutiGuru $cuti): JsonResponse
    {
        $connection = $cuti->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $cuti->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data cuti guru berhasil dihapus',
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

    public function approve(Request $request, CutiGuru $cuti): JsonResponse
    {
        $connection = $cuti->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $cuti->update([
                'status_approval' => 'approved',
                'petugas_id' => Auth::id(),
                'waktu_approval' => now(),
            ]);

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Cuti guru berhasil di-approve',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat approve cuti',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, CutiGuru $cuti): JsonResponse
    {
        $connection = $cuti->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $cuti->update([
                'status_approval' => 'rejected',
                'petugas_id' => Auth::id(),
                'waktu_approval' => now(),
            ]);

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Cuti guru berhasil di-reject',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat reject cuti',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
