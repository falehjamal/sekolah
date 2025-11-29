@csrf
@if ($method !== 'POST')
  @method($method)
@endif

<div class="row">
  <div class="col-md-6">
    <div class="mb-3">
      <label for="name" class="form-label">Nama Level</label>
      <input
        type="text"
        class="form-control @error('name') is-invalid @enderror"
        id="name"
        name="name"
        value="{{ old('name', $level->name ?? '') }}"
        placeholder="Contoh: Administrator"
        required
      />
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-6">
    <div class="mb-3">
      <label for="slug" class="form-label">Slug</label>
      <input
        type="text"
        class="form-control @error('slug') is-invalid @enderror"
        id="slug"
        name="slug"
        value="{{ old('slug', $level->slug ?? '') }}"
        placeholder="administrator"
      />
      <small class="text-muted">Slug digunakan sebagai nama role di sistem permission.</small>
      @error('slug')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="mb-3">
  <label for="description" class="form-label">Deskripsi</label>
  <textarea
    id="description"
    class="form-control @error('description') is-invalid @enderror"
    name="description"
    rows="3"
    placeholder="Tuliskan detail tugas level ini">{{ old('description', $level->description ?? '') }}</textarea>
  @error('description')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <h6 class="fw-semibold mb-2">Hak Akses Menu</h6>
  <p class="text-muted mb-3">Centang menu yang boleh diakses oleh level ini.</p>
  @php($currentPermissions = collect(old('permissions', $selectedPermissions ?? [])))
  @if ($permissionGroups->isEmpty())
    <div class="alert alert-warning mb-0">Belum ada data permission. Seed data terlebih dahulu sebelum melanjutkan.</div>
  @else
    <div class="row">
      @foreach ($permissionGroups as $group => $permissions)
        <div class="col-md-6 mb-3">
          <div class="border rounded p-3 h-100">
            <p class="fw-semibold text-uppercase mb-3 small text-muted">{{ $group }}</p>
            @foreach ($permissions as $permission)
              @php($permissionId = 'permission-' . \Illuminate\Support\Str::slug($permission->name))
              <div class="form-check mb-2">
                <input
                  class="form-check-input"
                  type="checkbox"
                  name="permissions[]"
                  id="{{ $permissionId }}"
                  value="{{ $permission->name }}"
                  {{ $currentPermissions->contains($permission->name) ? 'checked' : '' }}
                />
                <label class="form-check-label" for="{{ $permissionId }}">
                  {{ \Illuminate\Support\Str::headline($permission->name) }}
                </label>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  @endif
  @error('permissions')
    <div class="text-danger small">{{ $message }}</div>
  @enderror
</div>

<div class="d-flex justify-content-between">
  <a href="{{ route('auth.levels.index') }}" class="btn btn-light">Batal</a>
  <button type="submit" class="btn btn-primary">
    <i class="bx bx-save"></i>
    Simpan
  </button>
</div>

