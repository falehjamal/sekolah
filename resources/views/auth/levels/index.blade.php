@extends('layouts.app')

@section('title', 'Level User')

@section('content')
<div class="row">
  <div class="col-12">
    @if (session('status'))
      <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible" role="alert">
        <ul class="mb-0 ps-3">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">Level User</h5>
          <small class="text-muted">Kelola role, permission menu, dan akses pengguna.</small>
        </div>
        @can('auth.roles.manage')
          <a href="{{ route('auth.levels.create') }}" class="btn btn-primary btn-sm">
            <i class="bx bx-plus"></i>
            Level Baru
          </a>
        @endcan
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Nama Level</th>
              <th>Slug</th>
              <th>Default</th>
              <th>Hak Akses Menu</th>
              <th>Jumlah Pengguna</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($levels as $level)
              @php($role = $roles[$level->slug] ?? null)
              <tr>
                <td class="fw-semibold">{{ $level->name }}</td>
                <td><code>{{ $level->slug }}</code></td>
                <td>
                  @if ($level->is_default)
                    <span class="badge bg-label-success">Default</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if ($role && $role->permissions->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1">
                      @foreach ($role->permissions as $permission)
                        <span class="badge bg-label-primary">{{ \Illuminate\Support\Str::headline($permission->name) }}</span>
                      @endforeach
                    </div>
                  @else
                    <span class="text-muted">Belum ada permission</span>
                  @endif
                </td>
                <td>{{ $level->users_count }}</td>
                <td class="text-end">
                  @can('auth.roles.manage')
                    <div class="btn-group btn-group-sm" role="group">
                      <a href="{{ route('auth.levels.edit', $level) }}" class="btn btn-outline-secondary">
                        <i class="bx bx-edit-alt"></i>
                      </a>
                      <form action="{{ route('auth.levels.destroy', $level) }}" method="POST" onsubmit="return confirm('Hapus level {{ $level->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                          <i class="bx bx-trash"></i>
                        </button>
                      </form>
                    </div>
                  @else
                    <span class="text-muted small">Tidak ada akses</span>
                  @endcan
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">Belum ada data level.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($levels->hasPages())
        <div class="card-footer">
          {{ $levels->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

