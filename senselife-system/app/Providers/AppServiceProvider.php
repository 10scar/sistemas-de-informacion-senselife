<?php

namespace App\Providers;

use App\Models\Usuario\User;
use App\Support\RolNombre;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin-panel', function (?User $user): bool {
            return $user !== null && $user->rol?->nombre === RolNombre::ADMINISTRADOR;
        });

        Gate::define('access-portal-panel', function (?User $user): bool {
            if ($user === null) {
                return false;
            }

            return in_array($user->rol?->nombre, [RolNombre::MEDICO, RolNombre::OPERADOR], true);
        });

        Gate::define('access-medico-portal', function (?User $user): bool {
            return $user !== null && $user->rol?->nombre === RolNombre::MEDICO;
        });

        Gate::define('access-centro-portal', function (?User $user): bool {
            return $user !== null && $user->rol?->nombre === RolNombre::OPERADOR;
        });
    }
}
