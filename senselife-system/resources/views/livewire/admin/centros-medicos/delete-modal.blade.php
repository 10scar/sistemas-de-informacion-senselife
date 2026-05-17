{{-- Modal de confirmación para eliminar centro médico. --}}
@if ($showDeleteModal)
    <div
        wire:key="delete-centro-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeDeleteModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-centro-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-[416px] flex-col items-center justify-center overflow-hidden rounded-3xl bg-neutral-0 p-8 pt-10 text-center shadow-elev-card">

            {{-- Icono de advertencia --}}
            <div
                class="mb-6 flex size-14 shrink-0 items-center justify-center rounded-full bg-error-light"
                aria-label="{{ __('admin/centros-medicos.delete_modal.icon_aria') }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="text-error" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M3 6h18" />
                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                </svg>
            </div>

            {{-- Títulos y descripción --}}
            <div class="mb-8 flex flex-col gap-2">
                <h2 id="delete-centro-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                    {{ __('admin/centros-medicos.delete_modal.title') }}
                </h2>
                <p class="px-2 text-sm leading-relaxed text-neutral-600">
                    {{ __('admin/centros-medicos.delete_modal.description', ['nombre' => $deletingCentroNombre]) }}
                </p>
                @error('deleting')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Botones --}}
            <div class="flex w-full items-center gap-3">
                <button
                    type="button"
                    wire:click="closeDeleteModal"
                    class="flex-1 rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('admin/centros-medicos.delete_modal.cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="eliminar"
                    wire:loading.attr="disabled"
                    class="flex-[1.2] rounded-lg bg-error py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-error-text disabled:cursor-not-allowed disabled:opacity-70">
                    {{ __('admin/centros-medicos.delete_modal.confirm') }}
                </button>
            </div>
        </div>
    </div>
@endif
