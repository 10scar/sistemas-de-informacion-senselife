{{-- Modal de confirmación: se cierra automáticamente tras unos segundos o al pulsar Continuar. --}}
@if ($showSuccessModal)
    <div
        wire:key="success-dispositivo-modal"
        x-data
        x-init="setTimeout(() => $wire.closeSuccessModal(), 2500)"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeSuccessModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="success-dispositivo-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-md flex-col items-center justify-center gap-6 rounded-3xl bg-neutral-0 p-8 pt-10 text-center shadow-elev-card">

            {{-- Icono de éxito --}}
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-full bg-success-mid"
                aria-label="{{ __('admin/dispositivos.success_modal.icon_aria') }}">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="text-success-text" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>

            {{-- Título --}}
            <h2 id="success-dispositivo-title"
                class="px-4 text-xl font-bold leading-tight text-text md:text-[22px]">
                {{ $successTitle !== '' ? $successTitle : __('admin/dispositivos.success_modal.title') }}
            </h2>

            {{-- Acción --}}
            <div class="mt-2 flex w-full justify-center">
                <button
                    type="button"
                    wire:click="closeSuccessModal"
                    class="flex h-11 w-[172px] items-center justify-center rounded-lg border border-neutral-200 bg-neutral-0 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('admin/dispositivos.success_modal.continue') }}
                </button>
            </div>
        </div>
    </div>
@endif
