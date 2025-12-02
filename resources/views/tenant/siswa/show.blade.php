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
                        <small class="text-muted text-uppercase">Akun Pengguna</small>
                        <p class="fw-semibold mb-0">
                            @if ($siswa->user)
                                {{ $siswa->user->name }} ({{ $siswa->user->username ?? '-' }})
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Kelas</small>
                        <p class="fw-semibold mb-0">
                            @if ($siswa->kelas)
                                {{ $siswa->kelas->nama_kelas }} (Tingkat {{ $siswa->kelas->tingkat }})
                            @else
                                -
                            @endif
                        </p>
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
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">SPP</small>
                        <p class="fw-semibold mb-0">
                            @if ($siswa->spp)
                                {{ $siswa->spp->nama ?? 'SPP' }} - Rp {{ number_format($siswa->spp->nominal, 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted text-uppercase d-block mb-1">Alamat</small>
                        <p class="fw-semibold mb-0">{{ $siswa->alamat }}</p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted text-uppercase d-block mb-1">Orang Tua / Wali</small>
                        @if ($siswa->orangtua->isNotEmpty())
                            <ul class="list-unstyled mb-0">
                                @foreach ($siswa->orangtua as $orangtua)
                                    <li class="mb-2">
                                        <span class="fw-semibold">{{ $orangtua->nama }}</span>
                                        <small class="text-muted">- {{ ucfirst($orangtua->hubungan) }}</small>
                                        @if ($orangtua->no_hp)
                                            <span class="d-block text-muted">No HP: {{ $orangtua->no_hp }}</span>
                                        @endif
                                        @if ($orangtua->pekerjaan)
                                            <span class="d-block text-muted">Pekerjaan: {{ $orangtua->pekerjaan }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="fw-semibold mb-0">-</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

