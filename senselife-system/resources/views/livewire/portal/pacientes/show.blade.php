@php
    use App\Enums\PacienteEstadoMonitoreo;

    $estado = $paciente->estadoMonitoreoVisual();
    $avatarBgClass = match ($estado) {
        PacienteEstadoMonitoreo::Critico => 'bg-error-light border-error-mid text-error-text',
        PacienteEstadoMonitoreo::Alerta => 'bg-warning-light border-warning-mid text-warning-text',
        default => 'bg-success-light border-success-mid text-success-text',
    };
    $fcDisplay = $fcActual !== null ? number_format($fcActual, 0) : __('portal/pacientes.vital_placeholder');
    $frDisplay = $frActual !== null ? number_format($frActual, 0) : __('portal/pacientes.vital_placeholder');
    $fcPromDisplay = $fcPromedio !== null ? number_format($fcPromedio, 0) : __('portal/pacientes.vital_placeholder');
    $frPromDisplay = $frPromedio !== null ? number_format($frPromedio, 0) : __('portal/pacientes.vital_placeholder');
@endphp

<div wire:poll.3s="refrescarTelemetria" class="w-full px-6 py-6 md:px-10 md:py-8 lg:px-12">
    <style>
        @keyframes sweep {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .scan-line { animation: sweep 3s linear infinite; }
    </style>

    <div class="mb-6">
        <a
            href="{{ route('portal.pacientes.index') }}"
            wire:navigate
            class="inline-flex items-center gap-2 text-sm font-medium text-neutral-600 transition-colors hover:text-primary-600">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('portal/pacientes.show.back') }}
        </a>
    </div>

    <section
        class="mb-6 flex flex-col gap-6 rounded-2xl bg-neutral-0 p-5 sm:flex-row sm:items-center sm:gap-4 sm:p-6">
        <div
            class="flex size-11 shrink-0 items-center justify-center rounded-full border text-sm font-bold {{ $avatarBgClass }}">
            {{ $paciente->iniciales }}
        </div>
        <div class="flex min-w-0 flex-col">
            <h1 class="text-base font-bold leading-tight text-text">{{ $paciente->nombre_completo }}</h1>
            <p class="mt-0.5 text-sm text-neutral-600">
                {{ $sexoLabel }}
                <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                {{ number_format((float) $paciente->peso, 1) }} kg
                <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                {{ number_format((float) $paciente->altura, 0) }} cm
            </p>
        </div>
        <div class="hidden h-10 w-px shrink-0 bg-neutral-100 sm:block" aria-hidden="true"></div>
        <div class="flex min-w-0 flex-col">
            <span class="mb-0.5 text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                {{ __('portal/pacientes.show.cuna_label') }}
            </span>
            <span class="flex items-center gap-1.5 text-sm font-semibold text-text">
                @if ($dispositivo?->ubicacion)
                    <span class="size-1.5 rounded-full bg-info" aria-hidden="true"></span>
                    {{ $dispositivo->ubicacion }}
                @else
                    {{ __('portal/pacientes.unassigned_location') }}
                @endif
            </span>
        </div>
        <div class="hidden h-10 w-px shrink-0 bg-neutral-100 sm:block" aria-hidden="true"></div>
        <div class="flex min-w-0 flex-col">
            <span class="mb-0.5 text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                {{ __('portal/pacientes.show.dispositivo_label') }}
            </span>
            <span class="text-sm font-semibold text-text">
                {{ $dispositivo?->numero_serie ?? __('portal/pacientes.unassigned_location') }}
            </span>
        </div>
        <div class="flex-grow"></div>
        <div class="mt-4 flex w-full items-center gap-2 sm:mt-0 sm:w-auto">
            <button
                type="button"
                wire:click="openEditModal"
                class="h-10 flex-1 rounded-xl border border-neutral-300 bg-neutral-0 px-4 text-sm font-semibold text-primary-600 shadow-elev-control transition hover:bg-accent-50 sm:flex-none">
                {{ __('portal/pacientes.show.edit') }}
            </button>
            <button type="button" disabled
                class="h-10 flex-1 cursor-not-allowed rounded-xl border border-neutral-300 bg-neutral-0 px-4 text-sm font-semibold text-neutral-500 opacity-60 shadow-elev-control sm:flex-none">
                {{ __('portal/pacientes.show.history') }}
            </button>
        </div>
    </section>

    @if ($dispositivo === null)
        <div class="mb-6 rounded-xl border border-info-border bg-info-light px-4 py-3 text-sm text-info-text">
            {{ __('portal/pacientes.show.no_device') }}
        </div>
    @endif

    <section class="mb-6 flex flex-col gap-3">
        <h2 class="px-1 text-xs font-bold uppercase tracking-wider text-neutral-500">
            {{ __('portal/pacientes.show.averages_title') }}
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl bg-neutral-0 p-4 shadow-elev-control">
                <span class="text-[10px] font-bold tracking-wider text-neutral-500">
                    {{ __('portal/pacientes.show.fc_avg') }}
                </span>
                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold text-text">{{ $fcPromDisplay }}</span>
                    <span class="text-xs font-semibold text-neutral-500">bpm</span>
                </div>
            </div>
            <div class="rounded-2xl bg-neutral-0 p-4 shadow-elev-control">
                <span class="text-[10px] font-bold tracking-wider text-neutral-500">
                    {{ __('portal/pacientes.show.fr_avg') }}
                </span>
                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold text-text">{{ $frPromDisplay }}</span>
                    <span class="text-xs font-semibold text-neutral-500">rpm</span>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-6 flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-2 px-1">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-500">
                {{ __('portal/pacientes.show.monitor_title') }}
            </h2>
            @if ($dispositivo !== null)
                <div class="flex items-center gap-1.5 rounded-full border border-error-border bg-error-light px-2 py-0.5">
                    <span class="size-1.5 animate-pulse rounded-full bg-error" aria-hidden="true"></span>
                    <span class="text-[10px] font-bold uppercase tracking-wide text-error">Live</span>
                </div>
                <span class="text-[10px] font-medium text-neutral-500">
                    {{ __('portal/pacientes.show.monitor_window', ['count' => $puntosVentana, 'max' => $ventanaMax]) }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="flex flex-col gap-4 rounded-2xl bg-neutral-0 p-5 shadow-elev-control">
                <div class="flex items-center justify-between px-1">
                    <span class="text-sm font-bold text-text">{{ __('portal/pacientes.show.ecg_wave') }}</span>
                    <span class="text-lg font-bold text-error">
                        {{ $fcDisplay }}
                        <span class="text-xs text-neutral-500">bpm</span>
                    </span>
                </div>
                <div class="relative h-[140px] overflow-hidden rounded-xl border border-neutral-100 bg-neutral-50">
                    @if ($puntosVentana > 0)
                        <span class="absolute left-2 top-2 z-10 rounded bg-neutral-0/90 px-1.5 py-0.5 text-[9px] font-semibold text-neutral-500">
                            {{ __('portal/pacientes.show.monitor_range_fc', [
                                'min' => number_format($fcRango['min'], 0),
                                'max' => number_format($fcRango['max'], 0),
                            ]) }}
                        </span>
                    @endif
                    <div class="scan-line pointer-events-none absolute inset-0 z-[1] w-12 bg-gradient-to-r from-transparent via-error/20 to-transparent"></div>
                    <svg class="relative z-0 h-full w-full" viewBox="0 0 400 100" preserveAspectRatio="none" aria-hidden="true">
                        <path
                            d="{{ $fcWavePath }}"
                            fill="none" stroke="var(--color-error)" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-2xl bg-neutral-0 p-5 shadow-elev-control">
                <div class="flex items-center justify-between px-1">
                    <span class="text-sm font-bold text-text">{{ __('portal/pacientes.show.resp_wave') }}</span>
                    <span class="text-lg font-bold text-info">
                        {{ $frDisplay }}
                        <span class="text-xs text-neutral-500">rpm</span>
                    </span>
                </div>
                <div class="relative h-[140px] overflow-hidden rounded-xl border border-neutral-100 bg-neutral-50">
                    @if ($puntosVentana > 0)
                        <span class="absolute left-2 top-2 z-10 rounded bg-neutral-0/90 px-1.5 py-0.5 text-[9px] font-semibold text-neutral-500">
                            {{ __('portal/pacientes.show.monitor_range_fr', [
                                'min' => number_format($frRango['min'], 0),
                                'max' => number_format($frRango['max'], 0),
                            ]) }}
                        </span>
                    @endif
                    <div class="scan-line pointer-events-none absolute inset-0 z-[1] w-12 bg-gradient-to-r from-transparent via-info/20 to-transparent"></div>
                    <svg class="relative z-0 h-full w-full" viewBox="0 0 400 100" preserveAspectRatio="none" aria-hidden="true">
                        <path
                            d="{{ $frWavePath }}"
                            fill="none" stroke="var(--color-info)" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <section class="flex flex-col gap-3">
        <h2 class="px-1 text-xs font-bold uppercase tracking-wider text-neutral-500">
            {{ __('portal/pacientes.show.alerts_title') }}
        </h2>
        <div class="divide-y divide-neutral-100 overflow-hidden rounded-2xl bg-neutral-0 shadow-elev-control">
            @forelse ($alertasRecientes as $alerta)
                @include('livewire.portal.pacientes.partials.alerta-row', ['alerta' => $alerta])
            @empty
                <p class="p-4 text-sm text-neutral-500">{{ __('portal/pacientes.show.no_alerts') }}</p>
            @endforelse
        </div>
    </section>

    @include('livewire.portal.pacientes.edit-modal')
    @include('livewire.portal.pacientes.success-modal')
</div>
