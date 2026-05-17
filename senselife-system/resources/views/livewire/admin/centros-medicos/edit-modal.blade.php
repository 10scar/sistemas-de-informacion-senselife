@php
    $inputBaseClass = 'w-full rounded-lg border border-neutral-300 bg-neutral-0 px-4 py-2.5 text-sm text-text placeholder-neutral-400 shadow-elev-control focus:border-accent-400 focus:outline-none focus:ring-2 focus:ring-accent-100';
    $selectBaseClass = 'w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-text shadow-elev-control focus:border-accent-400 focus:outline-none focus:ring-2 focus:ring-accent-100';
@endphp

@if ($showEditModal)
    <div
        wire:key="edit-centro-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeEditModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="edit-centro-title">
        <div
            wire:click.stop
            class="relative flex max-h-[90vh] w-full max-w-2xl flex-col gap-6 overflow-y-auto overscroll-contain rounded-3xl bg-neutral-0 p-7 shadow-elev-card">

            {{-- Cabecera --}}
            <div class="flex items-start justify-between">
                <h2 id="edit-centro-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                    {{ __('admin/centros-medicos.edit_modal.title') }}
                </h2>
                <button
                    type="button"
                    wire:click="closeEditModal"
                    aria-label="{{ __('admin/centros-medicos.create_modal.close_aria') }}"
                    class="flex size-7 shrink-0 items-center justify-center rounded-lg border border-neutral-200 text-neutral-600 transition-colors hover:bg-neutral-50">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="10.5" y1="3.5" x2="3.5" y2="10.5" />
                        <line x1="3.5" y1="3.5" x2="10.5" y2="10.5" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="actualizar" class="flex flex-col gap-5">

                <hr class="border-t border-neutral-200">

                {{-- Información básica --}}
                <div class="flex flex-col gap-4">
                    <h3 class="text-sm font-semibold text-neutral-500">
                        {{ __('admin/centros-medicos.create_modal.section_basic') }}
                    </h3>

                    {{-- Fila 1: Nombre + Departamento --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="edit_form_nombre" class="text-sm font-medium text-text">
                                {{ __('admin/centros-medicos.create_modal.nombre_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                id="edit_form_nombre"
                                wire:model="form_nombre"
                                placeholder="{{ __('admin/centros-medicos.create_modal.nombre_placeholder') }}"
                                class="{{ $inputBaseClass }}">
                            @error('form_nombre')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="edit_form_departamento_id" class="text-sm font-medium text-text">
                                {{ __('admin/centros-medicos.create_modal.departamento_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    id="edit_form_departamento_id"
                                    wire:model.live="form_departamento_id"
                                    class="{{ $selectBaseClass }}">
                                    <option value="" disabled hidden>
                                        {{ __('admin/centros-medicos.create_modal.select_placeholder') }}
                                    </option>
                                    @foreach ($departamentos as $depto)
                                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-600">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                </div>
                            </div>
                            @error('form_departamento_id')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Fila 2: Ciudad + Dirección --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="edit_form_municipio_id" class="text-sm font-medium text-text">
                                {{ __('admin/centros-medicos.create_modal.ciudad_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    id="edit_form_municipio_id"
                                    wire:model="form_municipio_id"
                                    @disabled($form_departamento_id === null)
                                    class="{{ $selectBaseClass }} disabled:cursor-not-allowed disabled:bg-neutral-50 disabled:opacity-70">
                                    <option value="" disabled hidden>
                                        {{ $form_departamento_id === null
                                            ? __('admin/centros-medicos.create_modal.municipio_disabled')
                                            : __('admin/centros-medicos.create_modal.select_placeholder') }}
                                    </option>
                                    @foreach ($municipios as $municipio)
                                        <option value="{{ $municipio->id }}">{{ $municipio->name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-600">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                </div>
                            </div>
                            @error('form_municipio_id')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="edit_form_direccion" class="text-sm font-medium text-text">
                                {{ __('admin/centros-medicos.create_modal.direccion_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                id="edit_form_direccion"
                                wire:model="form_direccion"
                                placeholder="{{ __('admin/centros-medicos.create_modal.direccion_placeholder') }}"
                                class="{{ $inputBaseClass }}">
                            @error('form_direccion')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Fila 3: Registro médico + Fecha --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="edit_form_registro_medico" class="text-sm font-medium text-text">
                                {{ __('admin/centros-medicos.create_modal.registro_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                id="edit_form_registro_medico"
                                wire:model="form_registro_medico"
                                placeholder="{{ __('admin/centros-medicos.create_modal.registro_placeholder') }}"
                                class="{{ $inputBaseClass }}">
                            <p class="mt-0.5 text-xs text-neutral-600">
                                {{ __('admin/centros-medicos.create_modal.registro_help') }}
                            </p>
                            @error('form_registro_medico')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="edit_form_fecha_vinculacion" class="text-sm font-medium text-text">
                                {{ __('admin/centros-medicos.create_modal.fecha_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <input
                                type="date"
                                id="edit_form_fecha_vinculacion"
                                wire:model="form_fecha_vinculacion"
                                class="{{ $inputBaseClass }} pr-10">
                            @error('form_fecha_vinculacion')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-1 border-t border-neutral-200">

                {{-- Información de contacto --}}
                <div class="flex flex-col gap-4">
                    <h3 class="text-sm font-semibold text-neutral-500">
                        {{ __('admin/centros-medicos.create_modal.section_contact') }}
                    </h3>

                    {{-- Fila 4: Correo + Teléfono --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="edit_form_correo" class="text-sm font-medium text-text">
                                {{ __('admin/centros-medicos.create_modal.correo_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <input
                                type="email"
                                id="edit_form_correo"
                                wire:model="form_correo"
                                placeholder="{{ __('admin/centros-medicos.create_modal.correo_placeholder') }}"
                                class="{{ $inputBaseClass }}">
                            @error('form_correo')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="edit_form_contacto_celular" class="text-sm font-medium text-text">
                                {{ __('admin/centros-medicos.create_modal.telefono_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <input
                                type="tel"
                                id="edit_form_contacto_celular"
                                wire:model="form_contacto_celular"
                                placeholder="{{ __('admin/centros-medicos.create_modal.telefono_placeholder') }}"
                                class="{{ $inputBaseClass }}">
                            @error('form_contacto_celular')
                                <p class="text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="mt-2 flex items-center justify-end gap-3 border-t border-neutral-200 pt-6">
                    <button
                        type="button"
                        wire:click="closeEditModal"
                        class="rounded-lg border border-neutral-200 bg-neutral-0 px-5 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                        {{ __('admin/centros-medicos.create_modal.cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        {{ __('admin/centros-medicos.edit_modal.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
