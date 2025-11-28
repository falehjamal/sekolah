@extends('layouts.app')

@section('title', 'Data Siswa')

@push('styles')
<!-- DataTables CSS -->
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
    border-radius: 0.75rem;
}
.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 24px;
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
            @php
                $disableSiswaForm = $jurusanList->isEmpty() || $kelasList->isEmpty();
            @endphp
            <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Data Siswa</h5>
                    <p class="text-muted mb-0 small">Kelola seluruh daftar siswa dengan tampilan informasi yang lebih ringkas dan rapi.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="tambahData()" @if ($disableSiswaForm) disabled @endif>
                    <i class="bx bx-plus me-1"></i> Tambah Siswa
                </button>
            </div>
            <div class="card-body">
                @if ($disableSiswaForm)
                    <div class="alert alert-warning" role="alert">
                        <strong>Perlu data pendukung:</strong>
                        <ul class="mb-0 ps-3">
                            @if ($jurusanList->isEmpty())
                                <li>Belum ada data jurusan. Tambahkan melalui menu Jurusan.</li>
                            @endif
                            @if ($kelasList->isEmpty())
                                <li>Belum ada data kelas. Tambahkan melalui menu Kelas.</li>
                            @endif
                        </ul>
                    </div>
                @endif
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tableSiswa">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Siswa</th>
                                <th>Detail</th>
                                <th>Akademik</th>
                                <th>Status</th>
                                <th width="15%">Aksi</th>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormTitle">Tambah Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSiswa">
                <div class="modal-body">
                    <input type="hidden" id="siswa_id" name="siswa_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nis" class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nis" name="nis" placeholder="Masukkan NIS" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nisn" name="nisn" placeholder="Masukkan NISN" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="jk" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" id="jk" name="jk" required>
                                <option value="">Pilih</option>
                                <option value="l">Laki-laki</option>
                                <option value="p">Perempuan</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tempat_lahir" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" placeholder="Tempat lahir" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="kelas_id" name="kelas_id" data-placeholder="Pilih Kelas" required>
                                <option value="">Pilih Kelas</option>
                                @foreach ($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }} (Tingkat {{ $kelas->tingkat }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="jurusan_id" class="form-label">Jurusan <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="jurusan_id" name="jurusan_id" data-placeholder="Pilih jurusan" required>
                                <option value="">Pilih Jurusan</option>
                                @foreach ($jurusanList as $jurusan)
                                    <option value="{{ $jurusan->id }}">{{ $jurusan->kode }} - {{ $jurusan->nama_jurusan }}</option>
                                @endforeach
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
                                <option value="alumni">Alumni</option>
                                <option value="keluar">Keluar</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let table;
let isEditMode = false;

$(document).ready(function() {
    const select2Config = {
        '#jurusan_id': 'Pilih Jurusan',
        '#kelas_id': 'Pilih Kelas'
    };

    Object.entries(select2Config).forEach(([selector, placeholder]) => {
        $(selector).select2({
            dropdownParent: $('#modalForm'),
            theme: 'bootstrap4',
            placeholder,
            width: '100%',
            allowClear: true
        });
    });

    // Initialize DataTable
    table = $('#tableSiswa').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('siswa.index') }}",
            type: 'GET'
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        responsive: true,
        autoWidth: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'siswa_info', name: 'nama', orderable: false, searchable: true },
            { data: 'detail_info', name: 'tanggal_lahir', orderable: false, searchable: true },
            { data: 'kelas_info', name: 'kelas_id', orderable: false, searchable: true },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
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
    $('#formSiswa').on('submit', function(e) {
        e.preventDefault();
        simpanData();
    });
});

function tambahData() {
    isEditMode = false;
    $('#modalFormTitle').text('Tambah Data Siswa');
    $('#formSiswa')[0].reset();
    $('#siswa_id').val('');
    $('#jurusan_id').val('').trigger('change');
    $('#kelas_id').val('').trigger('change');
    clearValidation();
    $('#modalForm').modal('show');
}

function editData(id) {
    isEditMode = true;
    $('#modalFormTitle').text('Edit Data Siswa');
    clearValidation();

    showLoading();

    $.ajax({
        url: "{{ url('siswa') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                $('#siswa_id').val(data.id);
                $('#nis').val(data.nis);
                $('#nisn').val(data.nisn);
                $('#nama').val(data.nama);
                $('#jk').val(data.jk);
                $('#tempat_lahir').val(data.tempat_lahir);
                $('#tanggal_lahir').val(data.tanggal_lahir);
                $('#alamat').val(data.alamat);
                $('#kelas_id').val(data.kelas_id ?? '').trigger('change');
                $('#jurusan_id').val(data.jurusan_id ?? '').trigger('change');
                $('#no_hp').val(data.no_hp);
                $('#status').val(data.status);

                $('#modalForm').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data siswa');
        }
    });
}

function simpanData() {
    const siswaId = $('#siswa_id').val();
    const url = siswaId ? "{{ url('siswa') }}/" + siswaId : "{{ route('siswa.store') }}";
    const method = siswaId ? 'PUT' : 'POST';

    const formData = {
        nis: $('#nis').val(),
        nisn: $('#nisn').val(),
        nama: $('#nama').val(),
        jk: $('#jk').val(),
        tempat_lahir: $('#tempat_lahir').val(),
        tanggal_lahir: $('#tanggal_lahir').val(),
        alamat: $('#alamat').val(),
        kelas_id: $('#kelas_id').val(),
        jurusan_id: $('#jurusan_id').val(),
        no_hp: $('#no_hp').val(),
        status: $('#status').val()
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
            url: "{{ url('siswa') }}/" + id,
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
                showToast('error', 'Error', 'Gagal menghapus data siswa');
            }
        });
    }
}

function showToast(type, title, message) {
    const toastEl = document.getElementById('toastNotification');
    const toast = new bootstrap.Toast(toastEl, {
        delay: 3000
    });

    // Set icon and color based on type
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

