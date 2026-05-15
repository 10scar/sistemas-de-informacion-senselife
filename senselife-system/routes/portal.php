<?php

use App\Livewire\Portal\Dashboard;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')
    ->name('portal.')
    ->middleware(['auth', 'can:access-portal-panel'])
    ->group(function (): void {
        Route::get('/', Dashboard::class)->name('dashboard');
    });
