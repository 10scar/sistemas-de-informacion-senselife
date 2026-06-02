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

<div
    @if ($dispositivo !== null) wire:poll.3s="refrescarTelemetria" @endif
    class="w-full px-6 py-6 md:px-10 md:py-8 lg:px-12">
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
            <a
                href="{{ route('portal.pacientes.historial', $paciente) }}"
                wire:navigate
                class="flex h-10 flex-1 items-center justify-center rounded-xl border border-neutral-300 bg-neutral-0 px-4 text-sm font-semibold text-primary-600 shadow-elev-control transition hover:bg-accent-50 sm:flex-none">
                {{ __('portal/pacientes.show.history') }}
            </a>
        </div>
    </section>

    @if ($dispositivo === null)
        <div class="mb-6 rounded-xl border border-info-border bg-info-light px-4 py-3 text-sm text-info-text">
            {{ __('portal/pacientes.show.no_device') }}
        </div>
    @else
        <section class="mb-6 grid grid-cols-1 gap-5 xl:grid-cols-2">
            @include('livewire.portal.pacientes.partials.monitor-vital-card', [
                'title' => __('portal/pacientes.show.fc_title'),
                'valor' => $fcDisplay,
                'unidad' => __('portal/pacientes.show.unit_lpm'),
                'promedio' => $fcPromDisplay,
                'tendenciaPct' => $fcTendenciaPct,
                'chart' => $fcChart,
                'strokeColor' => 'var(--color-primary-600)',
            ])
            @include('livewire.portal.pacientes.partials.monitor-vital-card', [
                'title' => __('portal/pacientes.show.fr_title'),
                'valor' => $frDisplay,
                'unidad' => __('portal/pacientes.show.unit_rpm'),
                'promedio' => $frPromDisplay,
                'tendenciaPct' => $frTendenciaPct,
                'chart' => $frChart,
                'strokeColor' => 'var(--color-primary-600)',
            ])
        </section>
    @endif

    <section class="flex flex-col gap-3">
        <h2 class="px-1 text-xs font-bold uppercase tracking-wider text-neutral-500">
            {{ __('portal/pacientes.show.alerts_title') }}
        </h2>
        <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-0 shadow-elev-card">
            @include('livewire.portal.pacientes.partials.alertas-table', [
                'alertas' => $alertasRecientes,
                'showActions' => true,
            ])
        </div>
    </section>

    @include('livewire.portal.pacientes.edit-modal')
    @include('livewire.portal.pacientes.success-modal')
    @include('livewire.portal.pacientes.partials.alertas-confirm-modals')
</div>
