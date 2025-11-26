@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="row">
  <div class="col-12">
    @if (session('status'))
      <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card">
      <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">Daftar Pengguna</h5>
          <small class="text-muted">Kelola akun portal admin tiap tenant.</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <form action="{{ route('auth.users.index') }}" method="GET" class="d-flex">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bx bx-search"></i></span>
              <input
                type="search"
                class="form-control"
                placeholder="Cari nama, username, atau email"
                name="search"
                value="{{ $search }}"
              />
            </div>
          </form>
          @can('auth.users.manage')
            <a href="{{ route('auth.users.create') }}" class="btn btn-primary btn-sm">
              <i class="bx bx-plus"></i> Pengguna Baru
            </a>
          @endcan
        </div>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Username</th>
              <th>Email</th>
              <th>Level</th>
              <th>Role</th>
              <th>Status</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($users as $user)
              <tr>
                <td class="fw-semibold">{{ $user->name }}</td>
                <td><code>{{ $user->username }}</code></td>
                <td>{{ $user->email ?? '-' }}</td>
                <td>{{ $user->level?->name ?? '-' }}</td>
                <td>
                  @php($roles = $user->getRoleNames())
                  @if ($roles->isEmpty())
                    <span class="text-muted">-</span>
                  @else
                    <div class="d-flex flex-wrap gap-1">
                      @foreach ($roles as $role)
                        <span class="badge bg-label-info text-capitalize">{{ $role }}</span>
                      @endforeach
                    </div>
                  @endif
                </td>
                <td>
                  @if ($user->is_active)
                    <span class="badge bg-label-success">Aktif</span>
                  @else
                    <span class="badge bg-label-secondary">Nonaktif</span>
                  @endif
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    @can('auth.users.manage')
                      <a href="{{ route('auth.users.edit', $user) }}" class="btn btn-outline-secondary">
                        <i class="bx bx-edit-alt"></i>
                      </a>
                      <form action="{{ route('auth.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna {{ $user->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                          <i class="bx bx-trash"></i>
                        </button>
                      </form>
                    @else
                      <span class="text-muted small">Tidak ada akses</span>
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Belum ada pengguna.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($users->hasPages())
        <div class="card-footer">
          {{ $users->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

