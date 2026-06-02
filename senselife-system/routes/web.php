<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\Usuario\User;
use App\Support\RolNombre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing', \App\Support\LandingMonitorDemo::data());
})->name('home');

Route::get('dashboard', function (): RedirectResponse {
    /** @var User $user */
    $user = auth()->user();

    return match ($user->rol?->nombre) {
        RolNombre::ADMINISTRADOR => redirect()->route('admin.dashboard'),
        RolNombre::MEDICO, RolNombre::OPERADOR => redirect()->route('portal.dashboard'),
        default => redirect()->route('home'),
    };
})->middleware('auth')->name('dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/livewire-demo', function () {
    return view('livewire-demo');
});
