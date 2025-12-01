@extends('layouts.app')

@section('title', 'Level User')

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
.avatar-purple {
    background: linear-gradient(135deg, #654ea3, #eaafc8);
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
.menu-tree {
    border: 1px solid var(--bs-border-color);
    border-radius: 0.75rem;
    padding: 1rem;
    max-height: 260px;
    overflow-y: auto;
}
.menu-tree__node {
    margin-bottom: 0.75rem;
}
.menu-tree__children {
    margin-left: 1.5rem;
    margin-top: 0.5rem;
}
.menu-tree__label {
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Level User</h5>
                    <p class="text-muted mb-0 small">Atur role, default access, dan permission per level.</p>
                </div>
                @if ($canManage)
                    <button type="button" class="btn btn-primary" onclick="tambahLevel()">
                        <i class="bx bx-plus me-1"></i> Level Baru
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped table-modern" id="tableLevel">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Informasi</th>
                                <th>Deskripsi</th>
                                <th>Menu Sidebar</th>
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

<div class="modal fade" id="modalLevel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLevelTitle">Level Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formLevel">
                <div class="modal-body">
                    <input type="hidden" id="level_id" name="level_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Level <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="slug" name="slug" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Tuliskan informasi tambahan level"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mt-4">
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                            <label class="form-label mb-0">Menu Sidebar</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllMenus(true)">Pilih Semua</button>
                                <button type="button" class="btn btn-sm btn-outline-dark" onclick="toggleAllMenus(false)">Bersihkan</button>
                            </div>
                        </div>
                        @if ($menuTree->isNotEmpty())
                            <div class="menu-tree" id="menuTreeContainer">
                                @include('auth.levels.partials.menu-tree', ['items' => $menuTree, 'depth' => 0])
                            </div>
                        @else
                            <p class="text-muted mb-0">Belum ada menu yang dikonfigurasi. Silakan atur melalui halaman menu.</p>
                        @endif
                        <div class="text-danger small mt-2" id="menusError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanLevel" @if (! $canManage) disabled @endif>
                        <span class="spinner-border spinner-border-sm d-none" id="loadingSpinnerLevel"></span>
                        <span id="btnTextLevel">Simpan</span>
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
const levelBaseUrl = "{{ url('autentikasi/level-user') }}";
let levelTable;
const menuTreeData = @json($menuTree->toArray());
const menuParentMap = {};
const menuChildrenMap = {};
buildMenuRelations(menuTreeData);

$(document).ready(function() {
    levelTable = $('#tableLevel').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('auth.levels.index') }}",
            type: 'GET'
        },
        dom: "<'datatable-top d-flex flex-wrap align-items-center justify-content-between mb-3'<'d-flex align-items-center gap-2'l><'datatable-search'f>>" +
             "rt" +
             "<'datatable-bottom d-flex flex-wrap align-items-center justify-content-between'<'text-muted'i><'pagination pagination-sm'p>>",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'info_card', name: 'name', orderable: false, searchable: true },
            { data: 'detail_card', name: 'description', orderable: false, searchable: true },
            { data: 'menu_badges', name: 'role.name', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '12%' }
        ],
        language: {
            processing: 'Memuat data...',
            zeroRecords: 'Data tidak ditemukan'
        }
    });

    $('#formLevel').on('submit', function(e) {
        e.preventDefault();
        simpanLevel();
    });

    $('#name').on('input', function() {
        if (! $('#level_id').val()) {
            $('#slug').val(slugify($(this).val()));
        }
    });

    $(document).on('change', '.menu-checkbox', function() {
        const menuId = parseInt($(this).val(), 10);
        if (Number.isNaN(menuId)) {
            return;
        }

        const isChecked = $(this).is(':checked');
        toggleChildMenus(menuId, isChecked);
        updateParentState(menuId);
    });
});

function slugify(string) {
    return string
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-zA-Z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .toLowerCase();
}

function tambahLevel() {
    $('#modalLevelTitle').text('Level Baru');
    $('#formLevel')[0].reset();
    $('#level_id').val('');
    clearValidation();
    clearMenuSelection();
    $('#modalLevel').modal('show');
}

function editLevel(id) {
    clearValidation();
    showLoading();

    $.ajax({
        url: `${levelBaseUrl}/${id}`,
        type: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const data = response.data;
                const menus = response.menus ?? [];

                $('#modalLevelTitle').text('Edit Level User');
                $('#level_id').val(data.id);
                $('#name').val(data.name);
                $('#slug').val(data.slug);
                $('#description').val(data.description);
                setMenuSelection(menus);
                $('#menusError').text('');
                $('#modalLevel').modal('show');
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal mengambil data level');
        }
    });
}

function simpanLevel() {
    const levelId = $('#level_id').val();
    const url = levelId ? `${levelBaseUrl}/${levelId}` : "{{ route('auth.levels.store') }}";
    const method = levelId ? 'PUT' : 'POST';

    const formData = {
        name: $('#name').val(),
        slug: $('#slug').val(),
        description: $('#description').val(),
        permissions: $('.permission-checkbox:checked').map(function() { return $(this).val(); }).get(),
        menu_ids: $('.menu-checkbox:checked').map(function() { return $(this).val(); }).get()
    };

    clearValidation();
    $('#menusError').text('');
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
                $('#modalLevel').modal('hide');
                levelTable.ajax.reload();
                showToast('success', 'Berhasil', response.message);
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            setBtnLoading(false);
            if (xhr.status === 422) {
                displayValidationErrors(xhr.responseJSON.errors);
                if (xhr.responseJSON.errors?.menu_ids) {
                    $('#menusError').text(xhr.responseJSON.errors.menu_ids[0]);
                }
                showToast('error', 'Validasi Error', 'Periksa kembali form Anda');
            } else {
                showToast('error', 'Error', 'Terjadi kesalahan saat menyimpan data');
            }
        }
    });
}

function hapusLevel(id) {
    if (! confirm('Hapus level ini?')) {
        return;
    }

    showLoading();

    $.ajax({
        url: `${levelBaseUrl}/${id}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                levelTable.ajax.reload();
                showToast('success', 'Berhasil', response.message);
            } else {
                showToast('error', 'Error', response.message);
            }
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Gagal menghapus level');
        }
    });
}

function toggleAllMenus(state) {
    $('.menu-checkbox').prop('checked', state);
    $('#menusError').text('');

    if (!state) {
        return;
    }

    Object.keys(menuParentMap).forEach(id => {
        const menuId = parseInt(id, 10);
        if (!Number.isNaN(menuId)) {
            updateParentState(menuId);
        }
    });
}

function clearMenuSelection() {
    $('.menu-checkbox').prop('checked', false);
    $('#menusError').text('');
}

function setMenuSelection(menuIds) {
    clearMenuSelection();
    (menuIds || []).forEach(id => {
        const checkbox = $(`.menu-checkbox[value="${id}"]`);
        checkbox.prop('checked', true);
        updateParentState(id);
    });
}

function buildMenuRelations(items, parentId = null) {
    (items || []).forEach(item => {
        menuParentMap[item.id] = parentId;
        const children = item.children || [];
        menuChildrenMap[item.id] = children.map(child => child.id);
        buildMenuRelations(children, item.id);
    });
}

function toggleChildMenus(menuId, state) {
    const children = menuChildrenMap[menuId] || [];
    children.forEach(childId => {
        const checkbox = $(`.menu-checkbox[value="${childId}"]`);
        checkbox.prop('checked', state);
        toggleChildMenus(childId, state);
    });
}

function updateParentState(menuId) {
    const parentId = menuParentMap[menuId];
    if (!parentId) {
        return;
    }

    const siblings = menuChildrenMap[parentId] || [];
    if (!siblings.length) {
        updateParentState(parentId);
        return;
    }

    const hasCheckedSibling = siblings.some(id => $(`.menu-checkbox[value="${id}"]`).is(':checked'));
    $(`.menu-checkbox[value="${parentId}"]`).prop('checked', hasCheckedSibling);
    updateParentState(parentId);
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
    $('#menusError').text('');
}

function setBtnLoading(loading) {
    if (loading) {
        $('#loadingSpinnerLevel').removeClass('d-none');
        $('#btnTextLevel').text('Menyimpan...');
        $('#btnSimpanLevel').prop('disabled', true);
    } else {
        $('#loadingSpinnerLevel').addClass('d-none');
        $('#btnTextLevel').text('Simpan');
        $('#btnSimpanLevel').prop('disabled', false);
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

