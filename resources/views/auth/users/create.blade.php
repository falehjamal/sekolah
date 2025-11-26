@extends('layouts.app')

@section('title', 'Pengguna Baru')

@section('content')
<div class="row">
  <div class="col-lg-9 mx-auto">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Tambah Pengguna</h5>
        <small class="text-muted">Masukkan data pengguna dan tentukan levelnya.</small>
      </div>
      <div class="card-body">
        <form action="{{ route('auth.users.store') }}" method="POST">
          @include('auth.users.partials.form', [
              'method' => 'POST',
              'user' => null,
              'levels' => $levels,
              'isEdit' => false,
          ])
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

