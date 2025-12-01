@extends('layouts.app')

@section('title', 'Dashboard Siswa | ' . config('app.name', 'Sekolah'))

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mx-auto mb-3 avatar-initials bg-primary text-white fw-semibold">
                    {{ strtoupper(mb_substr($profile['name'], 0, 1)) }}
                </div>
                <h5 class="mb-0">{{ $profile['name'] }}</h5>
                <small class="text-muted">NIS {{ $profile['nis'] }}</small>

                <hr class="my-4">
                <ul class="list-unstyled text-start mb-0">
                    <li class="d-flex align-items-center mb-3">
                        <span class="avatar-initial rounded bg-label-primary me-3">
                            <i class="bx bx-book"></i>
                        </span>
                        <div>
                            <small class="text-muted d-block">Kelas / Jurusan</small>
                            <strong>{{ $profile['kelas'] }} &bull; {{ $profile['jurusan'] }}</strong>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <span class="avatar-initial rounded bg-label-success me-3">
                            <i class="bx bx-envelope"></i>
                        </span>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <strong>{{ $profile['email'] }}</strong>
                        </div>
                    </li>
                    <li class="d-flex align-items-center">
                        <span class="avatar-initial rounded bg-label-warning me-3">
                            <i class="bx bx-phone"></i>
                        </span>
                        <div>
                            <small class="text-muted d-block">Kontak</small>
                            <strong>{{ $profile['phone'] }}</strong>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header pb-2">
                <h5 class="mb-0">Ringkasan Absensi</h5>
                <small class="text-muted">Semester Ganjil 2025/2026</small>
            </div>
            <div class="card-body">
                <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
                    @foreach ($attendance['summary'] as $label => $value)
                        @php
                            $badgeClass = [
                                'hadir' => 'success',
                                'izin' => 'warning',
                                'sakit' => 'info',
                                'alpha' => 'danger',
                            ][$label] ?? 'primary';
                        @endphp
                        <div class="col">
                            <div class="border rounded p-3 text-center h-100">
                                <span class="badge bg-label-{{ $badgeClass }} mb-1 text-capitalize">{{ $label }}</span>
                                <h4 class="mb-0">{{ $value }}</h4>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendance['history'] as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item['tanggal'] }}</td>
                                    <td>
                                        @php
                                            $statusClass = match ($item['status']) {
                                                'Hadir' => 'success',
                                                'Izin' => 'warning',
                                                'Sakit' => 'info',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-label-{{ $statusClass }}">{{ $item['status'] }}</span>
                                    </td>
                                    <td>{{ $item['catatan'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-2">
                <h5 class="mb-0">Daftar Tagihan SPP</h5>
                <small class="text-muted">Riwayat pembayaran tiga bulan terakhir</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Kode Tagihan</th>
                                <th>Nominal</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                @php
                                    $statusMap = [
                                        'Lunas' => 'success',
                                        'Menunggu' => 'warning',
                                        'Belum Dibuat' => 'secondary',
                                    ];
                                    $statusClass = $statusMap[$invoice['status']] ?? 'primary';
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $invoice['bulan'] }}</td>
                                    <td>{{ $invoice['kode'] }}</td>
                                    <td>Rp {{ number_format($invoice['nominal'], 0, ',', '.') }}</td>
                                    <td>{{ $invoice['jatuh_tempo'] }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $statusClass }}">{{ $invoice['status'] }}</span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" type="button" disabled>
                                            <i class="bx bx-receipt me-1"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

