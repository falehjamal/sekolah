@extends('layouts.app')

@section('title', 'Detail Guru')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Detail Guru</h5>
            <a href="{{ route('guru.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Nama</small>
                        <p class="fw-semibold mb-0">{{ $guru->nama }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">NIP</small>
                        <p class="fw-semibold mb-0">{{ $guru->nip ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Jenis Kelamin</small>
                        <p class="fw-semibold mb-0">{{ $guru->jenis_kelamin_label }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Status</small>
                        <p class="mb-0">{!! $guru->status_badge !!}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">No. HP</small>
                        <p class="fw-semibold mb-0">{{ $guru->no_hp ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase">Akun Pengguna</small>
                        <p class="fw-semibold mb-0">
                            @if ($guru->user)
                                {{ $guru->user->name }} ({{ $guru->user->username ?? '-' }})
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted text-uppercase d-block mb-1">Alamat</small>
                        <p class="fw-semibold mb-0">{{ $guru->alamat ?? '-' }}</p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted text-uppercase d-block mb-1">Dibuat / Diperbarui</small>
                        <p class="fw-semibold mb-0">
                            {{ $guru->created_at?->translatedFormat('d F Y H:i') ?? '-' }}
                            <span class="text-muted">/</span>
                            {{ $guru->updated_at?->translatedFormat('d F Y H:i') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

