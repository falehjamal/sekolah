@extends('layouts.app')

@section('title', 'Tagihan Manual')

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
                $disableForm = $siswaList->isEmpty();
            @endphp
            <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Tagihan Manual</h5>
                    <p class="text-muted mb-0 small">Kelola tagihan SPP manual untuk setiap siswa.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="tambahData()" @if ($disableForm) disabled @endif>
                    <i class="bx bx-plus me-1"></i> Tambah Tagihan
                </button>
            </div>
            <div class="card-body">
                @if ($disableForm)
                    <div class="alert alert-warning" role="alert">
                        <strong>Perlu data pendukung:</strong>
                        <ul class="mb-0 ps-3">
                            <li>Belum ada data siswa aktif. Tambahkan melalui menu Siswa.</li>
                        </ul>
                    </div>
                @endif
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tableTagihan">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Siswa</th>
                                <th>Detail Tagihan</th>
                                <th>Metode</th>
                                <th>Keterangan</th>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormTitle">Tambah Tagihan Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTagihan">
                <div class="modal-body">
                    <input type="hidden" id="tagihan_id" name="tagihan_id">

                    <!-- Info SPP Panel -->
                    <div class="alert alert-info d-none" id="sppInfoPanel">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bx bx-info-circle me-2 fs-5"></i>
                            <strong>Informasi SPP</strong>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Total SPP</small>
                                <span class="fw-semibold" id="infoNominalSpp">-</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Sudah Dibayar</small>
                                <span class="fw-semibold text-success" id="infoTotalDibayar">-</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Sisa Kekurangan</small>
                                <span class="fw-semibold text-warning" id="infoSisaKekurangan">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Lunas Panel -->
                    <div class="alert alert-danger d-none" id="lunasWarningPanel">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-error-circle me-2 fs-5"></i>
                            <div>
                                <strong>Tagihan Sudah Lunas!</strong>
                                <p class="mb-0 small" id="lunasWarningMessage">SPP untuk bulan ini sudah lunas. Tidak dapat menambah pembayaran lagi.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="siswa_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="siswa_id" name="siswa_id" data-placeholder="Pilih Siswa" required>
                            <option value="">Pilih Siswa</option>
                            @foreach ($siswaList as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->nis }} - {{ $siswa->nama }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bulan" class="form-label">Bulan <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="bulan" name="bulan" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nominal" class="form-label">Nominal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="nominal" name="nominal" placeholder="0" min="0" required>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted d-none" id="nominalHint">Maksimal: <span id="maxNominal">-</span></small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_bayar" class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_bayar" name="tanggal_bayar" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="metode_bayar" class="form-label">Metode Bayar</label>
                            <select class="form-select" id="metode_bayar" name="metode_bayar">
                                <option value="">Pilih Metode</option>
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                                <option value="debit">Debit</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan (opsional)"></textarea>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let table;
let isEditMode = false;
let sppData = null;

$(document).ready(function() {
    $('#siswa_id').select2({
        dropdownParent: $('#modalForm'),
        theme: 'bootstrap4',
        placeholder: 'Pilih Siswa',
        width: '100%',
        allowClear: true
    });

    // Initialize DataTable
    table = $('#tableTagihan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('tagihan-manual.index') }}",
            type: 'GET'
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        responsive: true,
        autoWidth: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'siswa_info', name: 'siswa.nama', orderable: false, searchable: true },
            { data: 'tagihan_info', name: 'bulan', orderable: false, searchable: true },
            { data: 'metode_badge', name: 'metode_bayar', orderable: false, searchable: false },
            { data: 'keterangan_text', name: 'keterangan', orderable: false, searchable: true },
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
        order: [[2, 'desc']]
    });

    // Form Submit Handler
    $('#formTagihan').on('submit', function(e) {
        e.preventDefault();
        simpanData();
    });

    // Trigger SPP info fetch when siswa or bulan changes
    $('#siswa_id').on('change', function() {
        fetchSppInfo();
    });

    $('#bulan').on('change', function() {
        fetchSppInfo();
    });
});

function fetchSppInfo() {
    const siswaId = $('#siswa_id').val();
    const bulan = $('#bulan').val();
    const tagihanId = $('#tagihan_id').val();

    // Reset panels
    hideSppInfoPanels();
    sppData = null;

    if (!siswaId || !bulan) {
        return;
    }

    $.ajax({
        url: "{{ route('tagihan-manual.spp-info') }}",
        type: 'GET',
        data: {
            siswa_id: siswaId,
            bulan: bulan,
            tagihan_id: tagihanId
        },
        success: function(response) {
            if (response.success) {
                sppData = response.data;
                displaySppInfo(sppData);
            } else {
                showToast('warning', 'Peringatan', response.message);
            }
        },
        error: function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                showToast('warning', 'Peringatan', xhr.responseJSON.message);
            }
        }
    });
}

function displaySppInfo(data) {
    if (data.is_lunas) {
        // Tampilkan warning lunas
        $('#lunasWarningPanel').removeClass('d-none');
        $('#sppInfoPanel').addClass('d-none');
        $('#btnSimpan').prop('disabled', true);
        $('#nominalHint').addClass('d-none');
    } else {
        // Tampilkan info SPP
        $('#sppInfoPanel').removeClass('d-none');
        $('#lunasWarningPanel').addClass('d-none');
        $('#btnSimpan').prop('disabled', false);

        $('#infoNominalSpp').text(data.nominal_spp_format);
        $('#infoTotalDibayar').text(data.total_dibayar_format);
        $('#infoSisaKekurangan').text(data.sisa_kekurangan_format);

        // Update hint nominal
        $('#maxNominal').text(data.sisa_kekurangan_format);
        $('#nominalHint').removeClass('d-none');

        // Set max value untuk input nominal
        $('#nominal').attr('max', data.sisa_kekurangan);
    }
}

function hideSppInfoPanels() {
    $('#sppInfoPanel').addClass('d-none');
    $('#lunasWarningPanel').addClass('d-none');
    $('#nominalHint').addClass('d-none');
    $('#btnSimpan').prop('disabled', false);
    $('#nominal').removeAttr('max');
}

function tambahData() {
    isEditMode = false;
    $('#modalFormTitle').text('Tambah Tagihan Manual');
    $('#formTagihan')[0].reset();
    $('#tagihan_id').val('');
    $('#siswa_id').val('').trigger('change');

    // Set default tanggal bayar ke hari ini
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_bayar').val(today);

    // Set default bulan ke bulan ini
    const currentMonth = new Date().toISOString().slice(0, 7);
    $('#bulan').val(currentMonth);

    clearValidation();
    hideSppInfoPanels();
    $('#modalForm').modal('show');
}

function editData(id) {
    isEditMode = true;
    $('#modalFormTitle').text('Edit Tagihan Manual');
    clearValidation();
    hideSppInfoPanels();

    showLoading();

    $.ajax({
        url: "{{ url('tagihan-manual') }}/" + id,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                $('#tagihan_id').val(data.id);
                $('#bulan').val(data.bulan);
                $('#nominal').val(data.nominal);
                $('#tanggal_bayar').val(data.tanggal_bayar);
                $('#metode_bayar').val(data.metode_bayar);
                $('#keterangan').val(data.keterangan);

                // Set siswa_id last to trigger fetchSppInfo with correct tagihan_id
                $('#siswa_id').val(data.siswa_id ?? '').trigger('change');

                $('#modalForm').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data tagihan');
        }
    });
}

function simpanData() {
    const tagihanId = $('#tagihan_id').val();
    const url = tagihanId ? "{{ url('tagihan-manual') }}/" + tagihanId : "{{ route('tagihan-manual.store') }}";
    const method = tagihanId ? 'PUT' : 'POST';
    const nominal = parseFloat($('#nominal').val()) || 0;

    // Validasi client-side sebelum submit
    if (sppData) {
        if (sppData.is_lunas) {
            showToast('error', 'Gagal', 'SPP untuk bulan ini sudah lunas');
            return;
        }

        if (nominal > sppData.sisa_kekurangan) {
            showToast('error', 'Gagal', 'Nominal pembayaran melebihi sisa kekurangan (Rp ' + sppData.sisa_kekurangan.toLocaleString('id-ID') + ')');
            return;
        }

        if (nominal <= 0) {
            showToast('error', 'Gagal', 'Nominal pembayaran harus lebih dari 0');
            return;
        }
    }

    const formData = {
        siswa_id: $('#siswa_id').val(),
        bulan: $('#bulan').val(),
        nominal: $('#nominal').val(),
        tanggal_bayar: $('#tanggal_bayar').val(),
        metode_bayar: $('#metode_bayar').val(),
        keterangan: $('#keterangan').val()
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
            url: "{{ url('tagihan-manual') }}/" + id,
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
                showToast('error', 'Error', 'Gagal menghapus data tagihan');
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
