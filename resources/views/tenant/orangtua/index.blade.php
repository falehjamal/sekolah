@extends('layouts.app')

@section('title', 'Data Orang Tua')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<style>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
.select2-container--bootstrap4 .select2-selection {
    min-height: 38px;
    padding: 6px 8px;
}
.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 24px;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data Orang Tua</h5>
                <button type="button" class="btn btn-primary" onclick="tambahOrangtua()">
                    <i class="bx bx-plus me-1"></i> Tambah Orang Tua
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped" id="tableOrangtua">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Siswa</th>
                                <th>Nama</th>
                                <th>Hubungan</th>
                                <th>No HP</th>
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

<div class="modal fade" id="modalOrangtua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalOrangtuaTitle">Tambah Orang Tua</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formOrangtua">
                <div class="modal-body">
                    <input type="hidden" id="orangtua_id" name="orangtua_id">
                    <div class="mb-3">
                        <label for="siswa_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="siswa_id" name="siswa_id" data-placeholder="Pilih Siswa" required>
                            <option value="">Pilih Siswa</option>
                            @foreach ($siswaList as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->nama }} ({{ $siswa->nis }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Orang Tua <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama lengkap" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hubungan" class="form-label">Hubungan <span class="text-danger">*</span></label>
                            <select class="form-select" id="hubungan" name="hubungan" required>
                                <option value="">Pilih Hubungan</option>
                                <option value="ayah">Ayah</option>
                                <option value="ibu">Ibu</option>
                                <option value="wali">Wali</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Nomor kontak">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pekerjaan" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" placeholder="Pekerjaan">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanOrangtua">
                        <span class="spinner-border spinner-border-sm d-none" id="loadingSpinnerOrangtua"></span>
                        <span id="btnTextOrangtua">Simpan</span>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let orangtuaTable;
let isOrangtuaEditMode = false;

$(document).ready(function() {
    orangtuaTable = $('#tableOrangtua').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('orangtua.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'siswa_nama', name: 'siswa_id', orderable: false },
            { data: 'nama', name: 'nama' },
            { data: 'hubungan_label', name: 'hubungan', orderable: false },
            { data: 'no_hp', name: 'no_hp' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            processing: 'Memuat data...',
            zeroRecords: 'Data tidak ditemukan'
        },
        order: [[2, 'asc']]
    });

    $('#formOrangtua').on('submit', function(e) {
        e.preventDefault();
        simpanOrangtua();
    });

    $('#siswa_id').select2({
        dropdownParent: $('#modalOrangtua'),
        theme: 'bootstrap4',
        placeholder: 'Pilih Siswa',
        width: '100%',
        allowClear: true
    });
});

function tambahOrangtua() {
    isOrangtuaEditMode = false;
    $('#modalOrangtuaTitle').text('Tambah Orang Tua');
    $('#formOrangtua')[0].reset();
    $('#orangtua_id').val('');
    $('#siswa_id').val('').trigger('change');
    clearValidation();
    $('#modalOrangtua').modal('show');
}

function editOrangtua(id) {
    isOrangtuaEditMode = true;
    $('#modalOrangtuaTitle').text('Edit Orang Tua');
    clearValidation();
    showLoading();

    $.ajax({
        url: "{{ url('orangtua') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                $('#orangtua_id').val(data.id);
                $('#siswa_id').val(data.siswa_id).trigger('change');
                $('#nama').val(data.nama);
                $('#hubungan').val(data.hubungan);
                $('#no_hp').val(data.no_hp);
                $('#pekerjaan').val(data.pekerjaan);
                $('#alamat').val(data.alamat);
                $('#modalOrangtua').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data orang tua');
        }
    });
}

function simpanOrangtua() {
    const orangtuaId = $('#orangtua_id').val();
    const url = orangtuaId ? "{{ url('orangtua') }}/" + orangtuaId : "{{ route('orangtua.store') }}";
    const method = orangtuaId ? 'PUT' : 'POST';
    const formData = {
        siswa_id: $('#siswa_id').val(),
        nama: $('#nama').val(),
        hubungan: $('#hubungan').val(),
        no_hp: $('#no_hp').val(),
        pekerjaan: $('#pekerjaan').val(),
        alamat: $('#alamat').val()
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
                $('#modalOrangtua').modal('hide');
                orangtuaTable.ajax.reload();
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

function hapusOrangtua(id) {
    if (! confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        return;
    }

    showLoading();

    $.ajax({
        url: "{{ url('orangtua') }}/" + id,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                orangtuaTable.ajax.reload();
                showToast('success', 'Berhasil', response.message);
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal menghapus data orang tua');
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

        if (input.hasClass('select2-hidden-accessible')) {
            input.next('.select2-container').find('.select2-selection').addClass('is-invalid');
        }
    });
}

function clearValidation() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    $('.select2').each(function() {
        $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    });
}

function setBtnLoading(loading) {
    if (loading) {
        $('#loadingSpinnerOrangtua').removeClass('d-none');
        $('#btnTextOrangtua').text('Menyimpan...');
        $('#btnSimpanOrangtua').prop('disabled', true);
    } else {
        $('#loadingSpinnerOrangtua').addClass('d-none');
        $('#btnTextOrangtua').text('Simpan');
        $('#btnSimpanOrangtua').prop('disabled', false);
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
