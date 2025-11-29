@extends('layouts.app')

@section('title', 'Manajemen Menu')

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
    justify-content:center;
    font-size: 1.05rem;
    flex-shrink: 0;
}
.avatar-teal {
    background: linear-gradient(135deg, #0ba360, #3cba92);
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
    min-width: 100px;
    font-weight: 600;
    color: #8c8fa5;
    text-transform: uppercase;
    font-size: 0.72rem;
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
                    <h5 class="mb-1">Manajemen Menu</h5>
                    <p class="text-muted mb-0 small">Atur struktur menu, permission, dan akses role.</p>
                </div>
                @if ($canManage)
                    <button type="button" class="btn btn-primary" onclick="tambahMenu()">
                        <i class="bx bx-plus me-1"></i> Menu Baru
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tableMenu">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Menu</th>
                                <th>Detail</th>
                                <th>Permission & Role</th>
                                <th width="12%">Status</th>
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

<div class="modal fade" id="modalMenu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMenuTitle">Menu Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMenu">
                <div class="modal-body">
                    <input type="hidden" id="menu_id" name="menu_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="parent_id" class="form-label">Parent Menu</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">-- Menu Utama --</option>
                                @foreach ($menuOptions as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="route_name" class="form-label">Route Name</label>
                            <input type="text" class="form-control" id="route_name" name="route_name" placeholder="contoh: auth.users.index">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">Icon (Class)</label>
                            <input type="text" class="form-control" id="icon" name="icon" placeholder="contoh: bx bx-user">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sort_order" class="form-label">Urutan</label>
                            <input type="number" min="0" class="form-control" id="sort_order" name="sort_order" value="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guard_name" class="form-label">Guard</label>
                            <input type="text" class="form-control" id="guard_name" name="guard_name" value="web">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="permission_name" class="form-label">Permission</label>
                            <select class="form-select" id="permission_name" name="permission_name">
                                <option value="">-- Opsional --</option>
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="role_ids" class="form-label">Role Akses</label>
                        <select class="form-select" id="role_ids" name="role_ids[]" multiple size="4">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Kosongkan bila menu tidak dibatasi role.</small>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanMenu" @if (! $canManage) disabled @endif>
                        <span class="spinner-border spinner-border-sm d-none" id="loadingSpinnerMenu"></span>
                        <span id="btnTextMenu">Simpan</span>
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
const menuBaseUrl = "{{ url('autentikasi/menu') }}";
let menuTable;

$(document).ready(function() {
    menuTable = $('#tableMenu').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('auth.menus.index') }}",
            type: 'GET'
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'info_card', name: 'name', orderable: false, searchable: true },
            { data: 'detail_card', name: 'parent_id', orderable: false, searchable: false },
            { data: 'permission_card', name: 'permission_name', orderable: false, searchable: false },
            { data: 'status_badge', name: 'is_active', orderable: false, searchable: false, width: '12%' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '12%' }
        ],
        language: {
            processing: 'Memuat data...',
            zeroRecords: 'Data tidak ditemukan'
        }
    });

    $('#formMenu').on('submit', function(e) {
        e.preventDefault();
        simpanMenu();
    });
});

function tambahMenu() {
    $('#modalMenuTitle').text('Menu Baru');
    $('#formMenu')[0].reset();
    $('#menu_id').val('');
    $('#role_ids option').prop('selected', false);
    clearValidation();
    $('#modalMenu').modal('show');
}

function editMenu(id) {
    clearValidation();
    showLoading();

    $.ajax({
        url: `${menuBaseUrl}/${id}`,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                const roleIds = response.role_ids ?? [];

                $('#modalMenuTitle').text('Edit Menu');
                $('#menu_id').val(data.id);
                $('#name').val(data.name);
                $('#parent_id').val(data.parent_id);
                $('#route_name').val(data.route_name);
                $('#icon').val(data.icon);
                $('#sort_order').val(data.sort_order);
                $('#permission_name').val(data.permission_name);
                $('#guard_name').val(data.guard_name ?? 'web');
                $('#is_active').prop('checked', data.is_active);
                $('#role_ids option').prop('selected', false);
                roleIds.forEach(id => {
                    $(`#role_ids option[value="${id}"]`).prop('selected', true);
                });
                $('#modalMenu').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data menu');
        }
    });
}

function simpanMenu() {
    const menuId = $('#menu_id').val();
    const url = menuId ? `${menuBaseUrl}/${menuId}` : "{{ route('auth.menus.store') }}";
    const method = menuId ? 'PUT' : 'POST';

    const formData = {
        name: $('#name').val(),
        parent_id: $('#parent_id').val(),
        route_name: $('#route_name').val(),
        icon: $('#icon').val(),
        sort_order: $('#sort_order').val(),
        guard_name: $('#guard_name').val(),
        permission_name: $('#permission_name').val(),
        role_ids: $('#role_ids').val(),
        is_active: $('#is_active').is(':checked') ? 1 : 0
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
                $('#modalMenu').modal('hide');
                menuTable.ajax.reload();
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

function hapusMenu(id) {
    if (! confirm('Hapus menu ini?')) {
        return;
    }

    showLoading();

    $.ajax({
        url: `${menuBaseUrl}/${id}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                menuTable.ajax.reload();
                showToast('success', 'Berhasil', response.message);
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal menghapus menu');
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
        if (input.length) {
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]);
        }
    });
}

function clearValidation() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

function setBtnLoading(loading) {
    if (loading) {
        $('#loadingSpinnerMenu').removeClass('d-none');
        $('#btnTextMenu').text('Menyimpan...');
        $('#btnSimpanMenu').prop('disabled', true);
    } else {
        $('#loadingSpinnerMenu').addClass('d-none');
        $('#btnTextMenu').text('Simpan');
        $('#btnSimpanMenu').prop('disabled', false);
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

