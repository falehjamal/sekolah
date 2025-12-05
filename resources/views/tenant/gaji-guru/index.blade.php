@extends('layouts.app')

@section('title', 'Gaji Guru')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- Select2 CSS -->
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
    min-width: 40px;
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
.detail-total {
    background-color: rgba(105, 108, 255, 0.1);
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}
.detail-total .detail-label {
    font-size: 1rem;
}
.detail-total .detail-value {
    font-size: 1.25rem;
    color: #696cff;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Data Gaji Guru</h5>
                    <p class="text-muted mb-0 small">Kelola data gaji guru dan tunjangan.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="tambahData()">
                    <i class="bx bx-plus me-1"></i> Tambah Gaji Guru
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tableGajiGuru">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Guru</th>
                                <th>TTL</th>
                                <th>Tgl Bergabung</th>
                                <th>Jenis Gaji</th>
                                <th>Total Gaji</th>
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
                <h5 class="modal-title" id="modalFormTitle">Tambah Gaji Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formGajiGuru">
                <div class="modal-body">
                    <input type="hidden" id="gaji_guru_id" name="gaji_guru_id">

                    <div class="row">
                        <div class="col-md-6">
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
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_gaji" class="form-label">Jenis Gaji <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_gaji" name="jenis_gaji" required>
                                    <option value="bulanan">Bulanan</option>
                                    <option value="harian">Harian</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" placeholder="Contoh: Jakarta">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_bergabung" class="form-label">Tanggal Bergabung</label>
                                <input type="date" class="form-control" id="tanggal_bergabung" name="tanggal_bergabung">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="mb-3"><i class="bx bx-money me-1"></i> Komponen Gaji</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gaji_pokok" class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="gaji_pokok" name="gaji_pokok" placeholder="0" min="0" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="uang_makan" class="form-label">Uang Makan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="uang_makan" name="uang_makan" placeholder="0" min="0" value="0">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="uang_transport" class="form-label">Uang Transport</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="uang_transport" name="uang_transport" placeholder="0" min="0" value="0">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tunjangan_jabatan" class="form-label">Tunjangan Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="tunjangan_jabatan" name="tunjangan_jabatan" placeholder="0" min="0" value="0">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tunjangan_lain" class="form-label">Tunjangan Lain</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="tunjangan_lain" name="tunjangan_lain" placeholder="0" min="0" value="0">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
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

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Gaji Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar avatar-xl mx-auto mb-3">
                        <div class="avatar-initials avatar-xl" id="detail_avatar">-</div>
                    </div>
                    <h5 class="mb-1" id="detail_nama">-</h5>
                    <p class="text-muted mb-0" id="detail_nip">-</p>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Tempat, Tanggal Lahir</small>
                        <p class="mb-0 fw-medium" id="detail_ttl">-</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Tanggal Bergabung</small>
                        <p class="mb-0 fw-medium" id="detail_bergabung">-</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Jenis Gaji</small>
                        <p class="mb-0" id="detail_jenis_gaji">-</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Status</small>
                        <p class="mb-0" id="detail_status">-</p>
                    </div>
                </div>

                <hr>
                <h6 class="mb-3"><i class="bx bx-money me-1"></i> Rincian Gaji</h6>

                <div class="detail-row">
                    <span class="detail-label">Gaji Pokok</span>
                    <span class="detail-value" id="detail_gaji_pokok">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Uang Makan</span>
                    <span class="detail-value" id="detail_uang_makan">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Uang Transport</span>
                    <span class="detail-value" id="detail_uang_transport">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tunjangan Jabatan</span>
                    <span class="detail-value" id="detail_tunjangan_jabatan">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tunjangan Lain</span>
                    <span class="detail-value" id="detail_tunjangan_lain">-</span>
                </div>

                <div class="detail-total">
                    <div class="detail-row mb-0">
                        <span class="detail-label">Total Gaji</span>
                        <span class="detail-value" id="detail_total_gaji">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
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
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let table;
let isEditMode = false;

$(document).ready(function() {
    // Initialize Select2
    $('#guru_id').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalForm'),
        placeholder: 'Pilih Guru',
        allowClear: true
    });

    // Initialize DataTable
    table = $('#tableGajiGuru').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('gaji-guru.index') }}",
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
            { data: 'ttl', name: 'tempat_lahir', orderable: false, searchable: false },
            { data: 'tanggal_bergabung_display', name: 'tanggal_bergabung', orderable: false, searchable: false },
            { data: 'jenis_gaji_badge', name: 'jenis_gaji', orderable: false, searchable: false },
            { data: 'gaji_display', name: 'gaji_pokok', orderable: false, searchable: false },
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
        order: []
    });

    // Form Submit Handler
    $('#formGajiGuru').on('submit', function(e) {
        e.preventDefault();
        simpanData();
    });
});

function tambahData() {
    isEditMode = false;
    $('#modalFormTitle').text('Tambah Gaji Guru');
    $('#formGajiGuru')[0].reset();
    $('#gaji_guru_id').val('');
    $('#guru_id').val('').trigger('change');
    $('#uang_makan, #uang_transport, #tunjangan_jabatan, #tunjangan_lain').val(0);
    clearValidation();
    $('#modalForm').modal('show');
}

function editData(id) {
    isEditMode = true;
    $('#modalFormTitle').text('Edit Gaji Guru');
    clearValidation();

    showLoading();

    $.ajax({
        url: "{{ url('gaji-guru') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                $('#gaji_guru_id').val(data.id);
                $('#guru_id').val(data.guru_id).trigger('change');
                $('#tempat_lahir').val(data.tempat_lahir || '');
                $('#tanggal_lahir').val(data.tanggal_lahir || '');
                $('#tanggal_bergabung').val(data.tanggal_bergabung || '');
                $('#jenis_gaji').val(data.jenis_gaji);
                $('#gaji_pokok').val(data.gaji_pokok);
                $('#uang_makan').val(data.uang_makan);
                $('#uang_transport').val(data.uang_transport);
                $('#tunjangan_jabatan').val(data.tunjangan_jabatan);
                $('#tunjangan_lain').val(data.tunjangan_lain);
                $('#status').val(data.status);

                $('#modalForm').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data gaji guru');
        }
    });
}

function detailData(id) {
    showLoading();

    $.ajax({
        url: "{{ url('gaji-guru') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;

                // Set avatar
                const initial = data.guru_nama ? data.guru_nama.substring(0, 2).toUpperCase() : '-';
                $('#detail_avatar').text(initial);

                // Set info
                $('#detail_nama').text(data.guru_nama || '-');
                $('#detail_nip').text(data.guru_nip ? 'NIP: ' + data.guru_nip : '-');

                // TTL
                const tempat = data.tempat_lahir || '-';
                const tglLahir = data.tanggal_lahir_format || '-';
                $('#detail_ttl').text(tempat + ', ' + tglLahir);

                $('#detail_bergabung').text(data.tanggal_bergabung_format || '-');
                $('#detail_jenis_gaji').html(data.jenis_gaji_badge);
                $('#detail_status').html(data.status_badge);

                // Gaji details
                $('#detail_gaji_pokok').text(data.gaji_pokok_format);
                $('#detail_uang_makan').text(data.uang_makan_format);
                $('#detail_uang_transport').text(data.uang_transport_format);
                $('#detail_tunjangan_jabatan').text(data.tunjangan_jabatan_format);
                $('#detail_tunjangan_lain').text(data.tunjangan_lain_format);
                $('#detail_total_gaji').text(data.total_gaji_format);

                $('#modalDetail').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data gaji guru');
        }
    });
}

function simpanData() {
    const gajiGuruId = $('#gaji_guru_id').val();
    const url = gajiGuruId ? "{{ url('gaji-guru') }}/" + gajiGuruId : "{{ route('gaji-guru.store') }}";
    const method = gajiGuruId ? 'PUT' : 'POST';

    const formData = {
        guru_id: $('#guru_id').val(),
        tempat_lahir: $('#tempat_lahir').val(),
        tanggal_lahir: $('#tanggal_lahir').val() || null,
        tanggal_bergabung: $('#tanggal_bergabung').val() || null,
        jenis_gaji: $('#jenis_gaji').val(),
        gaji_pokok: $('#gaji_pokok').val(),
        uang_makan: $('#uang_makan').val() || 0,
        uang_transport: $('#uang_transport').val() || 0,
        tunjangan_jabatan: $('#tunjangan_jabatan').val() || 0,
        tunjangan_lain: $('#tunjangan_lain').val() || 0,
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
                const errors = xhr.responseJSON.errors || {};
                if (xhr.responseJSON.message && !Object.keys(errors).length) {
                    showToast('error', 'Error', xhr.responseJSON.message);
                } else {
                    displayValidationErrors(errors);
                    showToast('error', 'Validasi Error', 'Periksa kembali form Anda');
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                showToast('error', 'Error', xhr.responseJSON.message);
            } else {
                showToast('error', 'Error', 'Terjadi kesalahan saat menyimpan data');
            }
        }
    });
}

function deleteData(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data gaji guru ini?')) {
        showLoading();

        $.ajax({
            url: "{{ url('gaji-guru') }}/" + id,
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
                showToast('error', 'Error', 'Gagal menghapus data gaji guru');
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
        input.closest('.input-group').find('.invalid-feedback').text(messages[0]);

        // Handle Select2
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
