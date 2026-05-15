@php
    use App\Enums\DispositivoEstado;
    $isEditing = $editingDispositivoId !== null;
    $modalTitle = $isEditing
        ? __('admin/dispositivos.edit_modal.title')
        : __('admin/dispositivos.create_modal.title');
    $modalSubtitle = $isEditing
        ? __('admin/dispositivos.edit_modal.subtitle')
        : __('admin/dispositivos.create_modal.subtitle');
    $submitLabel = $isEditing
        ? __('admin/dispositivos.edit_modal.submit')
        : __('admin/dispositivos.create_modal.submit');
@endphp

{{-- Overlay fijo sobre el viewport; el layout admin ya no usa overflow-y-auto en <main>. --}}
@if ($showCreateModal)
    <div
        wire:key="create-dispositivo-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeCreateModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="create-dispositivo-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-xl flex-col gap-5 rounded-3xl bg-neutral-0 p-7 shadow-elev-card">

            {{-- Cabecera --}}
            <div class="flex items-start justify-between">
                <div class="flex flex-col gap-1">
                    <h2 id="create-dispositivo-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                        {{ $modalTitle }}
                    </h2>
                    <p class="text-sm text-neutral-600">
                        {{ $modalSubtitle }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="closeCreateModal"
                    aria-label="{{ __('admin/dispositivos.create_modal.close_aria') }}"
                    class="flex size-7 shrink-0 items-center justify-center rounded-lg border border-neutral-200 text-neutral-600 transition-colors hover:bg-neutral-50">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="10.5" y1="3.5" x2="3.5" y2="10.5" />
                        <line x1="3.5" y1="3.5" x2="10.5" y2="10.5" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="guardar" class="flex flex-col gap-5">

                {{-- Identificación --}}
                <div class="flex flex-col gap-4">
                    <h3 class="text-sm font-semibold text-neutral-500">
                        {{ __('admin/dispositivos.create_modal.section_id') }}
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {{-- Modelo --}}
                        <div class="flex flex-col gap-2">
                            <label for="form_modelo_id" class="text-sm font-medium text-text">
                                {{ __('admin/dispositivos.create_modal.modelo_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    id="form_modelo_id"
                                    wire:model="form_modelo_id"
                                    class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-text shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                                    <option value="" disabled selected hidden>
                                        {{ __('admin/dispositivos.create_modal.modelo_placeholder') }}
                                    </option>
                                    @foreach ($modelos as $modeloOpt)
                                        <option value="{{ $modeloOpt->id }}">{{ $modeloOpt->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-600">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                </div>
                            </div>
                            @error('form_modelo_id')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Número de serie --}}
                        <div class="flex flex-col gap-2">
                            <label for="form_numero_serie" class="text-sm font-medium text-text">
                                {{ __('admin/dispositivos.create_modal.serie_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                id="form_numero_serie"
                                wire:model="form_numero_serie"
                                placeholder="SNX1-2025-0014"
                                class="w-full rounded-lg border border-neutral-200 bg-neutral-0 px-4 py-2.5 text-sm text-text placeholder-neutral-400 shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                            @error('form_numero_serie')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-1 border-t border-neutral-200">

                {{-- Asignación --}}
                <div class="flex flex-col gap-4">
                    <h3 class="text-sm font-semibold text-neutral-500">
                        {{ __('admin/dispositivos.create_modal.section_assign') }}
                    </h3>

                    {{-- Centro médico --}}
                    <div class="flex flex-col gap-2">
                        <label for="form_centro_medico_id" class="text-sm font-medium text-text">
                            {{ __('admin/dispositivos.create_modal.centro_label') }}
                        </label>
                        <div class="relative">
                            <select
                                id="form_centro_medico_id"
                                wire:model="form_centro_medico_id"
                                class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-neutral-600 shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                                <option value="">
                                    {{ __('admin/dispositivos.create_modal.centro_placeholder') }}
                                </option>
                                @foreach ($centros as $centroOpt)
                                    <option value="{{ $centroOpt->id }}">{{ $centroOpt->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-600">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-0.5 text-xs text-neutral-600">
                            {{ __('admin/dispositivos.create_modal.centro_help') }}
                        </p>
                        @error('form_centro_medico_id')
                            <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Estado inicial --}}
                    <div class="flex flex-col gap-2">
                        <label for="form_estado" class="text-sm font-medium text-text">
                            {{ __('admin/dispositivos.create_modal.estado_label') }}
                            <span class="text-error">*</span>
                        </label>
                        <div class="relative">
                            <select
                                id="form_estado"
                                wire:model="form_estado"
                                class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-text shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                                <option value="{{ DispositivoEstado::Inactivo->value }}">
                                    {{ __('admin/dispositivos.estado.inactivo') }}
                                </option>
                                <option value="{{ DispositivoEstado::Activo->value }}">
                                    {{ __('admin/dispositivos.estado.activo') }}
                                </option>
                                <option value="{{ DispositivoEstado::Mantenimiento->value }}">
                                    {{ __('admin/dispositivos.estado.mantenimiento') }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-600">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </div>
                        </div>
                        @error('form_estado')
                            <p class="text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="mt-2 flex items-center justify-end gap-3 border-t border-neutral-200 pt-6">
                    <button
                        type="button"
                        wire:click="closeCreateModal"
                        class="rounded-lg border border-neutral-200 bg-neutral-0 px-5 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                        {{ __('admin/dispositivos.create_modal.cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700">
                        @if (! $isEditing)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                        @endif
                        {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
