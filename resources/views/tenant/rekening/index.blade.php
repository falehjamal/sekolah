@extends('layouts.app')

@section('title', 'Rekening')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Data Rekening</h5>
                    <p class="text-muted mb-0 small">Kelola data rekening bank untuk pembayaran.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="tambahData()">
                    <i class="bx bx-plus me-1"></i> Tambah Rekening
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tableRekening">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Bank</th>
                                <th>No. Rekening</th>
                                <th>Nama Pemilik</th>
                                <th width="12%">Aksi</th>
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

<!-- Modal Form -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormTitle">Tambah Rekening</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRekening">
                <div class="modal-body">
                    <input type="hidden" id="rekening_id" name="rekening_id">

                    <div class="mb-3">
                        <label for="bank" class="form-label">Nama Bank <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bank" name="bank" placeholder="Contoh: BCA, BNI, Mandiri" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="no_rekening" class="form-label">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="no_rekening" name="no_rekening" placeholder="Masukkan nomor rekening" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="nama_rekening" class="form-label">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_rekening" name="nama_rekening" placeholder="Masukkan nama pemilik rekening" required>
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

<!-- Toast Container -->
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

<!-- Loading Overlay -->
<div class="position-fixed top-0 start-0 w-100 h-100 d-none" id="loadingOverlay" style="background: rgba(0,0,0,0.5); z-index: 9998;">
    <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
let table;
let isEditMode = false;

$(document).ready(function() {
    // Initialize DataTable
    table = $('#tableRekening').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('rekening.index') }}",
            type: 'GET'
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        responsive: true,
        autoWidth: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'bank_info', name: 'bank', orderable: true, searchable: true },
            { data: 'no_rekening_display', name: 'no_rekening', orderable: true, searchable: true },
            { data: 'nama_rekening_display', name: 'nama_rekening', orderable: true, searchable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '12%' }
        ],
        language: {
            processing: "Memuat data...",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan halaman _PAGE_ dari _PAGES_",
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

    // Form Submit Handler
    $('#formRekening').on('submit', function(e) {
        e.preventDefault();
        simpanData();
    });
});

function tambahData() {
    isEditMode = false;
    $('#modalFormTitle').text('Tambah Rekening');
    $('#formRekening')[0].reset();
    $('#rekening_id').val('');
    clearValidation();
    $('#modalForm').modal('show');
}

function editData(id) {
    isEditMode = true;
    $('#modalFormTitle').text('Edit Rekening');
    clearValidation();

    showLoading();

    $.ajax({
        url: "{{ url('rekening') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                $('#rekening_id').val(data.id);
                $('#bank').val(data.bank);
                $('#no_rekening').val(data.no_rekening);
                $('#nama_rekening').val(data.nama_rekening);

                $('#modalForm').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data rekening');
        }
    });
}

function simpanData() {
    const rekeningId = $('#rekening_id').val();
    const url = rekeningId ? "{{ url('rekening') }}/" + rekeningId : "{{ route('rekening.store') }}";
    const method = rekeningId ? 'PUT' : 'POST';

    const formData = {
        bank: $('#bank').val(),
        no_rekening: $('#no_rekening').val(),
        nama_rekening: $('#nama_rekening').val()
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
                showToast('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            setBtnLoading(false);
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayValidationErrors(errors);
                showToast('error', 'Validasi Error', 'Periksa kembali form Anda');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                showToast('error', 'Error', xhr.responseJSON.message);
            } else {
                showToast('error', 'Error', 'Terjadi kesalahan saat menyimpan data');
            }
        }
    });
}

function deleteData(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        showLoading();

        $.ajax({
            url: "{{ url('rekening') }}/" + id,
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
                    showToast('error', 'Error', response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                showToast('error', 'Error', 'Gagal menghapus data rekening');
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
    });
}

function clearValidation() {
    $('.form-control, .form-select').removeClass('is-invalid');
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
