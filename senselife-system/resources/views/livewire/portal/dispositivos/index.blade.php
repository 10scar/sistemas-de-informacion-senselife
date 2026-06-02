<div class="w-full px-6 py-6 md:px-10 md:py-8 lg:px-12">
    <a
        href="{{ route('portal.dashboard') }}"
        wire:navigate
        class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-primary-600 transition hover:text-primary-700"
    >
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        {{ __('portal/dispositivos.back_dashboard') }}
    </a>

    <header class="mb-8">
        <h1 class="font-display text-2xl font-bold tracking-tight text-text sm:text-3xl">
            {{ __('portal/dispositivos.title') }}
        </h1>
        @if ($centro !== null)
            <p class="mt-2 text-sm text-neutral-600">
                @if ($capacidad !== null)
                    {{ __('portal/dispositivos.subtitle_stats', [
                        'en_uso' => number_format($enUso),
                        'total' => number_format($activosTotal),
                        'pct' => number_format($capacidad, 1),
                    ]) }}
                @else
                    {{ $centro->nombre }}
                    · {{ number_format($registradosTotal) }} {{ __('portal/dispositivos.title') }}
                @endif
            </p>
        @endif
    </header>

    @if ($dispositivos->isEmpty())
        <p class="rounded-2xl border border-neutral-200 bg-neutral-0 px-6 py-12 text-center text-sm text-neutral-500 shadow-elev-card">
            {{ __('portal/dispositivos.empty') }}
        </p>
    @else
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @foreach ($dispositivos as $dispositivo)
                @include('livewire.portal.dispositivos.partials.device-card', [
                    'dispositivo' => $dispositivo,
                ])
            @endforeach
        </div>
    @endif

    @include('livewire.portal.dispositivos.edit-modal')
    @include('livewire.portal.dispositivos.success-modal')
</div>
