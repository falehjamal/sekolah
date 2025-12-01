<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreOrangtuaRequest;
use App\Http\Requests\Tenant\UpdateOrangtuaRequest;
use App\Models\Tenant\Orangtua;
use App\Models\Tenant\Siswa;
use App\Models\Tenant\UserAccount;
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

        $userList = UserAccount::query()
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('tenant.orangtua.index', [
            'siswaList' => $siswaList,
            'userList' => $userList,
        ]);
    }

    public function datatable(): JsonResponse
    {
        $orangtua = Orangtua::query()->with(['siswa', 'user']);

        return DataTables::of($orangtua)
            ->addIndexColumn()
            ->addColumn('orangtua_card', function (Orangtua $row): string {
                $initial = strtoupper(mb_substr($row->nama, 0, 1));
                $hubungan = match ($row->hubungan) {
                    'ayah' => 'Ayah',
                    'ibu' => 'Ibu',
                    'wali' => 'Wali',
                    default => ucfirst($row->hubungan),
                };

                return '
                    <div class="table-card">
                        <div class="table-avatar avatar-orange">'.$initial.'</div>
                        <div class="table-card__body">
                            <div class="table-card__title">'.$row->nama.'</div>
                            <ul class="table-meta">
                                <li><span>Hubungan</span>'.$hubungan.'</li>
                                <li><span>Dibuat</span>'.$row->created_at?->translatedFormat('d M Y').'</li>
                            </ul>
                        </div>
                    </div>';
            })
            ->addColumn('siswa_card', function (Orangtua $row): string {
                if (! $row->siswa) {
                    return '<div class="table-stack"><p class="mb-0 text-muted">Belum terhubung ke siswa.</p></div>';
                }

                return '
                    <div class="table-stack">
                        <p class="mb-1 text-muted small">Informasi Siswa</p>
                        <ul class="table-meta">
                            <li><span>Nama</span>'.$row->siswa->nama.'</li>
                            <li><span>NIS</span>'.$row->siswa->nis.'</li>
                            <li><span>NISN</span>'.$row->siswa->nisn.'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('contact_card', function (Orangtua $row): string {
                $noHp = $row->no_hp ?: '-';
                $pekerjaan = $row->pekerjaan ?: '-';
                $alamat = $row->alamat ? e($row->alamat) : '-';
                $userName = $row->user?->name;
                $username = $row->user?->username;
                $userInfo = $row->user
                    ? sprintf('%s (%s)', e($userName), e($username ?? '-'))
                    : '-';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>No HP</span>'.$noHp.'</li>
                            <li><span>Pekerjaan</span>'.$pekerjaan.'</li>
                            <li><span>Alamat</span>'.$alamat.'</li>
                            <li><span>Akun</span>'.$userInfo.'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('action', function (Orangtua $row): string {
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editOrangtua('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusOrangtua('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['orangtua_card', 'siswa_card', 'contact_card', 'action'])
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
        $orangtua->load(['siswa', 'user']);

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
