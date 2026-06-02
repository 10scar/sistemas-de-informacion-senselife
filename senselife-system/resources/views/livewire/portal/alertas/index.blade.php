<div class="w-full px-6 py-6 md:px-10 md:py-8 lg:px-12">
    <nav class="mb-4 text-sm text-neutral-500" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li class="font-semibold text-text">{{ __('portal/alertas.breadcrumb_current') }}</li>
        </ol>
    </nav>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight text-text sm:text-3xl">
                {{ __('portal/alertas.title') }}
            </h1>
            @if ($centro !== null)
                <p class="mt-1 text-sm text-neutral-600">{{ $centro->nombre }}</p>
            @endif
        </div>
        <a
            href="{{ route('portal.pacientes.index') }}"
            wire:navigate
            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-neutral-300 bg-neutral-0 px-4 text-sm font-semibold text-primary-600 shadow-elev-control transition hover:bg-accent-50">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('portal/alertas.back') }}
        </a>
    </div>

    @include('livewire.portal.pacientes.partials.alertas-stats-bar', [
        'total' => $total,
        'atendidas' => $atendidas,
        'enRevision' => $enRevision,
        'ignoradas' => $ignoradas,
    ])

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-0 shadow-elev-card">
        <div class="border-b border-neutral-100 px-5 py-4 sm:px-6">
            <h2 class="text-base font-bold text-text">{{ __('portal/alertas.section_recent') }}</h2>
            <p class="mt-1 text-xs text-neutral-500">
                {{ __('portal/alertas.subtitle', ['dias' => 30]) }}
            </p>
        </div>

        @include('livewire.portal.pacientes.partials.alertas-table', [
            'alertas' => $alertas,
            'showActions' => true,
        ])

        @if ($alertas->hasPages())
            <div class="flex flex-col gap-4 border-t border-neutral-100 bg-neutral-0 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="shrink-0 text-xs text-neutral-500">
                    {{ __('portal/alertas.showing', [
                        'from' => $alertas->firstItem(),
                        'to' => $alertas->lastItem(),
                        'total' => $alertas->total(),
                    ]) }}
                </p>
                <div class="min-w-0 overflow-x-auto">
                    {{ $alertas->links() }}
                </div>
            </div>
        @endif
    </section>

    @include('livewire.portal.pacientes.partials.alertas-confirm-modals')
</div>
