@extends('layouts.app')

@section('title', 'Pemotongan Guru')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
.datatable-top .form-select,
.datatable-top .form-control {
    border-radius: 999px;
    border-color: var(--bs-border-color);
}
.datatable-top .form-control {
    padding-left: 2.25rem;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23888' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.414-1.415l-3.85-3.849zm-5.242.656a5 5 0 1 1 0-10 5 5 0 0 1 0 10'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 0.85rem center;
    background-size: 1rem;
}
.table-modern thead th {
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.04em;
    color: #9195a3;
    border-bottom: none;
}
.table-modern tbody tr {
    border-bottom: 1px solid rgba(145, 149, 163, 0.15);
}
.table-card {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}
.table-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}
.table-avatar {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #5F72FF, #9921E8);
    color: #fff;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}
.table-card__title {
    font-weight: 600;
    font-size: 0.95rem;
}
.table-meta {
    list-style: none;
    padding: 0;
    margin: 0.25rem 0 0 0;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    font-size: 0.85rem;
}
.table-meta li {
    display: flex;
    gap: 0.75rem;
}
.table-meta li span {
    min-width: 70px;
    font-weight: 600;
    color: #8c8fa5;
    text-transform: uppercase;
    font-size: 0.72rem;
}
.datatable-bottom .pagination {
    margin-bottom: 0;
}
.datatable-bottom .pagination .page-link {
    border-radius: 10px;
    margin: 0 0.15rem;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    font-weight: 500;
    color: #697a8d;
}
.detail-value {
    font-weight: 600;
    text-align: right;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Data Pemotongan Guru</h5>
                    <p class="text-muted mb-0 small">Kelola pemotongan / potongan gaji guru.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="tambahData()">
                    <i class="bx bx-plus me-1"></i> Tambah Pemotongan
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tablePemotongan">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Guru</th>
                                <th>Nama Pemotongan</th>
                                <th>Jenis</th>
                                <th>Nominal</th>
                                <th>Waktu</th>
                                <th width="18%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormTitle">Tambah Pemotongan Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPemotongan">
                <div class="modal-body">
                    <input type="hidden" id="pemotongan_id" name="pemotongan_id">

                    <div class="mb-3">
                        <label for="guru_id" class="form-label">Guru <span class="text-danger">*</span></label>
                        <select class="form-select" id="guru_id" name="guru_id" required>
                            <option value="">Pilih Guru</option>
                            @foreach($guruList as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }} {{ $guru->nip ? '(' . $guru->nip . ')' : '' }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="nama_pemotongan" class="form-label">Nama Pemotongan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_pemotongan" name="nama_pemotongan" placeholder="Contoh: Potongan Keterlambatan" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_pemotongan" class="form-label">Jenis Pemotongan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="jenis_pemotongan" name="jenis_pemotongan" placeholder="Contoh: Administratif" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nominal_pemotongan" class="form-label">Nominal Pemotongan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" id="nominal_pemotongan" name="nominal_pemotongan" placeholder="0" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="waktu" class="form-label">Waktu <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="waktu" name="waktu" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">
                        <span class="spinner-border spinner-border-sm d-none" id="loadingSpinner"></span>
                        <span id="btnText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pemotongan Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="avatar avatar-lg bg-label-primary me-3">
                        <span class="avatar-initial rounded-circle" id="detail_avatar">-</span>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0" id="detail_nama">-</h5>
                        <small class="text-muted" id="detail_nip">-</small>
                    </div>
                </div>

                <hr>
                <h6 class="mb-3"><i class="bx bx-money me-1"></i> Informasi Pemotongan</h6>

                <div class="detail-row">
                    <span class="detail-label">Nama Pemotongan</span>
                    <span class="detail-value" id="detail_nama_pemotongan">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Jenis Pemotongan</span>
                    <span class="detail-value" id="detail_jenis_pemotongan">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nominal</span>
                    <span class="detail-value" id="detail_nominal_pemotongan">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Waktu</span>
                    <span class="detail-value" id="detail_waktu">-</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container">
    <div class="bs-toast toast" id="toastNotification" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bx bx-bell me-2" id="toastIcon"></i>
            <div class="me-auto fw-semibold" id="toastTitle">Notification</div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<div class="position-fixed top-0 start-0 w-100 h-100 d-none" id="loadingOverlay" style="background: rgba(0,0,0,0.5); z-index: 9998;">
    <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let table;

$(document).ready(function() {
    $('#guru_id').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalForm'),
        placeholder: 'Pilih Guru',
        allowClear: true
    });

    $('#nominal_pemotongan').on('input', function() {
        const value = $(this).val().replace(/[^0-9.,]/g, '');
        $(this).val(value);
    });

    table = $('#tablePemotongan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('pemotongan.index') }}",
            type: 'GET'
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        responsive: true,
        autoWidth: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'guru_info', name: 'guru.nama', orderable: false, searchable: false },
            { data: 'nama_pemotongan', name: 'nama_pemotongan' },
            { data: 'jenis_pemotongan_display', name: 'jenis_pemotongan', orderable: false, searchable: false },
            { data: 'nominal_pemotongan_display', name: 'nominal_pemotongan', orderable: false, searchable: false, className: 'text-nowrap' },
            { data: 'waktu_display', name: 'waktu', orderable: false, searchable: false, className: 'text-nowrap' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '18%' }
        ],
        language: {
            processing: 'Memuat data...',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            zeroRecords: 'Data tidak ditemukan',
            info: 'Menampilkan halaman _PAGE_ dari _PAGES_',
            infoEmpty: 'Tidak ada data yang tersedia',
            infoFiltered: '(difilter dari _MAX_ total data)',
            search: 'Cari:',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Selanjutnya',
                previous: 'Sebelumnya'
            }
        },
        order: []
    });

    $('#formPemotongan').on('submit', function(e) {
        e.preventDefault();
        simpanData();
    });

    $('#modalForm').on('hidden.bs.modal', function() {
        $('#formPemotongan')[0].reset();
        $('#guru_id').val('').trigger('change');
        $('#pemotongan_id').val('');
        clearValidation();
        setBtnLoading(false);
    });
});

function tambahData() {
    $('#modalFormTitle').text('Tambah Pemotongan Guru');
    $('#formPemotongan')[0].reset();
    $('#guru_id').val('').trigger('change');
    $('#pemotongan_id').val('');

    const now = new Date();
    const datetime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    $('#waktu').val(datetime);

    clearValidation();
    $('#modalForm').modal('show');
}

function editData(id) {
    clearValidation();
    showLoading();

    $.ajax({
        url: "{{ url('pemotongan') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                $('#modalFormTitle').text('Edit Pemotongan Guru');
                $('#pemotongan_id').val(data.id);
                $('#guru_id').val(data.guru_id).trigger('change');
                $('#nama_pemotongan').val(data.nama_pemotongan);
                $('#jenis_pemotongan').val(data.jenis_pemotongan);
                $('#nominal_pemotongan').val(data.nominal_pemotongan);
                $('#waktu').val(data.waktu);

                $('#modalForm').modal('show');
            } else {
                showToast('error', 'Error', response.message || 'Gagal mengambil data');
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data pemotongan');
        }
    });
}

function detailData(id) {
    showLoading();

    $.ajax({
        url: "{{ url('pemotongan') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                const initial = data.guru_nama ? data.guru_nama.substring(0, 2).toUpperCase() : '-';

                $('#detail_avatar').text(initial);
                $('#detail_nama').text(data.guru_nama || '-');
                $('#detail_nip').text(data.guru_nip ? 'NIP: ' + data.guru_nip : '-');
                $('#detail_nama_pemotongan').text(data.nama_pemotongan || '-');
                $('#detail_jenis_pemotongan').text(data.jenis_pemotongan || '-');
                $('#detail_nominal_pemotongan').text(data.nominal_pemotongan_format || '-');
                $('#detail_waktu').text(data.waktu_format || '-');

                $('#modalDetail').modal('show');
            } else {
                showToast('error', 'Error', response.message || 'Data tidak ditemukan');
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data pemotongan');
        }
    });
}

function simpanData() {
    const id = $('#pemotongan_id').val();
    const url = id ? "{{ url('pemotongan') }}/" + id : "{{ route('pemotongan.store') }}";
    const method = id ? 'PUT' : 'POST';

    const formData = {
        guru_id: $('#guru_id').val(),
        nama_pemotongan: $('#nama_pemotongan').val(),
        jenis_pemotongan: $('#jenis_pemotongan').val(),
        nominal_pemotongan: $('#nominal_pemotongan').val(),
        waktu: $('#waktu').val()
    };

    clearValidation();
    setBtnLoading(true);

    $.ajax({
        url: url,
        type: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            setBtnLoading(false);
            if (response.success) {
                $('#modalForm').modal('hide');
                table.ajax.reload();
                showToast('success', 'Berhasil', response.message);
            } else {
                showToast('error', 'Error', response.message || 'Terjadi kesalahan');
            }
        },
        error: function(xhr) {
            setBtnLoading(false);
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors || {};
                displayValidationErrors(errors);
                showToast('error', 'Validasi Gagal', 'Periksa kembali form Anda');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                showToast('error', 'Error', xhr.responseJSON.message);
            } else {
                showToast('error', 'Error', 'Terjadi kesalahan pada server');
            }
        }
    });
}

function deleteData(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data pemotongan ini?')) {
        showLoading();

        $.ajax({
            url: "{{ url('pemotongan') }}/" + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    table.ajax.reload();
                    showToast('success', 'Berhasil', response.message);
                } else {
                    showToast('error', 'Error', response.message || 'Terjadi kesalahan');
                }
            },
            error: function() {
                hideLoading();
                showToast('error', 'Error', 'Gagal menghapus data pemotongan');
            }
        });
    }
}

function showToast(type, title, message) {
    const toastEl = document.getElementById('toastNotification');
    const toast = new bootstrap.Toast(toastEl, {
        delay: 3000
    });

    const iconClass = type === 'success' ? 'bx-check-circle text-success' :
                     type === 'error' ? 'bx-error text-danger' :
                     'bx-info-circle text-info';

    $('#toastIcon').attr('class', 'bx me-2 ' + iconClass);
    $('#toastTitle').text(title);
    $('#toastMessage').text(message);

    toast.show();
}

function displayValidationErrors(errors) {
    $.each(errors, function(field, messages) {
        const input = $('#' + field);
        input.addClass('is-invalid');
        input.siblings('.invalid-feedback').text(messages[0]);

        if (field === 'guru_id') {
            input.next('.select2').find('.select2-selection').addClass('is-invalid');
        }
    });
}

function clearValidation() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.select2-selection').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

function setBtnLoading(loading) {
    if (loading) {
        $('#loadingSpinner').removeClass('d-none');
        $('#btnText').text('Menyimpan...');
        $('#btnSimpan').prop('disabled', true);
    } else {
        $('#loadingSpinner').addClass('d-none');
        $('#btnText').text('Simpan');
        $('#btnSimpan').prop('disabled', false);
    }
}

function showLoading() {
    $('#loadingOverlay').removeClass('d-none');
}

function hideLoading() {
    $('#loadingOverlay').addClass('d-none');
}
</script>
@endpush
