@extends('layouts.app')

@section('title', 'Data Jurusan')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
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
    color: #fff;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}
.avatar-purple {
    background: linear-gradient(135deg, #892CDC, #BC6FF1);
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
    min-width: 90px;
    font-weight: 600;
    color: #8c8fa5;
    text-transform: uppercase;
    font-size: 0.72rem;
}
.table-stack {
    padding: 0.75rem 0;
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
                    <h5 class="mb-1">Data Jurusan</h5>
                    <p class="text-muted mb-0 small">Kelola daftar jurusan beserta deskripsi dan kode uniknya.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="tambahJurusan()">
                    <i class="bx bx-plus me-1"></i> Tambah Jurusan
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tableJurusan">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Informasi</th>
                                <th>Detail</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalJurusan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalJurusanTitle">Tambah Jurusan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formJurusan">
                <div class="modal-body">
                    <input type="hidden" id="jurusan_id" name="jurusan_id">
                    <div class="mb-3">
                        <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kode" name="kode" placeholder="Masukkan kode jurusan" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="nama_jurusan" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_jurusan" name="nama_jurusan" placeholder="Masukkan nama jurusan" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi singkat jurusan"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanJurusan">
                        <span class="spinner-border spinner-border-sm d-none" id="loadingSpinnerJurusan"></span>
                        <span id="btnTextJurusan">Simpan</span>
                    </button>
                </div>
            </form>
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
<script>
let jurusanTable;
let isJurusanEditMode = false;

$(document).ready(function() {
    jurusanTable = $('#tableJurusan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('jurusan.index') }}",
            type: 'GET'
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'info_card', name: 'nama_jurusan', orderable: false, searchable: true },
            { data: 'detail_card', name: 'kode', orderable: false, searchable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '12%' }
        ],
        language: {
            processing: 'Memuat data...',
            zeroRecords: 'Data tidak ditemukan'
        }
    });

    $('#formJurusan').on('submit', function(e) {
        e.preventDefault();
        simpanJurusan();
    });
});

function tambahJurusan() {
    isJurusanEditMode = false;
    $('#modalJurusanTitle').text('Tambah Jurusan');
    $('#formJurusan')[0].reset();
    $('#jurusan_id').val('');
    clearValidation();
    $('#modalJurusan').modal('show');
}

function editJurusan(id) {
    isJurusanEditMode = true;
    $('#modalJurusanTitle').text('Edit Jurusan');
    clearValidation();
    showLoading();

    $.ajax({
        url: "{{ url('jurusan') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                $('#jurusan_id').val(data.id);
                $('#kode').val(data.kode);
                $('#nama_jurusan').val(data.nama_jurusan);
                $('#deskripsi').val(data.deskripsi);
                $('#modalJurusan').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data jurusan');
        }
    });
}

function simpanJurusan() {
    const jurusanId = $('#jurusan_id').val();
    const url = jurusanId ? "{{ url('jurusan') }}/" + jurusanId : "{{ route('jurusan.store') }}";
    const method = jurusanId ? 'PUT' : 'POST';
    const formData = {
        kode: $('#kode').val(),
        nama_jurusan: $('#nama_jurusan').val(),
        deskripsi: $('#deskripsi').val()
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
                $('#modalJurusan').modal('hide');
                jurusanTable.ajax.reload();
                showToast('success', 'Berhasil', response.message);
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            setBtnLoading(false);
            if (xhr.status === 422) {
                displayValidationErrors(xhr.responseJSON.errors);
                showToast('error', 'Validasi Error', 'Periksa kembali form Anda');
            } else {
                showToast('error', 'Error', 'Terjadi kesalahan saat menyimpan data');
            }
        }
    });
}

function hapusJurusan(id) {
    if (! confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        return;
    }

    showLoading();

    $.ajax({
        url: "{{ url('jurusan') }}/" + id,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                jurusanTable.ajax.reload();
                showToast('success', 'Berhasil', response.message);
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal menghapus data jurusan');
        }
    });
}

function showToast(type, title, message) {
    const toastEl = document.getElementById('toastNotification');
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    const iconClass = type === 'success'
        ? 'bx-check-circle text-success'
        : type === 'error'
            ? 'bx-error text-danger'
            : 'bx-info-circle text-info';

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
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

function setBtnLoading(loading) {
    if (loading) {
        $('#loadingSpinnerJurusan').removeClass('d-none');
        $('#btnTextJurusan').text('Menyimpan...');
        $('#btnSimpanJurusan').prop('disabled', true);
    } else {
        $('#loadingSpinnerJurusan').addClass('d-none');
        $('#btnTextJurusan').text('Simpan');
        $('#btnSimpanJurusan').prop('disabled', false);
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
