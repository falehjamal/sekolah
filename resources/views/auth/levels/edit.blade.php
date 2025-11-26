@extends('layouts.app')

@section('title', 'Ubah Level User')

@section('content')
<div class="row">
  <div class="col-lg-10 mx-auto">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Ubah Level: {{ $level->name }}</h5>
        <small class="text-muted">Sesuaikan informasi level dan hak aksesnya.</small>
      </div>
      <div class="card-body">
        <form action="{{ route('auth.levels.update', $level) }}" method="POST">
          @include('auth.levels.partials.form', [
              'method' => 'PUT',
              'level' => $level,
              'permissionGroups' => $permissionGroups,
              'selectedPermissions' => $selectedPermissions ?? [],
          ])
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

