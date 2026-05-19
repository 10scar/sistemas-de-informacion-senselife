@php
    $inputBaseClass = 'w-full rounded-lg border border-neutral-300 bg-neutral-0 px-4 py-2.5 text-sm text-text placeholder-neutral-400 shadow-elev-control focus:border-accent-400 focus:outline-none focus:ring-2 focus:ring-accent-100';
    $selectBaseClass = 'w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-text shadow-elev-control focus:border-accent-400 focus:outline-none focus:ring-2 focus:ring-accent-100';
    $langPrefix = 'admin/centros-medicos.show.'.$modalLang;
    $fieldPrefix = $fieldIdPrefix ?? '';
    $columns = (int) ($columns ?? 2);
@endphp

<div class="flex w-full min-w-0 flex-col gap-5">

    {{-- Información básica --}}
    <div class="flex w-full flex-col gap-4">
        <h3 class="text-sm font-semibold text-neutral-500">
            {{ __($langPrefix.'.section_basic') }}
        </h3>

        @if ($columns === 2)
        <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2">
        @else
        <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-3">
        @endif
            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_nombre" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.nombre_label') }}
                    <span class="text-error">*</span>
                </label>
                <input
                    type="text"
                    id="{{ $fieldPrefix }}form_med_nombre"
                    wire:model="form_med_nombre"
                    placeholder="{{ __($langPrefix.'.nombre_placeholder') }}"
                    class="{{ $inputBaseClass }}">
                @error('form_med_nombre')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_apellido" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.apellido_label') }}
                    <span class="text-error">*</span>
                </label>
                <input
                    type="text"
                    id="{{ $fieldPrefix }}form_med_apellido"
                    wire:model="form_med_apellido"
                    placeholder="{{ __($langPrefix.'.apellido_placeholder') }}"
                    class="{{ $inputBaseClass }}">
                @error('form_med_apellido')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_especialidad" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.especialidad_label') }}
                    <span class="text-error">*</span>
                </label>
                <input
                    type="text"
                    id="{{ $fieldPrefix }}form_med_especialidad"
                    wire:model="form_med_especialidad"
                    placeholder="{{ __($langPrefix.'.especialidad_placeholder') }}"
                    class="{{ $inputBaseClass }}">
                @error('form_med_especialidad')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_sub_especialidad" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.subespecialidad_label') }}
                </label>
                <input
                    type="text"
                    id="{{ $fieldPrefix }}form_med_sub_especialidad"
                    wire:model="form_med_sub_especialidad"
                    placeholder="{{ __($langPrefix.'.subespecialidad_placeholder') }}"
                    class="{{ $inputBaseClass }}">
                @error('form_med_sub_especialidad')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_registro_medico" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.registro_label') }}
                    <span class="text-error">*</span>
                </label>
                <input
                    type="text"
                    id="{{ $fieldPrefix }}form_med_registro_medico"
                    wire:model="form_med_registro_medico"
                    placeholder="{{ __($langPrefix.'.registro_placeholder') }}"
                    class="{{ $inputBaseClass }}">
                <p class="text-xs text-neutral-600">
                    {{ __($langPrefix.'.registro_help') }}
                </p>
                @error('form_med_registro_medico')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <hr class="w-full border-t border-neutral-200">

    {{-- Contacto --}}
    <div class="flex w-full flex-col gap-4">
        <h3 class="text-sm font-semibold text-neutral-500">
            {{ __($langPrefix.'.section_contact') }}
        </h3>

        @if ($columns === 2)
        <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2">
        @else
        <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-3">
        @endif
            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_correo" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.correo_label') }}
                    <span class="text-error">*</span>
                </label>
                <input
                    type="email"
                    id="{{ $fieldPrefix }}form_med_correo"
                    wire:model="form_med_correo"
                    placeholder="{{ __($langPrefix.'.correo_placeholder') }}"
                    class="{{ $inputBaseClass }}">
                @error('form_med_correo')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_contacto" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.telefono_label') }}
                    <span class="text-error">*</span>
                </label>
                <input
                    type="tel"
                    id="{{ $fieldPrefix }}form_med_contacto"
                    wire:model="form_med_contacto"
                    placeholder="{{ __($langPrefix.'.telefono_placeholder') }}"
                    class="{{ $inputBaseClass }}">
                @error('form_med_contacto')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <hr class="w-full border-t border-neutral-200">

    {{-- Acceso al sistema --}}
    <div class="flex w-full flex-col gap-4">
        <h3 class="text-sm font-semibold text-neutral-500">
            {{ __($langPrefix.'.section_access') }}
        </h3>

        @if ($columns === 2)
        <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2">
        @else
        <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-3">
        @endif
            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_rol_id" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.rol_label') }}
                    <span class="text-error">*</span>
                </label>
                <div class="relative w-full">
                    <select
                        id="{{ $fieldPrefix }}form_med_rol_id"
                        wire:model="form_med_rol_id"
                        class="{{ $selectBaseClass }}">
                        <option value="" disabled hidden>
                            {{ __($langPrefix.'.select_placeholder') }}
                        </option>
                        @foreach ($rolesMedico as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-600">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </div>
                </div>
                @error('form_med_rol_id')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_password" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.password_label') }}
                    @if (! $isEdit)
                        <span class="text-error">*</span>
                    @endif
                </label>
                <input
                    type="password"
                    id="{{ $fieldPrefix }}form_med_password"
                    wire:model="form_med_password"
                    autocomplete="new-password"
                    class="{{ $inputBaseClass }}">
                @if ($isEdit)
                    <p class="text-xs text-neutral-600">
                        {{ __($langPrefix.'.password_help') }}
                    </p>
                @endif
                @error('form_med_password')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex min-w-0 flex-col gap-2">
                <label for="{{ $fieldPrefix }}form_med_password_confirmation" class="text-sm font-medium text-text">
                    {{ __($langPrefix.'.password_confirm_label') }}
                    @if (! $isEdit)
                        <span class="text-error">*</span>
                    @endif
                </label>
                <input
                    type="password"
                    id="{{ $fieldPrefix }}form_med_password_confirmation"
                    wire:model="form_med_password_confirmation"
                    autocomplete="new-password"
                    class="{{ $inputBaseClass }}">
            </div>
        </div>
    </div>
</div>
