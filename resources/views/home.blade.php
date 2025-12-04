@extends('layouts.app')

@section('content')
    <div class="row gy-4">
        <!-- Statistik Siswa -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Total Siswa</p>
                            <a href="{{ route('siswa.index') }}" class="text-decoration-none">
                                <h2 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($studentStats['total'] ?? 0, 0, ',', '.') }}</h2>
                            </a>
                        </div>
                        <div class="rounded d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff;">
                            <i class="bx bx-group fs-4" style="color: #696cff;"></i>
                        </div>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="rounded p-3" style="background-color: #f5f5f9;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bx bx-male-sign" style="color: #696cff;"></i>
                                    <small class="text-muted">Laki-laki</small>
                                </div>
                                <a href="{{ route('siswa.index') }}" class="text-decoration-none">
                                    <h5 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($studentStats['male'] ?? 0, 0, ',', '.') }}</h5>
                                </a>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded p-3" style="background-color: #f5f5f9;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bx bx-female-sign" style="color: #ff6b6b;"></i>
                                    <small class="text-muted">Perempuan</small>
                                </div>
                                <a href="{{ route('siswa.index') }}" class="text-decoration-none">
                                    <h5 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($studentStats['female'] ?? 0, 0, ',', '.') }}</h5>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Guru -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Total Guru</p>
                            <a href="{{ route('guru.index') }}" class="text-decoration-none">
                                <h2 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($teacherStats['total'] ?? 0, 0, ',', '.') }}</h2>
                            </a>
                        </div>
                        <div class="rounded d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #ffe0db;">
                            <i class="bx bx-user fs-4" style="color: #ff3e1d;"></i>
                        </div>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="rounded p-3" style="background-color: #f5f5f9;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bx bx-male-sign" style="color: #696cff;"></i>
                                    <small class="text-muted">Laki-laki</small>
                                </div>
                                <a href="{{ route('guru.index') }}" class="text-decoration-none">
                                    <h5 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($teacherStats['male'] ?? 0, 0, ',', '.') }}</h5>
                                </a>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded p-3" style="background-color: #f5f5f9;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bx bx-female-sign" style="color: #ff6b6b;"></i>
                                    <small class="text-muted">Perempuan</small>
                                </div>
                                <a href="{{ route('guru.index') }}" class="text-decoration-none">
                                    <h5 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($teacherStats['female'] ?? 0, 0, ',', '.') }}</h5>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Kelas, Kejuruan, Mata Pelajaran -->
        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e7e7ff;">
                            <i class="bx bx-buildings fs-3" style="color: #696cff;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Jumlah Kelas</p>
                            <a href="{{ route('kelas.index') }}" class="text-decoration-none">
                                <h3 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($academicStats['classes'] ?? 0, 0, ',', '.') }}</h3>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #d7f5fc;">
                            <i class="bx bx-certification fs-3" style="color: #03c3ec;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Jumlah Kejuruan</p>
                            <a href="{{ route('jurusan.index') }}" class="text-decoration-none">
                                <h3 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($academicStats['majors'] ?? 0, 0, ',', '.') }}</h3>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #fff2d6;">
                            <i class="bx bx-book-open fs-3" style="color: #ffab00;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Mata Pelajaran</p>
                            <a href="{{ route('mata-pelajaran.index') }}" class="text-decoration-none">
                                <h3 class="fw-bold mb-0" style="color: #566a7f;">{{ number_format($academicStats['subjects'] ?? 0, 0, ',', '.') }}</h3>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Presensi Siswa -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e8fadf;">
                            <i class="bx bx-user-check fs-3" style="color: #71dd37;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold" style="color: #566a7f;">Presensi Siswa</h5>
                            <small class="text-muted">Minggu Ini</small>
                        </div>
                        <div class="text-end">
                            <h3 class="fw-bold mb-0" style="color: #71dd37;">1.152</h3>
                            <small style="color: #71dd37;">
                                <i class="bx bx-up-arrow-alt"></i> 95%
                            </small>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 rounded" style="background-color: #e8fadf;">
                                <p class="text-muted small mb-2">Rata-rata Harian</p>
                                <h5 class="fw-bold mb-1" style="color: #71dd37;">192</h5>
                                <small style="color: #71dd37;">
                                    <i class="bx bx-up-arrow-alt"></i> +5%
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background-color: #fff2d6;">
                                <p class="text-muted small mb-2">Terlambat</p>
                                <h5 class="fw-bold mb-1" style="color: #ffab00;">14</h5>
                                <small class="text-muted">Target: &lt;20</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Presensi Guru -->
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e7e7ff;">
                            <i class="bx bx-id-card fs-3" style="color: #696cff;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold" style="color: #566a7f;">Presensi Guru</h5>
                            <small class="text-muted">Minggu Ini</small>
                        </div>
                        <div class="text-end">
                            <h3 class="fw-bold mb-0" style="color: #696cff;">428</h3>
                            <small style="color: #696cff;">
                                <i class="bx bx-check"></i> 94%
                            </small>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 rounded" style="background-color: #e7e7ff;">
                                <p class="text-muted small mb-2">Rata-rata Harian</p>
                                <h5 class="fw-bold mb-1" style="color: #696cff;">72</h5>
                                <small style="color: #696cff;">
                                    <i class="bx bx-up-arrow-alt"></i> +3
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background-color: #ffe0db;">
                                <p class="text-muted small mb-2">Tugas Terlambat</p>
                                <h5 class="fw-bold mb-1" style="color: #ff3e1d;">6</h5>
                                <small class="text-muted">Sebelum Jumat</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
