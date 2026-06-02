@props([
    'chart',
    'strokeColor' => 'var(--color-primary-600)',
])

@php
    $chartWidth = 800;
    $chartHeight = 280;
    $coloresLinea = [
        'critico_alto' => 'var(--color-error)',
        'critico_bajo' => 'var(--color-error)',
        'alerta_alto' => 'var(--color-warning)',
        'alerta_bajo' => 'var(--color-warning)',
    ];
@endphp

<div class="relative">
    <div class="flex gap-3">
        <div class="relative h-[320px] w-12 shrink-0">
            <span class="absolute left-0 top-0 text-[10px] font-semibold text-neutral-400">
                {{ __('portal/pacientes.show.unit_lpm') }}
            </span>
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

        <div class="relative h-[320px] min-w-0 flex-1">
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
                        stroke-width="{{ str_starts_with($linea['nivel'], 'critico') ? '1.5' : '1' }}"
                        stroke-dasharray="{{ str_starts_with($linea['nivel'], 'critico') ? '8 5' : '5 4' }}"
                        opacity="{{ str_starts_with($linea['nivel'], 'critico') ? '0.9' : '0.75' }}" />
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

    <div class="relative ml-14 mt-3 h-5">
        @foreach ($chart['x_labels'] as $marca)
            <span
                class="absolute -translate-x-1/2 whitespace-nowrap text-[10px] font-medium tabular-nums text-neutral-400"
                style="left: {{ $marca['pct'] }}%">
                {{ $marca['label'] }}
            </span>
        @endforeach
    </div>
</div>
