@php
    $idCentroFormat = '#'.str_pad((string) $centro->id, 3, '0', STR_PAD_LEFT);
@endphp

@if ($showAddMedicoModal)
    <div
        wire:key="agregar-medico-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeAddMedicoModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="agregar-medico-title">
        <div
            wire:click.stop
            class="relative flex max-h-[90vh] w-full max-w-4xl min-h-0 min-w-0 flex-col overflow-hidden rounded-3xl bg-neutral-0 shadow-elev-card">

            {{-- Cabecera fija --}}
            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-neutral-200 px-8 py-6">
                <h2 id="agregar-medico-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                    {{ __('admin/centros-medicos.show.agregar_medico_modal.title') }}
                </h2>
                <button
                    type="button"
                    wire:click="closeAddMedicoModal"
                    aria-label="{{ __('admin/centros-medicos.show.agregar_medico_modal.close_aria') }}"
                    class="flex size-7 shrink-0 items-center justify-center rounded-lg border border-neutral-200 text-neutral-600 transition-colors hover:bg-neutral-50">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="10.5" y1="3.5" x2="3.5" y2="10.5" />
                        <line x1="3.5" y1="3.5" x2="10.5" y2="10.5" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="guardarMedico" class="flex min-h-0 min-w-0 flex-1 flex-col">

                {{-- Cuerpo con scroll --}}
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-8 py-5">
                    <div class="flex flex-col gap-5">
                        <div class="flex flex-col gap-2">
                            <span class="text-sm font-semibold text-neutral-500">
                                {{ __('admin/centros-medicos.show.agregar_medico_modal.centro_label') }}
                            </span>
                            <div class="flex items-center gap-3 rounded-lg border border-accent-100 bg-accent-50 px-4 py-3">
                                <div class="shrink-0 text-accent-500">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                        <polyline points="9 22 9 12 15 12 15 22" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-primary-600">{{ $centro->nombre }}</span>
                                <span class="ml-auto rounded border border-accent-100 bg-neutral-0 px-2 py-0.5 text-[11px] font-bold tracking-wide text-primary-600">
                                    {{ $idCentroFormat }}
                                </span>
                            </div>
                            <p class="text-xs text-neutral-600">
                                {{ __('admin/centros-medicos.show.agregar_medico_modal.centro_help') }}
                            </p>
                        </div>

                        <hr class="border-t border-neutral-200">

                        @include('livewire.admin.centros-medicos.partials.personal-form-fields', [
                            'modalLang' => 'agregar_medico_modal',
                            'isEdit' => false,
                            'fieldIdPrefix' => '',
                        ])
                    </div>
                </div>

                {{-- Pie fijo --}}
                <div class="flex shrink-0 items-center justify-end gap-3 border-t border-neutral-200 bg-neutral-0 px-8 py-5">
                    <button
                        type="button"
                        wire:click="closeAddMedicoModal"
                        class="rounded-lg border border-neutral-200 bg-neutral-0 px-5 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                        {{ __('admin/centros-medicos.show.agregar_medico_modal.cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        {{ __('admin/centros-medicos.show.agregar_medico_modal.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
