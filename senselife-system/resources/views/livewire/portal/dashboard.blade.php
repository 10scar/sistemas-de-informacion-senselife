<div class="space-y-6">
    @can('access-medico-portal')
        <div class="rounded-lg border border-neutral-200 bg-neutral-0 p-6 shadow-sm">
            <h2 class="font-display text-lg font-semibold text-text">{{ __('Área médica') }}</h2>
            <p class="mt-2 text-sm text-neutral-600">
                {{ __('Vista para personal médico: pacientes asociados, alertas y telemetría.') }}
            </p>
        </div>
    @endcan

    @can('access-centro-portal')
        <div class="rounded-lg border border-neutral-200 bg-neutral-0 p-6 shadow-sm">
            <h2 class="font-display text-lg font-semibold text-text">{{ __('Área de centro') }}</h2>
            <p class="mt-2 text-sm text-neutral-600">
                {{ __('Vista para operadores del centro: dispositivos, personal y configuración local.') }}
            </p>
        </div>
    @endcan
</div>
