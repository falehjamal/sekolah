@extends('layouts.app')

@section('title', 'Data Guru')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<style>
.table-card {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}
.table-avatar {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #f7971e, #ffd200);
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
.table-stack {
    padding: 0.75rem 0;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.65rem;
    border-radius: 999px;
    background: rgba(95, 114, 255, 0.08);
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
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
.select2-container--bootstrap4 .select2-selection {
    min-height: 38px;
    padding: 6px 8px;
    border-radius: 0.75rem;
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
            <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Data Guru</h5>
                    <p class="text-muted mb-0 small">Kelola daftar guru, termasuk informasi kontak dan akun pengguna.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="tambahGuru()">
                    <i class="bx bx-plus me-1"></i> Tambah Guru
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tableGuru">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Guru</th>
                                <th>Kontak & Akun</th>
                                <th>Status</th>
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

<div class="modal fade" id="modalGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGuruTitle">Tambah Data Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formGuru">
                <div class="modal-body">
                    <input type="hidden" id="guru_id" name="guru_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama guru">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="text" class="form-control" id="nip" name="nip" placeholder="Masukkan NIP">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">Akun Pengguna</label>
                            <select class="form-select select2" id="user_id" name="user_id" data-placeholder="Pilih Akun Pengguna">
                                <option value="">Pilih Akun</option>
                                @foreach ($userAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->username }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Opsional: hubungkan guru dengan akun login yang ada.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label">No. HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Masukkan no HP">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanGuru">
                        <span class="spinner-border spinner-border-sm d-none" id="loadingGuruSpinner"></span>
                        <span id="btnGuruText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="toast-container">
    <div class="bs-toast toast" id="toastGuru" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bx bx-bell me-2" id="toastGuruIcon"></i>
            <div class="me-auto fw-semibold" id="toastGuruTitle">Notification</div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastGuruMessage"></div>
    </div>
</div>

<div class="position-fixed top-0 start-0 w-100 h-100 d-none" id="loadingGuruOverlay" style="background: rgba(0,0,0,0.5); z-index: 9998;">
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
let tableGuru;

$(document).ready(function () {
    $('#user_id').select2({
        dropdownParent: $('#modalGuru'),
        theme: 'bootstrap4',
        placeholder: 'Pilih Akun Pengguna',
        width: '100%',
        allowClear: true
    });

    tableGuru = $('#tableGuru').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('guru.index') }}",
            type: 'GET'
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'guru_info', name: 'nama', orderable: false, searchable: true },
            { data: 'contact_info', name: 'no_hp', orderable: false, searchable: true },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '15%' }
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

    $('#formGuru').on('submit', function (e) {
        e.preventDefault();
        simpanGuru();
    });
});

function tambahGuru() {
    $('#modalGuruTitle').text('Tambah Data Guru');
    $('#formGuru')[0].reset();
    $('#guru_id').val('');
    $('#user_id').val('').trigger('change');
    clearGuruValidation();
    $('#status').val('aktif');
    $('#modalGuru').modal('show');
}

function editGuru(id) {
    clearGuruValidation();
    showGuruLoading();

    $.ajax({
        url: "{{ url('guru') }}/" + id,
        type: 'GET',
        success: function (response) {
            hideGuruLoading();
            if (response.success) {
                const data = response.data;
                $('#modalGuruTitle').text('Edit Data Guru');
                $('#guru_id').val(data.id);
                $('#nama').val(data.nama);
                $('#nip').val(data.nip);
                $('#user_id').val(data.user_id ?? '').trigger('change');
                $('#jenis_kelamin').val(data.jenis_kelamin ?? '');
                $('#no_hp').val(data.no_hp);
                $('#alamat').val(data.alamat);
                $('#status').val(data.status);
                $('#modalGuru').modal('show');
            } else {
                showGuruToast('error', 'Error', response.message);
            }
        },
        error: function () {
            hideGuruLoading();
            showGuruToast('error', 'Error', 'Gagal mengambil data guru');
        }
    });
}

function simpanGuru() {
    const guruId = $('#guru_id').val();
    const url = guruId ? "{{ url('guru') }}/" + guruId : "{{ route('guru.store') }}";
    const method = guruId ? 'PUT' : 'POST';

    const formData = {
        user_id: $('#user_id').val(),
        nama: $('#nama').val(),
        nip: $('#nip').val(),
        jenis_kelamin: $('#jenis_kelamin').val(),
        no_hp: $('#no_hp').val(),
        alamat: $('#alamat').val(),
        status: $('#status').val()
    };

    clearGuruValidation();
    setGuruBtnLoading(true);

    $.ajax({
        url: url,
        type: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            setGuruBtnLoading(false);
            if (response.success) {
                $('#modalGuru').modal('hide');
                tableGuru.ajax.reload();
                showGuruToast('success', 'Berhasil', response.message);
            } else {
                showGuruToast('error', 'Error', response.message);
            }
        },
        error: function (xhr) {
            setGuruBtnLoading(false);
            if (xhr.status === 422) {
                displayGuruValidationErrors(xhr.responseJSON.errors);
                showGuruToast('error', 'Validasi Error', 'Periksa kembali form Anda');
            } else {
                showGuruToast('error', 'Error', 'Terjadi kesalahan saat menyimpan data');
            }
        }
    });
}

function hapusGuru(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        return;
    }

    showGuruLoading();

    $.ajax({
        url: "{{ url('guru') }}/" + id,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            hideGuruLoading();
            if (response.success) {
                tableGuru.ajax.reload();
                showGuruToast('success', 'Berhasil', response.message);
            } else {
                showGuruToast('error', 'Error', response.message);
            }
        },
        error: function () {
            hideGuruLoading();
            showGuruToast('error', 'Error', 'Gagal menghapus data guru');
        }
    });
}

function showGuruToast(type, title, message) {
    const toastEl = document.getElementById('toastGuru');
    const toast = new bootstrap.Toast(toastEl, {
        delay: 3000
    });

    const iconClass = type === 'success'
        ? 'bx-check-circle text-success'
        : type === 'error'
            ? 'bx-error text-danger'
            : 'bx-info-circle text-info';

    $('#toastGuruIcon').attr('class', 'bx me-2 ' + iconClass);
    $('#toastGuruTitle').text(title);
    $('#toastGuruMessage').text(message);

    toast.show();
}

function displayGuruValidationErrors(errors) {
    $.each(errors, function (field, messages) {
        const input = $('#' + field);
        input.addClass('is-invalid');
        input.siblings('.invalid-feedback').text(messages[0]);

        if (input.hasClass('select2-hidden-accessible')) {
            input.next('.select2-container').find('.select2-selection').addClass('is-invalid');
        }
    });
}

function clearGuruValidation() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    $('.select2').each(function () {
        $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    });
}

function setGuruBtnLoading(loading) {
    if (loading) {
        $('#loadingGuruSpinner').removeClass('d-none');
        $('#btnGuruText').text('Menyimpan...');
        $('#btnSimpanGuru').prop('disabled', true);
    } else {
        $('#loadingGuruSpinner').addClass('d-none');
        $('#btnGuruText').text('Simpan');
        $('#btnSimpanGuru').prop('disabled', false);
    }
}

function showGuruLoading() {
    $('#loadingGuruOverlay').removeClass('d-none');
}

function hideGuruLoading() {
    $('#loadingGuruOverlay').addClass('d-none');
}
</script>
@endpush

