<div class="w-full">
    @if (! $centro)
        <div class="rounded-2xl border border-neutral-200 bg-neutral-0 p-8 text-center text-neutral-600 shadow-elev-card">
            {{ __('portal/pacientes.no_centro') }}
        </div>
    @else
        <div class="border border-neutral-200 bg-neutral-0 p-6 shadow-elev-card md:p-8">
            <header class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-start">
                <div>
                    <h1 class="font-display text-3xl font-bold tracking-tight text-text">
                        {{ __('portal/pacientes.title') }}
                    </h1>
                    <p class="mt-1 font-medium text-neutral-600">
                        {{ __('portal/pacientes.subtitle', ['centro' => $centro->nombre, 'count' => $totalPacientes]) }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-400"
                            aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('portal/pacientes.search_placeholder') }}"
                            class="block w-64 rounded-lg border border-neutral-300 bg-neutral-0 py-2 pl-10 pr-3 text-sm text-text placeholder:text-neutral-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                    </div>

                    <button
                        type="button"
                        wire:click="openCreateModal"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-neutral-0 shadow-elev-control transition hover:bg-primary-700">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('portal/pacientes.create_button') }}
                    </button>
                </div>
            </header>

            @if ($pacientes->isEmpty())
                <p class="py-12 text-center text-neutral-600">{{ __('portal/pacientes.empty') }}</p>
            @else
                <div class="flex flex-col gap-4">
                    @foreach ($pacientes as $paciente)
                        @include('livewire.portal.pacientes.partials.paciente-card', ['paciente' => $paciente])
                    @endforeach
                </div>

                @if ($pacientes->hasPages())
                    <div class="mt-6 border-t border-neutral-200 pt-4">
                        {{ $pacientes->links() }}
                    </div>
                @endif
            @endif
        </div>

        @include('livewire.portal.pacientes.create-modal')
        @include('livewire.portal.pacientes.success-modal')
    @endif
</div>
