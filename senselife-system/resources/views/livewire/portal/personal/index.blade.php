@php
    $cardClass = 'rounded-2xl border border-neutral-200 bg-neutral-0 shadow-elev-control';
@endphp

<div class="flex flex-col gap-8 p-6 pb-12 text-text md:p-8">
    <header class="flex flex-col gap-1">
        <h1 class="font-display text-3xl font-bold tracking-tight text-text">
            {{ __('portal/personal.title') }}
        </h1>
        <p class="text-sm font-medium text-neutral-600">
            {{ __('portal/personal.subtitle', ['centro' => $centro->nombre]) }}
        </p>
    </header>

    <section class="flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-lg font-bold text-text">
                    {{ __('admin/centros-medicos.show.personal.title') }}
                </h2>
                <div class="relative min-w-[180px]">
                    <select
                        wire:model.live="filtroPersonalEstado"
                        class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2 pl-3 pr-9 text-[13px] text-text shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                        <option value="activos">{{ __('admin/centros-medicos.show.personal.filter_activos') }}</option>
                        <option value="inactivos">{{ __('admin/centros-medicos.show.personal.filter_inactivos') }}</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                        <svg class="size-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </div>
                </div>
            </div>
            <button
                type="button"
                wire:click="openAddMedicoModal"
                class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-neutral-0 px-3 py-1.5 text-[13px] font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                {{ __('admin/centros-medicos.show.personal.cta_add') }}
            </button>
        </div>

        <div class="overflow-hidden {{ $cardClass }}">
            <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
                <table class="w-full min-w-[900px] border-collapse whitespace-nowrap text-left">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-neutral-50/50 text-[13px] font-medium text-neutral-600">
                            <th class="w-[25%] px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.personal.th_nombre') }}</th>
                            <th class="w-[20%] px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.personal.th_especialidad') }}</th>
                            <th class="w-[20%] px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.personal.th_telefono') }}</th>
                            <th class="w-[25%] px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.personal.th_correo') }}</th>
                            <th class="w-[10%] px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.personal.th_acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-[13px]">
                        @forelse ($medicos as $medico)
                            @php
                                $nombreCompleto = trim(($medico->nombre ?? '').' '.($medico->apellido ?? ''));
                                $correoMedico = $medico->user?->email;
                            @endphp
                            <tr
                                wire:key="portal-personal-{{ $medico->id }}"
                                class="group border-b border-neutral-100 transition-colors last:border-b-0 hover:bg-neutral-50">
                                <td class="px-5 py-3.5 font-medium text-text">{{ $nombreCompleto !== '' ? $nombreCompleto : __('admin/centros-medicos.show.personal.sin_dato') }}</td>
                                <td class="px-5 py-3.5 text-neutral-600">{{ $medico->especialidad ?: __('admin/centros-medicos.show.personal.sin_dato') }}</td>
                                <td class="px-5 py-3.5 text-text">{{ $medico->contacto ?: __('admin/centros-medicos.show.personal.sin_dato') }}</td>
                                <td class="px-5 py-3.5 text-neutral-600">{{ $correoMedico ?: __('admin/centros-medicos.show.personal.sin_dato') }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2 opacity-80 transition-opacity group-hover:opacity-100">
                                        @if ($mostrandoPersonalInactivos)
                                            <button
                                                type="button"
                                                wire:click="openEditPersonalModal({{ $medico->id }})"
                                                class="rounded px-2 py-1 font-medium text-primary-600 transition-colors hover:bg-accent-50">
                                                {{ __('admin/centros-medicos.show.actions.edit') }}
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="reactivarPersonal({{ $medico->id }})"
                                                wire:confirm="{{ __('admin/centros-medicos.show.personal.reactivate_confirm') }}"
                                                class="rounded px-2 py-1 font-medium text-success transition-colors hover:bg-success-light">
                                                {{ __('admin/centros-medicos.show.personal.reactivate') }}
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="openEditPersonalModal({{ $medico->id }})"
                                                class="rounded px-2 py-1 font-medium text-primary-600 transition-colors hover:bg-accent-50">
                                                {{ __('admin/centros-medicos.show.actions.edit') }}
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="openDesactivarPersonalModal({{ $medico->id }})"
                                                class="rounded px-2 py-1 font-medium text-error transition-colors hover:bg-error-light">
                                                {{ __('admin/centros-medicos.show.personal.deactivate') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-neutral-500">
                                    @if ($mostrandoPersonalInactivos)
                                        {{ __('admin/centros-medicos.show.personal.empty_inactivos') }}
                                    @else
                                        {{ __('admin/centros-medicos.show.personal.empty') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    @include('livewire.admin.centros-medicos.agregar-medico-modal', ['mostrarCentroEnModal' => false])
    @include('livewire.admin.centros-medicos.editar-personal-modal')
    @include('livewire.admin.centros-medicos.desactivar-personal-modal')
    @include('livewire.admin.centros-medicos.success-modal')
</div>
