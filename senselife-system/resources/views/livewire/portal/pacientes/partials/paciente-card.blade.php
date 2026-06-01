@php
    use App\Enums\PacienteEstadoMonitoreo;
    use App\Enums\Sexo;

    $estado = $paciente->estadoMonitoreoVisual();

    $borderClass = match ($estado) {
        PacienteEstadoMonitoreo::Critico => 'bg-error',
        PacienteEstadoMonitoreo::Alerta => 'bg-warning',
        default => 'bg-success',
    };

    $avatarBgClass = match ($estado) {
        PacienteEstadoMonitoreo::Critico => 'bg-error-light border-error-mid',
        PacienteEstadoMonitoreo::Alerta => 'bg-warning-light border-warning-mid',
        default => 'bg-success-light border-success-mid',
    };

    $fcClass = match ($estado) {
        PacienteEstadoMonitoreo::Critico, PacienteEstadoMonitoreo::Alerta => 'text-error',
        default => 'text-text',
    };

    $asociacion = $paciente->asociacionActiva;
    $dispositivo = $asociacion?->dispositivo;
    $ubicacion = $dispositivo?->ubicacion;
    $serie = $dispositivo?->numero_serie;

    $sexoLabel = $paciente->sexo === Sexo::F
        ? __('portal/pacientes.sex_f')
        : __('portal/pacientes.sex_m');
@endphp

<div
    class="flex overflow-hidden rounded-xl border border-neutral-200 bg-neutral-0 shadow-elev-control transition-shadow hover:shadow-elev-card">
    <div class="w-1.5 shrink-0 self-stretch {{ $borderClass }}"></div>

    <div class="flex min-w-0 flex-1 items-center gap-4 px-4 py-3.5">
        <div
            class="flex size-11 shrink-0 items-center justify-center rounded-full border text-sm font-bold text-text {{ $avatarBgClass }}">
            {{ $paciente->iniciales }}
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="truncate text-base font-semibold leading-tight text-text sm:text-lg">
                {{ $paciente->nombre_completo }}
            </h3>
            <p class="mt-0.5 truncate text-sm text-neutral-600">
                {{ $sexoLabel }}
                <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                {{ number_format((float) $paciente->peso, 1) }} kg
                <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                {{ number_format((float) $paciente->altura, 0) }} cm
                @if ($paciente->edad_dias !== null)
                    <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                    {{ trans_choice('portal/pacientes.age_days', $paciente->edad_dias, ['count' => $paciente->edad_dias]) }}
                @endif
            </p>
            <p class="mt-0.5 truncate text-[13px] font-medium text-neutral-500">
                @if ($ubicacion && $serie)
                    {{ $ubicacion }}
                    <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                    {{ $serie }}
                @elseif ($ubicacion || $serie)
                    {{ $ubicacion ?? $serie }}
                @else
                    {{ __('portal/pacientes.unassigned_location') }}
                @endif
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-6 sm:gap-8">
            <div class="flex items-center gap-5 text-center sm:gap-6">
                <div class="w-12 shrink-0">
                    <div class="text-2xl font-bold leading-none tracking-tight {{ $fcClass }}">
                        {{ __('portal/pacientes.vital_placeholder') }}
                    </div>
                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        {{ __('portal/pacientes.fc_label') }}
                    </div>
                </div>
                <div class="w-12 shrink-0">
                    <div class="text-2xl font-bold leading-none tracking-tight text-text">
                        {{ __('portal/pacientes.vital_placeholder') }}
                    </div>
                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                        {{ __('portal/pacientes.fr_label') }}
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                <a
                    href="{{ route('portal.pacientes.show', $paciente) }}"
                    title="{{ __('portal/pacientes.view_details') }}"
                    class="flex size-9 items-center justify-center rounded-full border border-neutral-300 bg-neutral-0 text-neutral-600 shadow-elev-control transition hover:border-primary-500 hover:text-primary-600"
                    aria-label="{{ __('portal/pacientes.view_details') }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
                <button
                    type="button"
                    disabled
                    title="{{ __('portal/pacientes.remove') }}"
                    class="flex size-9 cursor-not-allowed items-center justify-center rounded-full border border-error-border text-error-text opacity-60 hover:bg-error-light"
                    aria-label="{{ __('portal/pacientes.remove') }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
