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
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data Jurusan</h5>
                <button type="button" class="btn btn-primary" onclick="tambahJurusan()">
                    <i class="bx bx-plus me-1"></i> Tambah Jurusan
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped" id="tableJurusan">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode</th>
                                <th>Nama Jurusan</th>
                                <th>Deskripsi</th>
                                <th width="15%">Aksi</th>
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
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kode', name: 'kode' },
            { data: 'nama_jurusan', name: 'nama_jurusan' },
            { data: 'deskripsi_ringkas', name: 'deskripsi', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
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
