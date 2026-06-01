<?php

use App\Http\Controllers\Auth\PortalAuthenticatedSessionController;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\Pacientes\Index as PacientesIndex;
use App\Livewire\Portal\Pacientes\Show as PacientesShow;
use App\Livewire\Portal\Personal\Index as PersonalIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [PortalAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [PortalAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'can:access-portal-panel'])->group(function (): void {
        Route::post('logout', [PortalAuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::get('pacientes', PacientesIndex::class)->name('pacientes.index');
        Route::get('pacientes/{paciente}', PacientesShow::class)->name('pacientes.show');
        Route::get('personal', PersonalIndex::class)
            ->middleware('can:access-centro-portal')
            ->name('personal.index');
    });
});
