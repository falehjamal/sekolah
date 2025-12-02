<?php

use App\Http\Requests\Tenant\StoreGuruRequest;
use App\Http\Requests\Tenant\UpdateGuruRequest;
use App\Models\Tenant\Guru;
use App\Support\TenantContext;

beforeEach(function (): void {
    TenantContext::set(1);
});

afterEach(function (): void {
    TenantContext::forget();
});

it('menerapkan aturan user_id dan nip saat menyimpan guru', function (): void {
    $request = new StoreGuruRequest;

    $rules = $request->rules();

    expect($rules)
        ->toHaveKey('user_id')
        ->and($rules['user_id'])->toContain('exists:sekolah_tenant.user_1,id')
        ->and($rules['user_id'])->toContain('unique:sekolah_tenant.guru_1,user_id')
        ->and($rules)
        ->toHaveKey('nip')
        ->and($rules['nip'])->toContain('unique:sekolah_tenant.guru_1,nip');
});

it('mengabaikan guru aktif dalam aturan unik saat update', function (): void {
    $request = new UpdateGuruRequest;

    $guru = new Guru;
    $guru->id = 42;

    $request->setRouteResolver(function () use ($guru) {
        return new class($guru)
        {
            public function __construct(public Guru $guru) {}

            public function parameter(string $key): Guru
            {
                return $this->guru;
            }
        };
    });

    $rules = $request->rules();

    expect($rules)
        ->toHaveKey('user_id')
        ->and($rules['user_id'])->toContain('unique:sekolah_tenant.guru_1,user_id,42')
        ->and($rules)
        ->toHaveKey('nip')
        ->and($rules['nip'])->toContain('unique:sekolah_tenant.guru_1,nip,42');
});
