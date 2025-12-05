<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreGajiGuruRequest;
use App\Http\Requests\Tenant\UpdateGajiGuruRequest;
use App\Models\Tenant\GajiGuru;
use App\Models\Tenant\Guru;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class GajiGuruController extends Controller
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

        return view('tenant.gaji-guru.index', compact('guruList'));
    }

    public function datatable(): JsonResponse
    {
        $gajiGuru = GajiGuru::with('guru');

        return DataTables::of($gajiGuru)
            ->addIndexColumn()
            ->addColumn('guru_info', function (GajiGuru $row): string {
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
            ->addColumn('ttl', function (GajiGuru $row): string {
                $tempat = $row->tempat_lahir ?: '-';
                $tanggal = $row->tanggal_lahir ? $row->tanggal_lahir->format('d/m/Y') : '-';

                if ($tempat === '-' && $tanggal === '-') {
                    return '<span class="text-muted">-</span>';
                }

                return e($tempat) . ', ' . $tanggal;
            })
            ->addColumn('tanggal_bergabung_display', function (GajiGuru $row): string {
                return $row->tanggal_bergabung ? $row->tanggal_bergabung->format('d/m/Y') : '<span class="text-muted">-</span>';
            })
            ->addColumn('jenis_gaji_badge', function (GajiGuru $row): string {
                return $row->jenis_gaji_badge;
            })
            ->addColumn('gaji_display', function (GajiGuru $row): string {
                return '<div class="text-end">
                    <div class="fw-semibold text-primary">' . $row->total_gaji_format . '</div>
                    <small class="text-muted">Pokok: ' . $row->gaji_pokok_format . '</small>
                </div>';
            })
            ->addColumn('status_badge', function (GajiGuru $row): string {
                return $row->status_badge;
            })
            ->addColumn('action', function (GajiGuru $row): string {
                $detailBtn = '<button type="button" class="btn btn-sm btn-icon btn-info" onclick="detailData(' . $row->id . ')" title="Detail"><i class="bx bx-show"></i></button>';
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editData(' . $row->id . ')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="deleteData(' . $row->id . ')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $detailBtn . ' ' . $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['guru_info', 'ttl', 'tanggal_bergabung_display', 'jenis_gaji_badge', 'gaji_display', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(StoreGajiGuruRequest $request): JsonResponse
    {
        $connection = (new GajiGuru)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            // Check if guru already has gaji record
            $existingGaji = GajiGuru::where('guru_id', $request->guru_id)->first();
            if ($existingGaji) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru ini sudah memiliki data gaji. Silakan edit data yang sudah ada.',
                ], 422);
            }

            $gajiGuru = GajiGuru::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data gaji guru berhasil ditambahkan',
                'data' => $gajiGuru,
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

    public function show(GajiGuru $gajiGuru): JsonResponse
    {
        $gajiGuru->load('guru');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $gajiGuru->id,
                'guru_id' => $gajiGuru->guru_id,
                'guru_nama' => $gajiGuru->guru?->nama ?? '-',
                'guru_nip' => $gajiGuru->guru?->nip ?? '-',
                'tempat_lahir' => $gajiGuru->tempat_lahir,
                'tanggal_lahir' => $gajiGuru->tanggal_lahir?->format('Y-m-d'),
                'tanggal_lahir_format' => $gajiGuru->tanggal_lahir_format,
                'tanggal_bergabung' => $gajiGuru->tanggal_bergabung?->format('Y-m-d'),
                'tanggal_bergabung_format' => $gajiGuru->tanggal_bergabung_format,
                'jenis_gaji' => $gajiGuru->jenis_gaji,
                'jenis_gaji_label' => $gajiGuru->jenis_gaji_label,
                'jenis_gaji_badge' => $gajiGuru->jenis_gaji_badge,
                'gaji_pokok' => $gajiGuru->gaji_pokok,
                'gaji_pokok_format' => $gajiGuru->gaji_pokok_format,
                'uang_makan' => $gajiGuru->uang_makan,
                'uang_makan_format' => $gajiGuru->uang_makan_format,
                'uang_transport' => $gajiGuru->uang_transport,
                'uang_transport_format' => $gajiGuru->uang_transport_format,
                'tunjangan_jabatan' => $gajiGuru->tunjangan_jabatan,
                'tunjangan_jabatan_format' => $gajiGuru->tunjangan_jabatan_format,
                'tunjangan_lain' => $gajiGuru->tunjangan_lain,
                'tunjangan_lain_format' => $gajiGuru->tunjangan_lain_format,
                'total_gaji' => $gajiGuru->total_gaji,
                'total_gaji_format' => $gajiGuru->total_gaji_format,
                'status' => $gajiGuru->status,
                'status_badge' => $gajiGuru->status_badge,
            ],
        ]);
    }

    public function update(UpdateGajiGuruRequest $request, GajiGuru $gajiGuru): JsonResponse
    {
        $connection = $gajiGuru->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            // Check if guru already has gaji record (excluding current)
            if ($request->guru_id != $gajiGuru->guru_id) {
                $existingGaji = GajiGuru::where('guru_id', $request->guru_id)
                    ->where('id', '!=', $gajiGuru->id)
                    ->first();
                if ($existingGaji) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Guru ini sudah memiliki data gaji.',
                    ], 422);
                }
            }

            $gajiGuru->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data gaji guru berhasil diperbarui',
                'data' => $gajiGuru,
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

    public function destroy(GajiGuru $gajiGuru): JsonResponse
    {
        $connection = $gajiGuru->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $gajiGuru->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data gaji guru berhasil dihapus',
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
