@extends('layouts.app')

@section('title', 'Data Kelas')

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
                <h5 class="mb-0">Data Kelas</h5>
                <button type="button" class="btn btn-primary" onclick="tambahKelas()">
                    <i class="bx bx-plus me-1"></i> Tambah Kelas
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped" id="tableKelas">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Jurusan</th>
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

<div class="modal fade" id="modalKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalKelasTitle">Tambah Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formKelas">
                <div class="modal-body">
                    <input type="hidden" id="kelas_id" name="kelas_id">
                    <div class="mb-3">
                        <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" placeholder="Contoh: XII IPA 1" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="tingkat" name="tingkat" min="1" max="12" placeholder="Contoh: 12" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="jurusan_id" class="form-label">Jurusan</label>
                        <select class="form-select" id="jurusan_id" name="jurusan_id">
                            <option value="">Pilih Jurusan (opsional)</option>
                            @foreach ($jurusanList as $jurusan)
                                <option value="{{ $jurusan->id }}">{{ $jurusan->kode }} - {{ $jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanKelas">
                        <span class="spinner-border spinner-border-sm d-none" id="loadingSpinnerKelas"></span>
                        <span id="btnTextKelas">Simpan</span>
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
let kelasTable;
let isKelasEditMode = false;

$(document).ready(function() {
    kelasTable = $('#tableKelas').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('kelas.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama_kelas', name: 'nama_kelas' },
            { data: 'tingkat', name: 'tingkat' },
            { data: 'jurusan_nama', name: 'jurusan_id', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            processing: 'Memuat data...',
            zeroRecords: 'Data tidak ditemukan'
        },
        order: [[1, 'asc']]
    });

    $('#formKelas').on('submit', function(e) {
        e.preventDefault();
        simpanKelas();
    });
});

function tambahKelas() {
    isKelasEditMode = false;
    $('#modalKelasTitle').text('Tambah Kelas');
    $('#formKelas')[0].reset();
    $('#kelas_id').val('');
    clearValidation();
    $('#modalKelas').modal('show');
}

function editKelas(id) {
    isKelasEditMode = true;
    $('#modalKelasTitle').text('Edit Kelas');
    clearValidation();
    showLoading();

    $.ajax({
        url: "{{ url('kelas') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                $('#kelas_id').val(data.id);
                $('#nama_kelas').val(data.nama_kelas);
                $('#tingkat').val(data.tingkat);
                $('#jurusan_id').val(data.jurusan_id ?? '');
                $('#modalKelas').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data kelas');
        }
    });
}

function simpanKelas() {
    const kelasId = $('#kelas_id').val();
    const url = kelasId ? "{{ url('kelas') }}/" + kelasId : "{{ route('kelas.store') }}";
    const method = kelasId ? 'PUT' : 'POST';
    const formData = {
        nama_kelas: $('#nama_kelas').val(),
        tingkat: $('#tingkat').val(),
        jurusan_id: $('#jurusan_id').val()
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
                $('#modalKelas').modal('hide');
                kelasTable.ajax.reload();
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

function hapusKelas(id) {
    if (! confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        return;
    }

    showLoading();

    $.ajax({
        url: "{{ url('kelas') }}/" + id,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                kelasTable.ajax.reload();
                showToast('success', 'Berhasil', response.message);
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal menghapus data kelas');
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
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

function setBtnLoading(loading) {
    if (loading) {
        $('#loadingSpinnerKelas').removeClass('d-none');
        $('#btnTextKelas').text('Menyimpan...');
        $('#btnSimpanKelas').prop('disabled', true);
    } else {
        $('#loadingSpinnerKelas').addClass('d-none');
        $('#btnTextKelas').text('Simpan');
        $('#btnSimpanKelas').prop('disabled', false);
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
