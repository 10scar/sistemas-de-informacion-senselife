<?php

use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\DispositivoContextoController;
use App\Http\Middleware\VerifyInternalApiToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(VerifyInternalApiToken::class)
    ->group(function (): void {
        Route::post('alertas', [AlertaController::class, 'store']);
        Route::get('dispositivos/{dispositivo}/contexto', [DispositivoContextoController::class, 'show']);
    });
