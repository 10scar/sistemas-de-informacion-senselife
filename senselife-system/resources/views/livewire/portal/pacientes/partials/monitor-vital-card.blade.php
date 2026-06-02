@props([
    'title',
    'valor',
    'unidad',
    'promedio',
    'tendenciaPct' => null,
    'chart',
    'strokeColor' => 'var(--color-primary-600)',
])

@php
    $chartHeight = 120;
    $chartWidth = 400;
    $coloresLinea = [
        'critico_alto' => 'var(--color-error)',
        'critico_bajo' => 'var(--color-error)',
        'alerta_alto' => 'var(--color-warning)',
        'alerta_bajo' => 'var(--color-warning)',
    ];
@endphp

<article class="flex flex-col rounded-2xl border border-neutral-200 bg-neutral-0 p-6 shadow-elev-card">
    <p class="text-[11px] font-medium uppercase tracking-wider text-neutral-400">
        {{ __('portal/pacientes.show.monitor_badge') }}
    </p>
    <h3 class="mt-1 text-base font-semibold text-neutral-600">{{ $title }}</h3>

    <div class="mt-3 flex flex-wrap items-baseline gap-x-2 gap-y-1">
        <span class="font-display text-5xl font-bold leading-none tabular-nums text-primary-600">
            {{ $valor }}
        </span>
        <span class="text-lg font-semibold text-primary-600">{{ $unidad }}</span>
    </div>

    @if ($tendenciaPct !== null)
        @php
            $sube = $tendenciaPct >= 0;
            $tendenciaAbs = abs($tendenciaPct);
            $tendenciaTexto = ($sube ? '+' : '−').$tendenciaAbs;
        @endphp
        <p @class([
            'mt-2 flex items-center gap-1 text-sm font-semibold',
            'text-error' => $sube,
            'text-success' => ! $sube,
        ])>
            @if ($sube)
                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-7 7m7-7l7 7" />
                </svg>
            @else
                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l7-7m-7 7l-7-7" />
                </svg>
            @endif
            {{ __('portal/pacientes.show.trend_vs_hour', ['value' => $tendenciaTexto]) }}
        </p>
    @endif

    <div class="relative mt-5">
        <div class="flex gap-2">
            <div class="relative h-[140px] w-10 shrink-0">
                <span class="absolute left-0 top-0 text-[10px] font-semibold text-neutral-400">{{ $unidad }}</span>
                @foreach ($chart['y_ticks'] as $tick)
                    @php
                        $span = max($chart['rango']['max'] - $chart['rango']['min'], 1.0);
                        $topPct = (($chart['rango']['max'] - $tick) / $span) * 100;
                    @endphp
                    <span
                        class="absolute left-0 -translate-y-1/2 text-[10px] font-semibold tabular-nums text-neutral-400"
                        style="top: {{ round($topPct, 1) }}%">
                        {{ $tick }}
                    </span>
                @endforeach
            </div>

            <div class="relative h-[140px] min-w-0 flex-1">
                @foreach ($chart['y_ticks'] as $tick)
                    @php
                        $span = max($chart['rango']['max'] - $chart['rango']['min'], 1.0);
                        $topPct = (($chart['rango']['max'] - $tick) / $span) * 100;
                    @endphp
                    <span
                        class="pointer-events-none absolute left-0 right-0 border-t border-dashed border-neutral-200"
                        style="top: {{ round($topPct, 1) }}%"
                        aria-hidden="true"></span>
                @endforeach

                <svg
                    class="relative z-[1] h-full w-full"
                    viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                    preserveAspectRatio="none"
                    aria-hidden="true">
                    @foreach ($chart['lineas_umbral'] as $linea)
                        <line
                            x1="0"
                            y1="{{ $linea['y'] }}"
                            x2="{{ $chartWidth }}"
                            y2="{{ $linea['y'] }}"
                            stroke="{{ $coloresLinea[$linea['nivel']] ?? 'var(--color-neutral-400)' }}"
                            stroke-width="{{ str_starts_with($linea['nivel'], 'critico') ? '1.75' : '1.25' }}"
                            stroke-dasharray="{{ str_starts_with($linea['nivel'], 'critico') ? '6 4' : '4 3' }}"
                            opacity="{{ str_starts_with($linea['nivel'], 'critico') ? '1' : '0.85' }}" />
                    @endforeach
                    <path
                        d="{{ $chart['path'] }}"
                        fill="none"
                        stroke="{{ $strokeColor }}"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        <div class="relative ml-12 mt-2 h-4">
            @forelse ($chart['x_labels'] as $marca)
                <span
                    class="absolute -translate-x-1/2 whitespace-nowrap text-[10px] font-medium tabular-nums text-neutral-400"
                    style="left: {{ $marca['pct'] }}%">
                    {{ $marca['label'] }}
                </span>
            @empty
            @endforelse
        </div>
    </div>

    <div class="mt-4 flex items-center justify-center gap-2 border-t border-neutral-100 pt-3">
        <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
            {{ __('portal/pacientes.show.avg_24h_label') }}
        </span>
        <span class="font-display text-base font-bold tabular-nums text-primary-600">
            {{ $promedio }}
            <span class="text-xs font-semibold text-neutral-500">{{ $unidad }}</span>
        </span>
    </div>
</article>
