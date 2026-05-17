{{-- Modal de confirmación tras crear/actualizar centro médico (auto-cierre). --}}
@if ($showSuccessModal)
    <div
        wire:key="success-centro-modal"
        x-data
        x-init="setTimeout(() => $wire.closeSuccessModal(), 2500)"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeSuccessModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="success-centro-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-md flex-col items-center justify-center gap-6 rounded-3xl bg-neutral-0 p-8 pt-10 text-center shadow-elev-card">

            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-full bg-success-mid"
                aria-label="{{ __('admin/centros-medicos.success_modal.icon_aria') }}">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="text-success-text" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>

            <h2 id="success-centro-title"
                class="px-4 text-xl font-bold leading-tight text-text md:text-[22px]">
                {{ $successTitle !== '' ? $successTitle : __('admin/centros-medicos.success_modal.created') }}
            </h2>

            <div class="mt-2 flex w-full justify-center">
                <button
                    type="button"
                    wire:click="closeSuccessModal"
                    class="flex h-11 w-[172px] items-center justify-center rounded-lg border border-neutral-200 bg-neutral-0 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('admin/centros-medicos.success_modal.continue') }}
                </button>
            </div>
        </div>
    </div>
@endif
