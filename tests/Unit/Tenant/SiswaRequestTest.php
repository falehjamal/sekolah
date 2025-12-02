<?php

use App\Http\Requests\Tenant\StoreSiswaRequest;
use App\Http\Requests\Tenant\UpdateSiswaRequest;
use App\Models\Tenant\Siswa;
use App\Support\TenantContext;

beforeEach(function (): void {
    TenantContext::set(1);
});

afterEach(function (): void {
    TenantContext::forget();
});

it('menerapkan aturan relasi user saat menambahkan siswa', function (): void {
    $request = new StoreSiswaRequest;

    $rules = $request->rules();

    expect($rules)
        ->toHaveKey('user_id')
        ->and($rules['user_id'])->toContain('exists:sekolah_tenant.user_1,id')
        ->and($rules['user_id'])->toContain('unique:sekolah_tenant.siswa_1,user_id');
});

it('mengabaikan siswa aktif ketika memvalidasi user_id saat update', function (): void {
    $request = new UpdateSiswaRequest;

    $siswa = new Siswa;
    $siswa->id = 42;

    $request->setRouteResolver(function () use ($siswa) {
        return new class($siswa)
        {
            public function __construct(public Siswa $siswa) {}

            public function parameter(string $key): Siswa
            {
                return $this->siswa;
            }
        };
    });

    $rules = $request->rules();

    expect($rules)
        ->toHaveKey('user_id')
        ->and($rules['user_id'])->toContain('unique:sekolah_tenant.siswa_1,user_id,42');
});
