<div class="w-full px-6 py-6 md:px-10 md:py-8 lg:px-12">
    <nav class="mb-4 text-sm text-neutral-500" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li>
                <a href="{{ route('portal.pacientes.index') }}" wire:navigate class="font-medium hover:text-primary-600">
                    {{ __('portal/pacientes.historial.breadcrumb_list') }}
                </a>
            </li>
            <li aria-hidden="true" class="text-neutral-300">&gt;</li>
            <li>
                <a href="{{ route('portal.pacientes.show', $paciente) }}" wire:navigate class="font-medium hover:text-primary-600">
                    {{ $paciente->nombre_completo }}
                </a>
            </li>
            <li aria-hidden="true" class="text-neutral-300">&gt;</li>
            <li class="font-semibold text-text">{{ __('portal/pacientes.historial.breadcrumb_current') }}</li>
        </ol>
    </nav>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <h1 class="font-display text-2xl font-bold tracking-tight text-text sm:text-3xl">
            {{ __('portal/pacientes.historial.title') }}
        </h1>
        <a
            href="{{ route('portal.pacientes.show', $paciente) }}"
            wire:navigate
            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-neutral-300 bg-neutral-0 px-4 text-sm font-semibold text-primary-600 shadow-elev-control transition hover:bg-accent-50">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('portal/pacientes.historial.back') }}
        </a>
    </div>

    @if (! $tieneHistorial)
        <div class="mb-6 rounded-xl border border-info-border bg-info-light px-4 py-3 text-sm text-info-text">
            {{ __('portal/pacientes.historial.sin_asociaciones') }}
        </div>
    @else
        @if ($fechaMinimaLabel !== null)
            <p class="mb-4 text-sm text-neutral-500">
                {{ __('portal/pacientes.historial.disponible_desde', ['fecha' => $fechaMinimaLabel]) }}
            </p>
        @endif

        <section class="mb-6 rounded-2xl border border-neutral-200 bg-neutral-0 p-4 shadow-elev-card sm:p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="fecha-inicio" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                            {{ __('portal/pacientes.historial.fecha_inicio') }}
                        </label>
                        <input
                            id="fecha-inicio"
                            type="datetime-local"
                            wire:model="fechaInicio"
                            min="{{ $fechaMinima }}"
                            max="{{ $fechaMaxima }}"
                            class="w-full rounded-lg border border-neutral-300 bg-neutral-0 px-3 py-2 text-sm text-text shadow-elev-control focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                        @error('fechaInicio')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="fecha-fin" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                            {{ __('portal/pacientes.historial.fecha_fin') }}
                        </label>
                        <input
                            id="fecha-fin"
                            type="datetime-local"
                            wire:model="fechaFin"
                            min="{{ $fechaMinima }}"
                            max="{{ $fechaMaxima }}"
                            class="w-full rounded-lg border border-neutral-300 bg-neutral-0 px-3 py-2 text-sm text-text shadow-elev-control focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                        @error('fechaFin')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['1h' => __('portal/pacientes.historial.filter_1h'), '24h' => __('portal/pacientes.historial.filter_24h'), '48h' => __('portal/pacientes.historial.filter_48h'), '7d' => __('portal/pacientes.historial.filter_7d')] as $clave => $etiqueta)
                        <button
                            type="button"
                            wire:click="seleccionarFiltroRapido('{{ $clave }}')"
                            wire:loading.attr="disabled"
                            wire:target="seleccionarFiltroRapido, aplicarFiltro"
                            @class([
                                'rounded-full border px-3 py-1.5 text-xs font-semibold transition disabled:cursor-wait disabled:opacity-60',
                                'border-primary-600 bg-primary-600 text-neutral-0' => $filtroRapido === $clave,
                                'border-neutral-300 bg-neutral-0 text-neutral-600 hover:border-primary-300 hover:text-primary-600' => $filtroRapido !== $clave,
                            ])>
                            {{ $etiqueta }}
                        </button>
                    @endforeach
                    <button
                        type="button"
                        wire:click="aplicarFiltro"
                        wire:loading.attr="disabled"
                        wire:target="seleccionarFiltroRapido, aplicarFiltro"
                        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-neutral-0 shadow-elev-control transition hover:bg-primary-700 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="seleccionarFiltroRapido, aplicarFiltro">
                            {{ __('portal/pacientes.historial.apply_filter') }}
                        </span>
                        <span wire:loading wire:target="seleccionarFiltroRapido, aplicarFiltro">
                            {{ __('portal/pacientes.historial.loading') }}
                        </span>
                    </button>
                </div>
            </div>
        </section>

        <div wire:loading wire:target="seleccionarFiltroRapido, aplicarFiltro" class="mb-6 rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm text-neutral-600">
            {{ __('portal/pacientes.historial.loading') }}
        </div>

        @if ($rangoSinDatos)
            <div class="mb-6 rounded-xl border border-warning-border bg-warning-light px-4 py-3 text-sm text-warning-text">
                {{ __('portal/pacientes.historial.rango_invalido') }}
            </div>
        @endif

        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            @include('livewire.portal.pacientes.partials.historial-summary-card', [
                'titulo' => __('portal/pacientes.historial.card_avg'),
                'valor' => $promedio !== null ? number_format($promedio, 0) : __('portal/pacientes.vital_placeholder'),
                'unidad' => __('portal/pacientes.show.unit_lpm'),
                'tendenciaPct' => $tendenciaProm,
                'sparkPath' => $sparkPath,
            ])
            @include('livewire.portal.pacientes.partials.historial-summary-card', [
                'titulo' => __('portal/pacientes.historial.card_min'),
                'valor' => $minimo !== null ? number_format($minimo, 0) : __('portal/pacientes.vital_placeholder'),
                'unidad' => __('portal/pacientes.show.unit_lpm'),
                'tendenciaPct' => null,
                'sparkPath' => $sparkPath,
            ])
            @include('livewire.portal.pacientes.partials.historial-summary-card', [
                'titulo' => __('portal/pacientes.historial.card_max'),
                'valor' => $maximo !== null ? number_format($maximo, 0) : __('portal/pacientes.vital_placeholder'),
                'unidad' => __('portal/pacientes.show.unit_lpm'),
                'tendenciaPct' => null,
                'sparkPath' => $sparkPath,
            ])
        </div>

        <section class="rounded-2xl border border-neutral-200 bg-neutral-0 p-5 shadow-elev-card sm:p-6">
            <div class="mb-4 flex flex-col gap-3 border-b border-neutral-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-text">
                        {{ __('portal/pacientes.historial.chart_title') }}
                    </h2>
                    <p class="mt-1 text-xs text-neutral-500">
                        {{ __('portal/pacientes.historial.chart_range', ['inicio' => $inicioLabel, 'fin' => $finLabel]) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        wire:click="alternarGrafico"
                        class="rounded-lg border border-neutral-300 bg-neutral-0 px-3 py-1.5 text-xs font-semibold text-neutral-600 transition hover:bg-neutral-50">
                        {{ $graficoVisible ? __('portal/pacientes.historial.hide_chart') : __('portal/pacientes.historial.show_chart') }}
                    </button>
                    <span class="text-xs font-medium text-neutral-400">100%</span>
                </div>
            </div>

            @if ($totalLecturas === 0 && ! $cargando)
                <p class="py-12 text-center text-sm text-neutral-500">
                    {{ $rangoSinDatos ? __('portal/pacientes.historial.rango_invalido') : __('portal/pacientes.historial.no_data') }}
                </p>
            @elseif ($graficoVisible && $fcChart !== [])
                @include('livewire.portal.pacientes.partials.historial-main-chart', [
                    'chart' => $fcChart,
                    'strokeColor' => 'var(--color-primary-600)',
                ])
            @endif
        </section>
    @endif
</div>
