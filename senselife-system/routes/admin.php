<?php

use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Dispositivos\Index as DispositivosIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'can:access-admin-panel'])->group(function (): void {
        Route::post('logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::get('dispositivos', DispositivosIndex::class)->name('dispositivos.index');
    });
});
