@props([
    'distribucion',
    'chartDesde',
    'chartHasta',
])

@php
    $items = $distribucion['items'];
    $total = $distribucion['total'];
    $maxCount = collect($items)->max('count') ?: 1;
    $desdeLabel = $chartDesde->locale('es')->isoFormat('D MMM YYYY');
    $hastaLabel = $chartHasta->locale('es')->isoFormat('D MMM YYYY');
@endphp

<section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <article class="rounded-2xl border border-neutral-200 bg-neutral-0 px-5 py-6 shadow-elev-card sm:px-6">
        <h2 class="text-base font-bold text-text">{{ __('portal/dashboard.chart_distribution') }}</h2>

        @if ($total === 0)
            <p class="mt-8 text-center text-sm text-neutral-500">{{ __('portal/dashboard.chart_empty') }}</p>
        @else
            <div class="mt-8 flex flex-col items-center gap-8">
                <div class="relative size-44 sm:size-52">
                    <div
                        class="size-full rounded-full"
                        style="background: {{ $distribucion['conic'] }}"
                        role="img"
                        aria-label="{{ __('portal/dashboard.chart_distribution') }}"
                    ></div>
                    <div class="absolute inset-6 flex flex-col items-center justify-center rounded-full bg-neutral-0 text-center shadow-inner sm:inset-8">
                        <span class="text-2xl font-bold tabular-nums text-text sm:text-3xl">{{ number_format($total) }}</span>
                        <span class="mt-0.5 max-w-[8rem] text-[10px] font-semibold uppercase leading-tight tracking-wide text-neutral-500">
                            {{ __('portal/dashboard.chart_distribution_center') }}
                        </span>
                    </div>
                </div>

                <div class="grid w-full grid-cols-2 gap-3">
                    @foreach ($items as $item)
                        <div class="rounded-xl border border-neutral-200 px-3 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                <span class="text-xs font-semibold text-neutral-700">{{ $item['label'] }}</span>
                            </div>
                            <p class="mt-1 text-lg font-bold tabular-nums text-text">
                                {{ number_format($item['count']) }}
                                <span class="text-sm font-semibold text-neutral-500">({{ number_format($item['pct'], 0) }}%)</span>
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap justify-center gap-4">
                    @foreach ($items as $item)
                        <div class="flex items-center gap-1.5 text-xs text-neutral-600">
                            <span class="size-2 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                            {{ $item['label'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </article>

    <article class="rounded-2xl border border-neutral-200 bg-neutral-0 px-5 py-6 shadow-elev-card sm:px-6">
        <h2 class="text-base font-bold text-text">{{ __('portal/dashboard.chart_common') }}</h2>

        @if ($total === 0)
            <p class="mt-8 text-center text-sm text-neutral-500">{{ __('portal/dashboard.chart_empty') }}</p>
        @else
            <div class="mt-8 space-y-5">
                @foreach ($items as $item)
                    @php
                        $barWidth = $maxCount > 0 ? max(8, ($item['count'] / $maxCount) * 100) : 0;
                    @endphp
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-2 text-sm">
                            <span class="font-semibold text-neutral-700">{{ $item['label'] }}</span>
                            <span class="shrink-0 tabular-nums text-xs font-bold text-neutral-500">
                                {{ number_format($item['pct'], 0) }}%
                            </span>
                        </div>
                        <div class="relative h-9 overflow-hidden rounded-lg bg-neutral-100">
                            <div
                                class="flex h-full min-w-[3rem] items-center rounded-lg px-3 text-xs font-bold text-white transition-all duration-500"
                                style="width: {{ $barWidth }}%; background-color: {{ $item['color'] }}"
                            >
                                @if ($item['count'] > 0)
                                    {{ __('portal/dashboard.chart_common_count', ['count' => number_format($item['count'])]) }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 border-t border-neutral-100 pt-4 text-xs leading-relaxed text-neutral-500">
                {{ __('portal/dashboard.chart_footer', ['desde' => $desdeLabel, 'hasta' => $hastaLabel]) }}
            </p>
        @endif
    </article>
</section>
