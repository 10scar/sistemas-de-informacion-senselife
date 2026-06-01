@if ($showCreateModal)
    <div
        wire:key="portal-create-paciente-modal"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-text/40 p-4 sm:p-6"
        wire:click.self="closeCreateModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="portal-create-paciente-title">
        <div
            wire:click.stop
            class="my-auto flex w-[min(100%,520px)] shrink-0 flex-col overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-0 shadow-elev-card max-h-[min(calc(100vh-2rem),720px)]">

            <div class="flex shrink-0 items-center gap-3 border-b border-neutral-100 bg-neutral-0 px-6 py-5">
                <h2 id="portal-create-paciente-title" class="text-lg font-semibold tracking-tight text-text">
                    {{ __('portal/pacientes.create_modal.title') }}
                </h2>
            </div>

            <form wire:submit.prevent="guardar" class="flex min-h-0 flex-1 flex-col overflow-hidden">
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-6 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-neutral-300">
                    @include('livewire.portal.pacientes.partials.paciente-form-fields', [
                        'fieldPrefix' => 'create_',
                        'dispositivosOpciones' => $dispositivosDisponibles,
                    ])
                </div>

                <div
                    class="flex shrink-0 items-center justify-end gap-3 border-t border-neutral-100 bg-neutral-50 px-6 py-4">
                    <button
                        type="button"
                        wire:click="closeCreateModal"
                        class="rounded-lg border border-neutral-300 bg-neutral-0 px-5 py-2.5 text-sm font-medium text-neutral-700 shadow-elev-control transition hover:bg-neutral-50 hover:text-text">
                        {{ __('portal/pacientes.create_modal.cancel') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-60">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('portal/pacientes.create_modal.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
