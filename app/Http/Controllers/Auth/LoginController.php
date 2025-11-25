<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TenantLoginRequest;
use App\Models\Tenant\UserAccount;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected string $redirectTo = '/';

    public function __construct(
        protected TenantResolver $tenantResolver,
        protected TenantConnectionManager $tenantConnection
    ) {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username(): string
    {
        return 'user';
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(TenantLoginRequest $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function attemptLogin(Request $request): bool
    {
        try {
            $tenant = $this->tenantResolver->resolveActive($request->input('idsekolah'));
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages([
                'idsekolah' => ['Tenant tidak ditemukan atau nonaktif.'],
            ]);
        }

        $this->tenantConnection->rememberTenant($tenant);

        try {
            $this->tenantConnection->connectFromSession();
        } catch (\Throwable) {
            $this->tenantConnection->disconnect();

            throw ValidationException::withMessages([
                'idsekolah' => ['Konfigurasi koneksi tenant tidak valid.'],
            ]);
        }

        $user = UserAccount::forTenant($tenant->getKey())
            ->where('username', $request->input('user'))
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            $this->tenantConnection->disconnect();

            return false;
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $this->guard()->login($user, $request->boolean('remember'));

        return true;
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'user' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $this->tenantConnection->disconnect();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
