<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario\User;
use App\Support\RolNombre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        if ($user->rol?->nombre === RolNombre::ADMINISTRADOR) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('auth.use_admin_login', ['url' => url('/admin/login')]),
            ]);
        }

        return redirect()->intended($this->dashboardUrlForUser($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $toAdminLogin = $request->user()?->rol?->nombre === RolNombre::ADMINISTRADOR;

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($toAdminLogin ? 'admin.login' : 'login');
    }

    private function dashboardUrlForUser(User $user): string
    {
        return match ($user->rol?->nombre) {
            RolNombre::ADMINISTRADOR => route('admin.dashboard'),
            RolNombre::MEDICO, RolNombre::OPERADOR => route('portal.dashboard'),
            default => route('home'),
        };
    }
}
