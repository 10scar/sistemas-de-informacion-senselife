@php
    use App\Support\AlertaPresentacion;
@endphp

<div
    class="w-full px-6 py-6 md:px-10 md:py-8 lg:px-12"
    wire:poll.{{ $pollSeconds }}s
>
    @if ($data === null)
        <p class="text-sm text-neutral-600">{{ __('portal/dashboard.no_centro') }}</p>
    @else
        <header class="mb-8">
            <h1 class="font-display text-2xl font-bold tracking-tight text-text sm:text-3xl">
                {{ __('portal/dashboard.title') }}
            </h1>
            <p class="mt-2 text-sm text-neutral-600">
                {{ __('portal/dashboard.subtitle') }}
                @if ($centro !== null)
                    <span class="font-medium text-neutral-700">· {{ $centro->nombre }}</span>
                @endif
                <span class="text-neutral-400">·</span>
                <span class="text-neutral-500">
                    {{ __('portal/dashboard.updated_ago', ['time' => $data['actualizado']->locale('es')->diffForHumans(short: true)]) }}
                </span>
            </p>
        </header>

        {{-- Actividad reciente --}}
        <section class="mb-6 overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-0 shadow-elev-card">
            <div class="flex flex-col gap-3 border-b border-neutral-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <h2 class="text-base font-bold text-text">{{ __('portal/dashboard.section_activity') }}</h2>
                <a
                    href="{{ route('portal.alertas.index') }}"
                    wire:navigate
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-neutral-300 bg-neutral-0 px-4 text-sm font-semibold text-primary-600 shadow-elev-control transition hover:bg-accent-50"
                >
                    {{ __('portal/dashboard.view_all') }} →
                </a>
            </div>
            @include('livewire.portal.pacientes.partials.alertas-table', [
                'alertas' => $data['actividadReciente'],
                'showActions' => false,
                'compact' => true,
            ])
        </section>

        {{-- Tarjetas resumen --}}
        <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-neutral-200 bg-neutral-0 px-5 py-5 shadow-elev-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    {{ __('portal/dashboard.card_patients') }}
                </p>
                <p class="mt-2 text-4xl font-bold tabular-nums text-text">
                    {{ number_format($data['pacientesActivos']) }}
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if ($data['pacientesNuevosHoy'] > 0)
                        <span class="inline-flex rounded-lg border border-success-border bg-success-light/50 px-2 py-0.5 text-xs font-semibold text-success-text">
                            {{ __('portal/dashboard.card_patients_new', ['count' => $data['pacientesNuevosHoy']]) }}
                        </span>
                    @endif
                </div>
            </article>

            <a
                href="{{ route('portal.dispositivos.index') }}"
                wire:navigate
                class="block rounded-2xl border border-neutral-200 bg-neutral-0 px-5 py-5 shadow-elev-card transition hover:border-primary-200 hover:shadow-md"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    {{ __('portal/dashboard.card_devices') }}
                </p>
                <p class="mt-2 text-4xl font-bold tabular-nums text-text">
                    {{ number_format($data['dispositivosEnUso']) }}
                    <span class="text-2xl font-semibold text-neutral-400">/</span>
                    {{ number_format($data['dispositivosActivosTotal']) }}
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if ($capacidadDispositivos !== null)
                        <span class="inline-flex rounded-lg border border-success-border bg-success-light/50 px-2 py-0.5 text-xs font-semibold text-success-text">
                            {{ __('portal/dashboard.card_devices_capacity', ['pct' => number_format($capacidadDispositivos, 1)]) }}
                        </span>
                    @endif
                    <span class="text-xs text-neutral-500">
                        {{ __('portal/dashboard.card_devices_available', ['count' => $dispositivosDisponibles]) }}
                    </span>
                </div>
            </a>

            <article class="rounded-2xl border border-neutral-200 bg-neutral-0 px-5 py-5 shadow-elev-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    {{ __('portal/dashboard.card_alerts_today') }}
                </p>
                <p class="mt-2 text-4xl font-bold tabular-nums text-text">
                    {{ number_format($data['alertasHoy']) }}
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if ($variacionAlertas !== null)
                        @php
                            $sign = $variacionAlertas > 0
                                ? __('portal/dashboard.card_alerts_vs_yesterday_up')
                                : ($variacionAlertas < 0
                                    ? __('portal/dashboard.card_alerts_vs_yesterday_down')
                                    : __('portal/dashboard.card_alerts_vs_yesterday_flat'));
                            $pctAbs = number_format(abs($variacionAlertas), 1);
                        @endphp
                        <span @class([
                            'inline-flex rounded-lg border px-2 py-0.5 text-xs font-semibold',
                            'border-success-border bg-success-light/50 text-success-text' => $variacionAlertas <= 0,
                            'border-warning-border bg-warning-light/50 text-warning-text' => $variacionAlertas > 0,
                        ])>
                            {{ __('portal/dashboard.card_alerts_vs_yesterday', ['sign' => $sign, 'pct' => $pctAbs]) }}
                        </span>
                    @endif
                    <span class="text-xs text-neutral-500">
                        {{ __('portal/dashboard.card_alerts_breakdown', [
                            'criticas' => $data['alertasCriticasHoy'],
                            'advertencias' => $data['alertasAdvertenciasHoy'],
                        ]) }}
                    </span>
                </div>
            </article>
        </section>

        {{-- Gráficos --}}
        @include('livewire.portal.dashboard.partials.charts', [
            'distribucion' => $data['distribucion'],
            'chartDesde' => $data['chartDesde'],
            'chartHasta' => $data['chartHasta'],
        ])
    @endif
</div>
