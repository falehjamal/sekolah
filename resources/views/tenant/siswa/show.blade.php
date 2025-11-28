@extends('layouts.app')

@section('title', 'Detail Siswa')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Detail Siswa</h5>
            <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">NIS</small>
                        <p class="fw-semibold mb-0">{{ $siswa->nis }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">NISN</small>
                        <p class="fw-semibold mb-0">{{ $siswa->nisn }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Nama Lengkap</small>
                        <p class="fw-semibold mb-0">{{ $siswa->nama }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Jenis Kelamin</small>
                        <p class="fw-semibold mb-0">{{ $siswa->jk_lengkap }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Tempat, Tanggal Lahir</small>
                        <p class="fw-semibold mb-0">
                            {{ $siswa->tempat_lahir }},
                            {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">No. HP</small>
                        <p class="fw-semibold mb-0">{{ $siswa->no_hp ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Kelas</small>
                        <p class="fw-semibold mb-0">Kelas {{ $siswa->kelas_id }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Jurusan</small>
                        <p class="fw-semibold mb-0">
                            @if ($siswa->jurusan)
                                {{ $siswa->jurusan->kode }} - {{ $siswa->jurusan->nama_jurusan }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Status</small>
                        <p class="mb-0">{!! $siswa->status_badge !!}</p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted text-uppercase d-block mb-1">Alamat</small>
                        <p class="fw-semibold mb-0">{{ $siswa->alamat }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

