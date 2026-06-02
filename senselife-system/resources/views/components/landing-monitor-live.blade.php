@props([
    'payload',
])

<article
    data-landing-monitor='@json($payload)'
    class="flex flex-col rounded-2xl border border-neutral-200 bg-neutral-0 p-6 shadow-elev-card">
    <p class="text-[11px] font-medium uppercase tracking-wider text-neutral-400">
        {{ __('portal/pacientes.show.monitor_badge') }}
    </p>
    <h3 class="mt-1 text-base font-semibold text-neutral-600">
        {{ __('portal/pacientes.show.fc_title') }}
    </h3>

    <div class="mt-3 flex flex-wrap items-baseline gap-x-2 gap-y-1">
        <span
            data-landing-valor
            class="font-display text-5xl font-bold leading-none tabular-nums text-primary-600 transition-all duration-300">
            {{ (int) end($payload['valores']) }}
        </span>
        <span class="text-lg font-semibold text-primary-600">{{ __('portal/pacientes.show.unit_lpm') }}</span>
    </div>

    <p
        data-landing-tendencia
        class="mt-2 flex items-center gap-1 text-sm font-semibold text-error">
        <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-7 7m7-7l7 7" />
        </svg>
        <span data-landing-tendencia-text>+12% vs media h.</span>
    </p>

    <div class="relative mt-5">
        <div class="flex gap-2">
            <div class="relative h-[140px] w-10 shrink-0">
                <span class="absolute left-0 top-0 text-[10px] font-semibold text-neutral-400">
                    {{ __('portal/pacientes.show.unit_lpm') }}
                </span>
                <div data-landing-y-axis class="contents"></div>
            </div>

            <div class="relative h-[140px] min-w-0 flex-1 overflow-hidden">
                <div
                    class="landing-monitor-scan pointer-events-none absolute inset-0 z-[2] w-12 bg-gradient-to-r from-transparent via-primary-600/10 to-transparent"
                    aria-hidden="true"></div>
                <div data-landing-grid class="absolute inset-0"></div>
                <svg
                    class="relative z-[1] h-full w-full"
                    viewBox="0 0 400 120"
                    preserveAspectRatio="none"
                    aria-hidden="true">
                    <g data-landing-umbrales></g>
                    <path
                        data-landing-path
                        fill="none"
                        stroke="var(--color-primary-600)"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="transition-all duration-300 ease-out" />
                </svg>
            </div>
        </div>

        <div data-landing-x-axis class="relative ml-12 mt-2 h-4"></div>
    </div>

    <div class="mt-4 flex items-center justify-center gap-2 border-t border-neutral-100 pt-3">
        <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
            {{ __('portal/pacientes.show.avg_24h_label') }}
        </span>
        <span class="font-display text-base font-bold tabular-nums text-primary-600">
            <span data-landing-promedio>{{ (int) round(array_sum($payload['valores']) / count($payload['valores'])) }}</span>
            <span class="text-xs font-semibold text-neutral-500">{{ __('portal/pacientes.show.unit_lpm') }}</span>
        </span>
    </div>
</article>
