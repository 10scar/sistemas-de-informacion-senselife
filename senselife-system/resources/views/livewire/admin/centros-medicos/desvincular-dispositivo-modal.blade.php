@if ($showDesvincularModal)
    <div
        wire:key="desvincular-dispositivo-modal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeDesvincularModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="desvincular-dispositivo-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-[416px] flex-col items-center justify-center overflow-hidden rounded-3xl bg-neutral-0 p-8 pt-10 text-center shadow-elev-card">

            <div
                class="mb-6 flex size-14 shrink-0 items-center justify-center rounded-full bg-error-light"
                aria-label="{{ __('admin/centros-medicos.show.desvincular_dispositivo_modal.icon_aria') }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="text-error" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
            </div>

            <div class="mb-8 flex flex-col gap-2">
                <h2 id="desvincular-dispositivo-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                    {{ __('admin/centros-medicos.show.desvincular_dispositivo_modal.title') }}
                </h2>
                <p class="px-2 text-sm leading-relaxed text-neutral-600">
                    {{ __('admin/centros-medicos.show.desvincular_dispositivo_modal.description', ['serie' => $desvincularDispositivoSerie]) }}
                </p>
            </div>

            <div class="flex w-full items-center gap-3">
                <button
                    type="button"
                    wire:click="closeDesvincularModal"
                    class="flex-1 rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('admin/centros-medicos.show.desvincular_dispositivo_modal.cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="confirmDesvincularDispositivo"
                    wire:loading.attr="disabled"
                    class="flex-[1.2] rounded-lg bg-error py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-error-text disabled:cursor-not-allowed disabled:opacity-70">
                    {{ __('admin/centros-medicos.show.desvincular_dispositivo_modal.confirm') }}
                </button>
            </div>
        </div>
    </div>
@endif
