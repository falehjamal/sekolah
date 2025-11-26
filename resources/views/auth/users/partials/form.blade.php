@php($model = $user ?? new \App\Models\Tenant\UserAccount)

@csrf
@if ($method !== 'POST')
  @method($method)
@endif

<div class="row">
  <div class="col-md-6">
    <div class="mb-3">
      <label for="name" class="form-label">Nama Lengkap</label>
      <input
        type="text"
        class="form-control @error('name') is-invalid @enderror"
        id="name"
        name="name"
        value="{{ old('name', $model->name ?? '') }}"
        required
      />
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-6">
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input
        type="text"
        class="form-control @error('username') is-invalid @enderror"
        id="username"
        name="username"
        value="{{ old('username', $model->username ?? '') }}"
        required
      />
      @error('username')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input
        type="email"
        class="form-control @error('email') is-invalid @enderror"
        id="email"
        name="email"
        value="{{ old('email', $model->email ?? '') }}"
      />
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-6">
    <div class="mb-3">
      <label for="level_id" class="form-label">Level User</label>
      <select
        id="level_id"
        name="level_id"
        class="form-select @error('level_id') is-invalid @enderror"
        required
      >
        <option value="">Pilih level</option>
        @foreach ($levels as $level)
          <option value="{{ $level->id }}" @selected(old('level_id', $model->level_id ?? '') == $level->id)>
            {{ $level->name }}
          </option>
        @endforeach
      </select>
      @error('level_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input
        type="password"
        class="form-control @error('password') is-invalid @enderror"
        id="password"
        name="password"
        @if (! $isEdit) required @endif
      />
      @if ($isEdit)
        <small class="text-muted">Kosongkan jika tidak ingin mengganti password.</small>
      @endif
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-6">
    <div class="mb-3">
      <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
      <input
        type="password"
        class="form-control"
        id="password_confirmation"
        name="password_confirmation"
        @if (! $isEdit) required @endif
      />
    </div>
  </div>
</div>

<div class="form-check form-switch mb-4">
  <input
    type="checkbox"
    class="form-check-input"
    id="is_active"
    name="is_active"
    value="1"
    {{ old('is_active', $model->is_active ?? true) ? 'checked' : '' }}
  />
  <label class="form-check-label" for="is_active">Aktifkan akun ini</label>
</div>

<div class="d-flex justify-content-between">
  <a href="{{ route('auth.users.index') }}" class="btn btn-light">Batal</a>
  <button type="submit" class="btn btn-primary">
    <i class="bx bx-save"></i>
    Simpan
  </button>
</div>

