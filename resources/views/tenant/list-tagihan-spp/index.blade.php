@extends('layouts.app')

@section('title', 'List Tagihan SPP')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<style>
/* Modern Select2 Styling */
.select2-container--bootstrap4 .select2-selection {
    min-height: 42px;
    padding: 8px 12px;
    border-radius: 0.5rem;
    border: 1px solid #d9dee3;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.select2-container--bootstrap4 .select2-selection:focus,
.select2-container--bootstrap4.select2-container--focus .select2-selection {
    border-color: #696cff;
    box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
}
.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 24px;
    color: #566a7f;
    padding-left: 0;
}
.select2-container--bootstrap4 .select2-selection__placeholder {
    color: #a1acb8;
}
.select2-container--bootstrap4 .select2-selection__arrow {
    height: 40px;
}
.select2-container--bootstrap4 .select2-dropdown {
    border-radius: 0.5rem;
    border-color: #d9dee3;
    box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
}
.select2-container--bootstrap4 .select2-results__option {
    padding: 10px 14px;
    transition: background-color 0.15s ease;
}
.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background-color: #696cff;
    color: #fff;
}
.select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
    background-color: rgba(105, 108, 255, 0.08);
    color: #696cff;
}
.select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
    border-radius: 0.375rem;
    padding: 8px 12px;
    border-color: #d9dee3;
}
.select2-container--bootstrap4 .select2-search--dropdown .select2-search__field:focus {
    border-color: #696cff;
    box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
}

/* DataTable Modern Styling */
.datatable-top .form-select,
.datatable-top .form-control {
    border-radius: 0.5rem;
    border-color: #d9dee3;
    min-height: 42px;
}
.datatable-top .form-control {
    padding-left: 2.5rem;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23697a8d' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.414-1.415l-3.85-3.849zm-5.242.656a5 5 0 1 1 0-10 5 5 0 0 1 0 10'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 0.85rem center;
    background-size: 1rem;
}
.datatable-top .form-control:focus {
    border-color: #696cff;
    box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
}

/* Table Modern Styling */
.table-modern {
    border-collapse: separate;
    border-spacing: 0;
}
.table-modern thead th {
    text-transform: uppercase;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    color: #566a7f;
    background-color: #f8f9fa;
    border-bottom: 2px solid #e7e7e8;
    padding: 1rem 0.75rem;
    white-space: nowrap;
}
.table-modern tbody tr {
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #e7e7e8;
}
.table-modern tbody tr:hover {
    background-color: rgba(105, 108, 255, 0.04);
}
.table-modern tbody td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

/* Table Card Component */
.table-card {
    display: flex;
    gap: 0.875rem;
    align-items: flex-start;
}
.table-avatar {
    width: 44px;
    height: 44px;
    border-radius: 0.625rem;
    background: linear-gradient(135deg, #696cff 0%, #8592ff 100%);
    color: #fff;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(105, 108, 255, 0.4);
}
.table-card__title {
    font-weight: 600;
    font-size: 0.9375rem;
    color: #566a7f;
    margin-bottom: 0.25rem;
}
.table-meta {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.8125rem;
}
.table-meta li {
    display: flex;
    gap: 0.5rem;
    color: #697a8d;
}
.table-meta li span {
    min-width: 40px;
    font-weight: 600;
    color: #a1acb8;
    text-transform: uppercase;
    font-size: 0.6875rem;
    letter-spacing: 0.03em;
}

/* Pagination Styling */
.datatable-bottom .pagination {
    margin-bottom: 0;
}
.datatable-bottom .pagination .page-link {
    border-radius: 0.375rem;
    margin: 0 0.125rem;
    border: none;
    color: #697a8d;
    padding: 0.5rem 0.875rem;
    transition: all 0.15s ease;
}
.datatable-bottom .pagination .page-link:hover {
    background-color: rgba(105, 108, 255, 0.08);
    color: #696cff;
}
.datatable-bottom .pagination .page-item.active .page-link {
    background-color: #696cff;
    color: #fff;
    box-shadow: 0 2px 6px rgba(105, 108, 255, 0.4);
}

/* Filter Card */
.filter-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    border: 1px solid #e7e7e8;
    border-radius: 0.75rem;
}
.filter-card .card-body {
    padding: 1.25rem;
}

/* Summary Cards */
.summary-card {
    border-radius: 0.75rem;
    border: none;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.summary-card .card-body {
    padding: 1.25rem;
}
.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.625rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.summary-icon.bg-primary-light {
    background-color: rgba(105, 108, 255, 0.1);
    color: #696cff;
}
.summary-icon.bg-success-light {
    background-color: rgba(113, 221, 55, 0.1);
    color: #71dd37;
}
.summary-icon.bg-warning-light {
    background-color: rgba(255, 171, 0, 0.1);
    color: #ffab00;
}
.summary-icon.bg-danger-light {
    background-color: rgba(255, 62, 29, 0.1);
    color: #ff3e1d;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter Card -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bx bx-filter-alt me-2 text-primary fs-5"></i>
                    <h6 class="mb-0 fw-semibold">Filter Data</h6>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filter_bulan" class="form-label small fw-medium">Bulan</label>
                        <select class="form-select" id="filter_bulan">
                            <option value="1" {{ $currentMonth == 1 ? 'selected' : '' }}>Januari</option>
                            <option value="2" {{ $currentMonth == 2 ? 'selected' : '' }}>Februari</option>
                            <option value="3" {{ $currentMonth == 3 ? 'selected' : '' }}>Maret</option>
                            <option value="4" {{ $currentMonth == 4 ? 'selected' : '' }}>April</option>
                            <option value="5" {{ $currentMonth == 5 ? 'selected' : '' }}>Mei</option>
                            <option value="6" {{ $currentMonth == 6 ? 'selected' : '' }}>Juni</option>
                            <option value="7" {{ $currentMonth == 7 ? 'selected' : '' }}>Juli</option>
                            <option value="8" {{ $currentMonth == 8 ? 'selected' : '' }}>Agustus</option>
                            <option value="9" {{ $currentMonth == 9 ? 'selected' : '' }}>September</option>
                            <option value="10" {{ $currentMonth == 10 ? 'selected' : '' }}>Oktober</option>
                            <option value="11" {{ $currentMonth == 11 ? 'selected' : '' }}>November</option>
                            <option value="12" {{ $currentMonth == 12 ? 'selected' : '' }}>Desember</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_tahun" class="form-label small fw-medium">Tahun</label>
                        <select class="form-select" id="filter_tahun">
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter_kelas" class="form-label small fw-medium">Kelas</label>
                        <select class="form-select select2" id="filter_kelas" data-placeholder="Semua Kelas">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }} (Tingkat {{ $kelas->tingkat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_siswa" class="form-label small fw-medium">Siswa</label>
                        <select class="form-select select2" id="filter_siswa" data-placeholder="Semua Siswa">
                            <option value="">Semua Siswa</option>
                            @foreach ($siswaList as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->nis }} - {{ $siswa->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="applyFilter()">
                            <i class="bx bx-search me-1"></i> Tampilkan
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetFilter()">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4" id="summaryCards">
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon bg-primary-light me-3">
                                <i class="bx bx-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-muted small">Total Siswa</h6>
                                <h4 class="mb-0 fw-bold" id="totalSiswa">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon bg-success-light me-3">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-muted small">Lunas</h6>
                                <h4 class="mb-0 fw-bold text-success" id="totalLunas">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon bg-warning-light me-3">
                                <i class="bx bx-time-five"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-muted small">Sebagian</h6>
                                <h4 class="mb-0 fw-bold text-warning" id="totalSebagian">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon bg-danger-light me-3">
                                <i class="bx bx-x-circle"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-muted small">Belum Bayar</h6>
                                <h4 class="mb-0 fw-bold text-danger" id="totalBelumBayar">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">List Tagihan SPP</h5>
                    <p class="text-muted mb-0 small" id="filterInfo">
                        Periode: <span class="fw-medium" id="periodeLabel">-</span>
                    </p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern" id="tableTagihan" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Nominal SPP</th>
                                <th>Dibayar</th>
                                <th>Sisa Tagihan</th>
                                <th width="12%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let table;

const bulanNames = {
    1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
    5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
    9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
};

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        allowClear: true,
        width: '100%'
    });

    // Initialize DataTable
    table = $('#tableTagihan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('list-tagihan-spp.index') }}",
            type: 'GET',
            data: function(d) {
                d.bulan = $('#filter_bulan').val();
                d.tahun = $('#filter_tahun').val();
                d.siswa_id = $('#filter_siswa').val();
                d.kelas_id = $('#filter_kelas').val();
            },
            dataSrc: function(json) {
                // Update summary cards
                updateSummary(json.data);
                return json.data;
            }
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        responsive: true,
        autoWidth: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'siswa_info', name: 'nama', orderable: true, searchable: true },
            { data: 'kelas_info', name: 'kelas', orderable: true, searchable: true },
            { data: 'nominal_spp_display', name: 'nominal_spp', orderable: true, searchable: false },
            { data: 'total_dibayar_display', name: 'total_dibayar', orderable: true, searchable: false },
            { data: 'sisa_tagihan_display', name: 'sisa_tagihan', orderable: true, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false, width: '12%' }
        ],
        language: {
            processing: "Memuat data...",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Tidak ada data ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data yang tersedia",
            infoFiltered: "(difilter dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        order: [[1, 'asc']]
    });

    // Update periode label on init
    updatePeriodeLabel();
});

function updateSummary(data) {
    let totalSiswa = data.length;
    let totalLunas = 0;
    let totalSebagian = 0;
    let totalBelumBayar = 0;

    data.forEach(function(item) {
        if (item.status_lunas) {
            totalLunas++;
        } else if (item.total_dibayar > 0) {
            totalSebagian++;
        } else {
            totalBelumBayar++;
        }
    });

    $('#totalSiswa').text(totalSiswa);
    $('#totalLunas').text(totalLunas);
    $('#totalSebagian').text(totalSebagian);
    $('#totalBelumBayar').text(totalBelumBayar);
}

function updatePeriodeLabel() {
    const bulan = $('#filter_bulan').val();
    const tahun = $('#filter_tahun').val();
    $('#periodeLabel').text(bulanNames[bulan] + ' ' + tahun);
}

function applyFilter() {
    updatePeriodeLabel();
    table.ajax.reload();
}

function resetFilter() {
    const currentMonth = {{ $currentMonth }};
    const currentYear = {{ $currentYear }};

    $('#filter_bulan').val(currentMonth);
    $('#filter_tahun').val(currentYear);
    $('#filter_kelas').val('').trigger('change');
    $('#filter_siswa').val('').trigger('change');

    updatePeriodeLabel();
    table.ajax.reload();
}
</script>
@endpush
