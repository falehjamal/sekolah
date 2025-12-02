@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row gy-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="row row-bordered g-0 h-100">
        <div class="col-md-8">
                        <h5 class="card-header m-0 me-2 pb-3">Statistik Siswa</h5>
                        <div class="px-4 pb-4">
                            <p class="text-muted mb-1">Total Siswa</p>
                            <a href="{{ route('siswa.index') }}" class="text-decoration-none text-body d-inline-block">
                                <h2 class="fw-bold mb-2">{{ number_format($studentStats['total'] ?? 0, 0, ',', '.') }}</h2>
                            </a>
                            <small class="text-muted">Update per November 2025</small>
                            <div class="mt-4">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <small>Progres Penerimaan</small>
                                    <small class="fw-semibold">68%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 68%;"
                                        aria-valuenow="68" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 border-top border-top-md-0">
                        <div class="card-body">
                            <div class="text-center fw-semibold pt-2 mb-2">Komposisi Gender</div>
                            <div class="d-flex flex-column align-items-center px-xxl-4 px-lg-2 p-4 gap-3">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-label-primary p-2 me-2"><i class="bx bx-male text-primary"></i></span>
                                    <div class="d-flex flex-column text-start">
                                        <small>Laki-laki</small>
                                        <a href="{{ route('siswa.index') }}" class="text-decoration-none text-body">
                                            <h6 class="mb-0">{{ number_format($studentStats['male'] ?? 0, 0, ',', '.') }} siswa</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-info p-2 me-2"><i class="bx bx-female text-info"></i></span>
                                    <div class="d-flex flex-column text-start">
                                        <small>Perempuan</small>
                                        <a href="{{ route('siswa.index') }}" class="text-decoration-none text-body">
                                            <h6 class="mb-0">{{ number_format($studentStats['female'] ?? 0, 0, ',', '.') }} siswa</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="row row-bordered g-0 h-100">
                    <div class="col-md-8">
                        <h5 class="card-header m-0 me-2 pb-3">Statistik Guru</h5>
                        <div class="px-4 pb-4">
                            <p class="text-muted mb-1">Total Guru</p>
                            <a href="{{ route('guru.index') }}" class="text-decoration-none text-body d-inline-block">
                                <h2 class="fw-bold mb-2">{{ number_format($teacherStats['total'] ?? 0, 0, ',', '.') }}</h2>
                            </a>
                            <small class="text-muted">Update per November 2025</small>
                            <div class="mt-4">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <small>Kompetensi Terpenuhi</small>
                                    <small class="fw-semibold">92%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 92%;"
                                        aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 border-top border-top-md-0">
                <div class="card-body">
                            <div class="text-center fw-semibold pt-2 mb-2">Komposisi Gender</div>
                            <div class="d-flex flex-column align-items-center px-xxl-4 px-lg-2 p-4 gap-3">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-label-success p-2 me-2"><i class="bx bx-male text-success"></i></span>
                                    <div class="d-flex flex-column text-start">
                                        <small>Laki-laki</small>
                                        <a href="{{ route('guru.index') }}" class="text-decoration-none text-body">
                                            <h6 class="mb-0">{{ number_format($teacherStats['male'] ?? 0, 0, ',', '.') }} guru</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-warning p-2 me-2"><i class="bx bx-female text-warning"></i></span>
                                    <div class="d-flex flex-column text-start">
                                        <small>Perempuan</small>
                                        <a href="{{ route('guru.index') }}" class="text-decoration-none text-body">
                                            <h6 class="mb-0">{{ number_format($teacherStats['female'] ?? 0, 0, ',', '.') }} guru</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-4">
                <div class="col-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-label-primary p-3 rounded-circle">
                                        <i class="bx bxs-school text-primary fs-5"></i>
                                    </span>
                                    <div class="text-start">
                                        <span class="d-block text-muted">Jumlah Kelas</span>
                                        <a href="{{ route('kelas.index') }}" class="text-decoration-none text-body">
                                            <h3 class="card-title text-nowrap mb-0">
                                                {{ number_format($academicStats['classes'] ?? 0, 0, ',', '.') }}
                                            </h3>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="kelasOptions" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="kelasOptions">
                                        <a class="dropdown-item" href="javascript:void(0);">Detail</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Ekspor</a>
                                    </div>
                                </div>
                            </div>
                            <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +2 kelas baru</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-label-info p-3 rounded-circle">
                                        <i class="bx bxs-graduation text-info fs-5"></i>
                                    </span>
                                    <div class="text-start">
                                        <span class="d-block text-muted">Jumlah Kejuruan</span>
                                        <a href="{{ route('jurusan.index') }}" class="text-decoration-none text-body">
                                            <h3 class="card-title text-nowrap mb-0">
                                                {{ number_format($academicStats['majors'] ?? 0, 0, ',', '.') }}
                                            </h3>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="kejuruanOptions" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="kejuruanOptions">
                                        <a class="dropdown-item" href="javascript:void(0);">Detail</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Ekspor</a>
                                    </div>
                                </div>
                            </div>
                            <small class="text-info fw-semibold"><i class="bx bx-chevron-up"></i> Stabil</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-label-warning p-3 rounded-circle">
                                        <i class="bx bx-book-open text-warning fs-5"></i>
                                    </span>
                                    <div class="text-start">
                                        <span class="d-block text-muted">Jumlah Mata Pelajaran</span>
                                        <a href="{{ route('mata-pelajaran.index') }}" class="text-decoration-none text-body">
                                            <h3 class="card-title text-nowrap mb-0">
                                                {{ number_format($academicStats['subjects'] ?? 0, 0, ',', '.') }}
                                            </h3>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="mapelOptions" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="mapelOptions">
                                        <a class="dropdown-item" href="javascript:void(0);">Detail</a>
                                        <a class="dropdown-item" href="javascript:void(0);">Ekspor</a>
                                    </div>
                                </div>
                            </div>
                            <small class="text-warning fw-semibold"><i class="bx bx-minus"></i> Revisi kurikulum</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div class="card-title d-flex align-items-center gap-3 mb-0">
                                        <span class="badge bg-label-success p-3 rounded-circle">
                                            <i class="bx bxs-user-check text-success fs-5"></i>
                                        </span>
                                        <div>
                                            <h5 class="text-nowrap mb-1">Presensi Siswa</h5>
                                            <span class="badge bg-label-success rounded-pill">Minggu Ini</span>
                                        </div>
                                    </div>
                                    <div class="text-sm-end">
                                        <small class="text-success text-nowrap fw-semibold">
                                            <i class="bx bx-up-arrow-alt"></i> 95% kehadiran
                                        </small>
                                        <h3 class="mb-0">1.152</h3>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <div class="border rounded p-3 h-100">
                                            <p class="mb-1 text-muted">Rata-rata harian</p>
                                            <h4 class="mb-0">192 siswa</h4>
                                            <small class="text-success fw-semibold">+5% dibanding minggu lalu</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="border rounded p-3 h-100">
                                            <p class="mb-1 text-muted">Keterlambatan</p>
                                            <h4 class="mb-0 text-warning">14 kasus</h4>
                                            <small class="text-muted">Target: &lt; 20 kasus</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div class="card-title d-flex align-items-center gap-3 mb-0">
                                        <span class="badge bg-label-primary p-3 rounded-circle">
                                            <i class="bx bxs-user-detail text-primary fs-5"></i>
                                        </span>
                                        <div>
                                            <h5 class="text-nowrap mb-1">Presensi Guru</h5>
                                            <span class="badge bg-label-primary rounded-pill">Minggu Ini</span>
                                        </div>
                                    </div>
                                    <div class="text-sm-end">
                                        <small class="text-success text-nowrap fw-semibold">
                                            <i class="bx bx-chevron-up"></i> 94% ketepatan
                                        </small>
                                        <h3 class="mb-0">428</h3>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <div class="border rounded p-3 h-100">
                                            <p class="mb-1 text-muted">Rata-rata harian</p>
                                            <h4 class="mb-0">72 guru</h4>
                                            <small class="text-success fw-semibold">+3 hadir</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="border rounded p-3 h-100">
                                            <p class="mb-1 text-muted">Tugas Terlambat</p>
                                            <h4 class="mb-0 text-danger">6 agenda</h4>
                                            <small class="text-muted">Diselesaikan sebelum Jumat</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
