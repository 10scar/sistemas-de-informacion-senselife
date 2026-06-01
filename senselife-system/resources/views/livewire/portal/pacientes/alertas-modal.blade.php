@if ($showAlertasModal)
    <div
        wire:key="portal-alertas-modal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeAlertasModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="portal-alertas-title">
        <div
            wire:click.stop
            class="relative flex max-h-[88vh] w-full max-w-3xl min-h-0 min-w-0 flex-col overflow-hidden rounded-3xl bg-neutral-0 p-7 shadow-elev-card">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 id="portal-alertas-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                        {{ __('portal/pacientes.show.alertas_modal_title') }}
                    </h2>
                    <p class="text-sm text-neutral-600">{{ $alertasModalPacienteNombre }}</p>
                </div>
                <button
                    type="button"
                    wire:click="closeAlertasModal"
                    class="flex size-7 shrink-0 items-center justify-center rounded-lg border border-neutral-200 text-neutral-600 transition-colors hover:bg-neutral-50"
                    aria-label="{{ __('portal/pacientes.show.close_modal') }}">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="10.5" y1="3.5" x2="3.5" y2="10.5" />
                        <line x1="3.5" y1="3.5" x2="10.5" y2="10.5" />
                    </svg>
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto rounded-2xl border border-neutral-200">
                @forelse ($alertasModal as $alerta)
                    @include('livewire.portal.pacientes.partials.alerta-row', ['alerta' => $alerta])
                @empty
                    <p class="p-4 text-sm text-neutral-500">{{ __('portal/pacientes.show.no_alerts') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endif
