@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')
<div class="row">
  <div class="col-lg-9 mx-auto">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Ubah Pengguna: {{ $user->name }}</h5>
        <small class="text-muted">Perbarui informasi akun dan level akses.</small>
      </div>
      <div class="card-body">
        <form action="{{ route('auth.users.update', $user) }}" method="POST">
          @include('auth.users.partials.form', [
              'method' => 'PUT',
              'user' => $user,
              'levels' => $levels,
              'isEdit' => true,
          ])
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

