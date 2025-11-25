@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p class="mb-2">Anda berhasil masuk sebagai <strong>{{ auth()->user()->name }}</strong>.</p>
                    <p class="mb-0">
                        Tenant aktif:
                        <strong>{{ tenant()?->name ?? session('tenant_id') }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
