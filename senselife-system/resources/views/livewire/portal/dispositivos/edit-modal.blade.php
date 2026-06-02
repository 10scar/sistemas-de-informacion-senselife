@php
    use App\Enums\DispositivoEstado;
@endphp

@if ($showEditModal)
    <div
        wire:key="portal-edit-dispositivo-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeEditModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="portal-edit-dispositivo-title">
        <div
            wire:click.stop
            class="relative flex max-h-[90vh] w-full max-w-xl flex-col gap-5 overflow-y-auto overscroll-contain rounded-3xl bg-neutral-0 p-7 shadow-elev-card">

            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 flex-col gap-1">
                    <h2 id="portal-edit-dispositivo-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                        {{ __('portal/dispositivos.edit_modal.title') }}
                    </h2>
                    <p class="text-sm text-neutral-600">
                        {{ __('portal/dispositivos.edit_modal.subtitle') }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="closeEditModal"
                    aria-label="{{ __('portal/dispositivos.edit_modal.close_aria') }}"
                    class="flex size-7 shrink-0 items-center justify-center rounded-lg border border-neutral-200 text-neutral-600 transition-colors hover:bg-neutral-50">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="10.5" y1="3.5" x2="3.5" y2="10.5" />
                        <line x1="3.5" y1="3.5" x2="10.5" y2="10.5" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="actualizarDispositivo" class="flex flex-col gap-5">
                <div class="flex flex-col gap-4">
                    <h3 class="text-sm font-semibold text-neutral-500">
                        {{ __('portal/dispositivos.edit_modal.section_id') }}
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-sm font-medium text-neutral-500">
                                {{ __('portal/dispositivos.edit_modal.modelo_label') }}
                            </span>
                            <p class="rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-2.5 text-sm font-semibold text-text">
                                {{ $edit_modelo_nombre !== '' ? $edit_modelo_nombre : '—' }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <span class="text-sm font-medium text-neutral-500">
                                {{ __('portal/dispositivos.edit_modal.serie_label') }}
                            </span>
                            <p class="rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-2.5 text-sm font-semibold text-text">
                                {{ $edit_numero_serie }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="edit_ubicacion" class="text-sm font-medium text-text">
                            {{ __('portal/dispositivos.edit_modal.ubicacion_label') }}
                        </label>
                        <input
                            type="text"
                            id="edit_ubicacion"
                            wire:model="edit_ubicacion"
                            placeholder="{{ __('portal/dispositivos.edit_modal.ubicacion_placeholder') }}"
                            class="w-full rounded-lg border border-neutral-200 bg-neutral-0 px-4 py-2.5 text-sm text-text placeholder-neutral-400 shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                        @error('edit_ubicacion')
                            <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="border-t border-neutral-200">

                <div class="flex flex-col gap-4">
                    <h3 class="text-sm font-semibold text-neutral-500">
                        {{ __('portal/dispositivos.edit_modal.section_estado') }}
                    </h3>

                    <div class="flex flex-col gap-2">
                        <label for="edit_estado" class="text-sm font-medium text-text">
                            {{ __('portal/dispositivos.edit_modal.estado_label') }}
                            <span class="text-error">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="edit_estado"
                                wire:model="edit_estado"
                                class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-text shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                                <option value="{{ DispositivoEstado::Inactivo->value }}">
                                    {{ __('portal/dispositivos.estado.inactivo') }}
                                </option>
                                <option value="{{ DispositivoEstado::Activo->value }}">
                                    {{ __('portal/dispositivos.estado.activo') }}
                                </option>
                                <option value="{{ DispositivoEstado::Mantenimiento->value }}">
                                    {{ __('portal/dispositivos.estado.mantenimiento') }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-600">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </div>
                        </div>
                        @error('edit_estado')
                            <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-neutral-200 pt-6">
                    <button
                        type="button"
                        wire:click="closeEditModal"
                        class="rounded-lg border border-neutral-200 bg-neutral-0 px-5 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                        {{ __('portal/dispositivos.edit_modal.cancel') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-70">
                        {{ __('portal/dispositivos.edit_modal.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
