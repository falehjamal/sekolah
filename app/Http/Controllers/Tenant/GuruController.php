<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreGuruRequest;
use App\Http\Requests\Tenant\UpdateGuruRequest;
use App\Models\Tenant\Guru;
use App\Models\Tenant\UserAccount;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class GuruController extends Controller
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

        $userAccounts = UserAccount::query()
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('tenant.guru.index', [
            'userAccounts' => $userAccounts,
        ]);
    }

    public function datatable(): JsonResponse
    {
        $guru = Guru::query()->with('user');

        return DataTables::of($guru)
            ->addIndexColumn()
            ->addColumn('guru_info', function (Guru $row): string {
                $initial = strtoupper(mb_substr($row->nama, 0, 1));
                $nip = $row->nip ?: '-';

                return '
                    <div class="table-card">
                        <div class="table-avatar">'.$initial.'</div>
                        <div class="table-card__body">
                            <div class="table-card__title">'.$row->nama.'</div>
                            <ul class="table-meta">
                                <li><span>NIP</span>'.$nip.'</li>
                                <li><span>JK</span>'.$row->jenis_kelamin_label.'</li>
                            </ul>
                        </div>
                    </div>';
            })
            ->addColumn('contact_info', function (Guru $row): string {
                $noHp = $row->no_hp ?: '-';
                $alamat = $row->alamat ? e($row->alamat) : '-';
                $userInfo = $row->user
                    ? sprintf('%s (%s)', e($row->user->name), e($row->user->username ?? '-'))
                    : 'Belum terhubung';

                return '
                    <div class="table-stack">
                        <ul class="table-meta">
                            <li><span>No HP</span>'.$noHp.'</li>
                            <li><span>Alamat</span>'.$alamat.'</li>
                            <li><span>Akun</span>'.$userInfo.'</li>
                        </ul>
                    </div>';
            })
            ->addColumn('status_badge', fn (Guru $row) => '<div class="status-pill">'.$row->status_badge.'</div>')
            ->addColumn('action', function (Guru $row) {
                $detailUrl = route('guru.detail', $row->id);
                $detailBtn = '<a href="'.$detailUrl.'" class="btn btn-sm btn-icon btn-info" title="Detail"><i class="bx bx-show"></i></a>';
                $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-warning" onclick="editGuru('.$row->id.')" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger" onclick="hapusGuru('.$row->id.')" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $detailBtn.' '.$editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['guru_info', 'contact_info', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(StoreGuruRequest $request): JsonResponse
    {
        $connection = (new Guru)->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $guru = Guru::create($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data guru berhasil ditambahkan',
                'data' => $guru->load('user'),
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data guru',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(Guru $guru): JsonResponse
    {
        $guru->load('user');

        return response()->json([
            'success' => true,
            'data' => $guru,
        ]);
    }

    public function detail(Guru $guru): View
    {
        $guru->load('user');

        return view('tenant.guru.show', [
            'guru' => $guru,
        ]);
    }

    public function update(UpdateGuruRequest $request, Guru $guru): JsonResponse
    {
        $connection = $guru->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $guru->update($request->validated());

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data guru berhasil diperbarui',
                'data' => $guru->load('user'),
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data guru',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Guru $guru): JsonResponse
    {
        $connection = $guru->getConnectionName();

        DB::connection($connection)->beginTransaction();

        try {
            $guru->delete();

            DB::connection($connection)->commit();

            return response()->json([
                'success' => true,
                'message' => 'Data guru berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::connection($connection)->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data guru',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
