@props([
    'titulo',
    'valor',
    'unidad',
    'tendenciaPct' => null,
    'sparkPath' => '',
])

<article class="flex flex-col rounded-2xl border border-neutral-200 bg-neutral-0 p-5 shadow-elev-card">
    <p class="text-xs font-semibold text-neutral-500">{{ $titulo }}</p>
    <div class="mt-2 flex flex-wrap items-baseline gap-x-2 gap-y-1">
        <span class="font-display text-3xl font-bold tabular-nums text-text">{{ $valor }}</span>
        <span class="text-sm font-semibold text-neutral-500">{{ $unidad }}</span>
    </div>

    @if ($tendenciaPct !== null)
        @php
            $sube = $tendenciaPct >= 0;
            $abs = abs($tendenciaPct);
        @endphp
        <p @class([
            'mt-1 flex items-center gap-1 text-xs font-semibold',
            'text-success' => $sube,
            'text-error' => ! $sube,
        ])>
            @if ($sube)
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-7 7m7-7l7 7" />
                </svg>
                ↑ {{ $abs }}%
            @else
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l7-7m-7 7l-7-7" />
                </svg>
                ↓ {{ $abs }}%
            @endif
        </p>
    @endif

    <div class="mt-4 h-9 border-t border-neutral-100 pt-3">
        @if ($sparkPath !== '')
            <svg class="h-full w-full" viewBox="0 0 120 36" preserveAspectRatio="none" aria-hidden="true">
                <path d="{{ $sparkPath }}" fill="none" stroke="var(--color-neutral-400)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        @endif
    </div>
</article>
