@props([
    'total',
    'atendidas',
    'enRevision',
    'ignoradas',
])

<section class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <article class="rounded-2xl border border-neutral-200 bg-neutral-0 px-4 py-3 shadow-elev-card">
        <p class="text-xs font-semibold text-neutral-500">{{ __('portal/alertas.stat_total') }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums text-text">{{ number_format($total) }}</p>
    </article>
    <article class="rounded-2xl border border-success-border bg-success-light/40 px-4 py-3 shadow-elev-card">
        <p class="text-xs font-semibold text-success-text">{{ __('portal/alertas.stat_atendidas') }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums text-success-text">
            {{ number_format($atendidas) }}
            @if ($total > 0)
                <span class="text-sm font-semibold">({{ number_format(($atendidas / $total) * 100, 1) }}%)</span>
            @endif
        </p>
    </article>
    <article class="rounded-2xl border border-warning-border bg-warning-light/40 px-4 py-3 shadow-elev-card">
        <p class="text-xs font-semibold text-warning-text">{{ __('portal/alertas.stat_revision') }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums text-warning-text">
            {{ number_format($enRevision) }}
            @if ($total > 0)
                <span class="text-sm font-semibold">({{ number_format(($enRevision / $total) * 100, 1) }}%)</span>
            @endif
        </p>
    </article>
    <article class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 shadow-elev-card">
        <p class="text-xs font-semibold text-neutral-600">{{ __('portal/alertas.stat_ignoradas') }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums text-neutral-700">
            {{ number_format($ignoradas) }}
            @if ($total > 0)
                <span class="text-sm font-semibold">({{ number_format(($ignoradas / $total) * 100, 1) }}%)</span>
            @endif
        </p>
    </article>
</section>
