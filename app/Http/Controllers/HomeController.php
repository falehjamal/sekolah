<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Guru;
use App\Models\Tenant\Jurusan;
use App\Models\Tenant\Kelas;
use App\Models\Tenant\MataPelajaran;
use App\Models\Tenant\Siswa;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(protected TenantConnectionManager $tenantConnection)
    {
        $this->middleware(['auth', 'permission:dashboard.view']);
        $this->middleware(function ($request, $next) {
            if (session()->has('tenant_connection')) {
                $this->tenantConnection->connectFromSession();
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        $studentStats = [
            'total' => Siswa::count(),
            'male' => Siswa::where('jk', 'l')->count(),
            'female' => Siswa::where('jk', 'p')->count(),
        ];

        $teacherStats = [
            'total' => Guru::count(),
            'male' => Guru::where('jenis_kelamin', 'L')->count(),
            'female' => Guru::where('jenis_kelamin', 'P')->count(),
        ];

        $academicStats = [
            'classes' => Kelas::count(),
            'majors' => Jurusan::count(),
            'subjects' => MataPelajaran::count(),
        ];

        return view('home', [
            'studentStats' => $studentStats,
            'teacherStats' => $teacherStats,
            'academicStats' => $academicStats,
        ]);
    }
}
