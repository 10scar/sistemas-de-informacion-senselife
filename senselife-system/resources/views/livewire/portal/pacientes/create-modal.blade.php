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
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col gap-1.5">
                        <label for="form_nombre_completo" class="text-sm font-medium text-text">
                            {{ __('portal/pacientes.create_modal.nombre_label') }}
                            <span class="text-error">*</span>
                        </label>
                        <input
                            id="form_nombre_completo"
                            type="text"
                            wire:model="form_nombre_completo"
                            placeholder="{{ __('portal/pacientes.create_modal.nombre_placeholder') }}"
                            class="w-full rounded-lg border border-neutral-300 bg-neutral-0 px-3 py-2.5 text-sm text-text shadow-elev-control placeholder:text-neutral-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                        @error('form_nombre_completo')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <label for="form_fecha_nacimiento" class="text-sm font-medium text-text">
                                {{ __('portal/pacientes.create_modal.fecha_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <input
                                id="form_fecha_nacimiento"
                                type="date"
                                wire:model="form_fecha_nacimiento"
                                max="{{ date('Y-m-d') }}"
                                class="w-full rounded-lg border border-neutral-300 bg-neutral-0 px-3 py-2.5 text-sm text-text shadow-elev-control focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                            @error('form_fecha_nacimiento')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="form_sexo" class="text-sm font-medium text-text">
                                {{ __('portal/pacientes.create_modal.sexo_label') }}
                                <span class="text-error">*</span>
                            </label>
                            <div class="relative shadow-elev-control">
                                <select
                                    id="form_sexo"
                                    wire:model="form_sexo"
                                    class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-3 pr-10 text-sm text-text focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25">
                                    <option value="">{{ __('portal/pacientes.create_modal.sexo_placeholder') }}</option>
                                    <option value="M">{{ __('portal/pacientes.create_modal.sexo_m') }}</option>
                                    <option value="F">{{ __('portal/pacientes.create_modal.sexo_f') }}</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-neutral-400"
                                    aria-hidden="true">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            @error('form_sexo')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="form_nuip" class="text-sm font-medium text-text">
                            {{ __('portal/pacientes.create_modal.nuip_label') }}
                        </label>
                        <input
                            id="form_nuip"
                            type="text"
                            wire:model="form_nuip"
                            placeholder="{{ __('portal/pacientes.create_modal.nuip_placeholder') }}"
                            class="w-full rounded-lg border border-neutral-300 bg-neutral-0 px-3 py-2.5 text-sm text-text shadow-elev-control placeholder:text-neutral-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                        @error('form_nuip')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="form_dispositivo_id" class="text-sm font-medium text-text">
                            {{ __('portal/pacientes.create_modal.dispositivo_label') }}
                        </label>
                        <div class="relative shadow-elev-control">
                            <select
                                id="form_dispositivo_id"
                                wire:model="form_dispositivo_id"
                                class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-3 pr-10 text-sm text-text focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25">
                                <option value="">{{ __('portal/pacientes.create_modal.dispositivo_placeholder') }}</option>
                                @foreach ($dispositivosDisponibles as $dispositivo)
                                    <option value="{{ $dispositivo->id }}">
                                        {{ $dispositivo->numero_serie }}@if ($dispositivo->ubicacion) — {{ $dispositivo->ubicacion }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-neutral-400"
                                aria-hidden="true">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-0.5 text-[13px] text-neutral-500">
                            {{ __('portal/pacientes.create_modal.dispositivo_hint') }}
                        </p>
                        @error('form_dispositivo_id')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-2 flex flex-col gap-4 rounded-xl border border-info-border bg-info-light p-5">
                        <div class="flex items-start gap-3">
                            <input
                                id="form_consentimiento"
                                type="checkbox"
                                wire:model="form_consentimiento"
                                class="mt-0.5 size-[18px] cursor-pointer rounded border-neutral-300 text-primary-600 focus:ring-primary-500" />
                            <div class="flex flex-col">
                                <label for="form_consentimiento" class="cursor-pointer text-sm font-medium text-text">
                                    {{ __('portal/pacientes.create_modal.consent_label') }}
                                    <span class="text-error">*</span>
                                </label>
                                <p class="mt-0.5 text-[13px] text-neutral-500">
                                    {{ __('portal/pacientes.create_modal.consent_hint') }}
                                </p>
                            </div>
                        </div>
                        <div class="pl-[30px]">
                            <div class="relative max-w-xs shadow-elev-control">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-400"
                                    aria-hidden="true">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    wire:model="form_tutor_identificacion"
                                    placeholder="{{ __('portal/pacientes.create_modal.tutor_placeholder') }}"
                                    class="w-full rounded-lg border border-info-border/50 bg-neutral-0 py-2 pl-9 pr-3 text-sm text-text placeholder:text-neutral-400 focus:border-info focus:outline-none focus:ring-2 focus:ring-info-border/40" />
                            </div>
                            @error('form_tutor_identificacion')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror
                            @error('form_consentimiento')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
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
