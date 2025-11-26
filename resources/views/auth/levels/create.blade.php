@extends('layouts.app')

@section('title', 'Level User Baru')

@section('content')
<div class="row">
  <div class="col-lg-10 mx-auto">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Tambah Level User</h5>
        <small class="text-muted">Definisikan level baru beserta hak akses menu.</small>
      </div>
      <div class="card-body">
        <form action="{{ route('auth.levels.store') }}" method="POST">
          @include('auth.levels.partials.form', [
              'method' => 'POST',
              'level' => null,
              'permissionGroups' => $permissionGroups,
              'selectedPermissions' => [],
          ])
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

