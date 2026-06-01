<div wire:poll.5s class="w-full">
    @if (! $centro)
        <div class="rounded-2xl border border-neutral-200 bg-neutral-0 p-8 text-center text-neutral-600 shadow-elev-card">
            {{ __('portal/pacientes.no_centro') }}
        </div>
    @else
        <div class="bg-neutral-0 p-6 md:p-8">
            <header class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-start">
                <div>
                    <h1 class="font-display text-3xl font-bold tracking-tight text-text">
                        {{ __('portal/pacientes.title') }}
                    </h1>
                    <p class="mt-1 font-medium text-neutral-600">
                        {{ __('portal/pacientes.subtitle', ['centro' => $centro->nombre, 'count' => $totalPacientes]) }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="relative min-w-[170px]">
                        <select
                            wire:model.live="filtroListado"
                            class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2 pl-3 pr-9 text-sm text-text shadow-elev-control focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25">
                            <option value="activos">{{ __('portal/pacientes.filter_activos') }}</option>
                            <option value="historial">{{ __('portal/pacientes.filter_historial') }}</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                            <svg class="size-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </div>
                    </div>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-400"
                            aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('portal/pacientes.search_placeholder') }}"
                            class="block w-64 rounded-lg border border-neutral-300 bg-neutral-0 py-2 pl-10 pr-3 text-sm text-text placeholder:text-neutral-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                    </div>

                    <button
                        type="button"
                        wire:click="openCreateModal"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-neutral-0 shadow-elev-control transition hover:bg-primary-700">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('portal/pacientes.create_button') }}
                    </button>
                </div>
            </header>

            @if ($pacientes->isEmpty())
                <p class="py-12 text-center text-neutral-600">{{ __('portal/pacientes.empty') }}</p>
            @else
                @if ($telemetriaSinConexion)
                    <div class="mb-4 rounded-xl border border-error-border bg-error-light px-4 py-3 text-sm font-medium text-error-text">
                        {{ __('portal/pacientes.telemetria_sin_conexion') }}
                    </div>
                @endif

                <div class="overflow-hidden rounded-2xl bg-neutral-0">
                    <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
                        <table class="w-full min-w-[980px] border-collapse text-left">
                            <thead>
                                <tr class="border-b border-neutral-200 bg-neutral-50/50 text-[13px] font-medium text-neutral-600">
                                    <th class="w-[35%] px-5 py-3.5 font-medium">{{ __('portal/pacientes.show.th_paciente') }}</th>
                                    <th class="w-[30%] px-5 py-3.5 font-medium">{{ __('portal/pacientes.show.th_ubicacion') }}</th>
                                    <th class="w-[20%] px-5 py-3.5 font-medium">{{ __('portal/pacientes.show.th_informacion') }}</th>
                                    <th class="w-[15%] px-5 py-3.5 font-medium">{{ __('portal/pacientes.show.th_acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-[13px]">
                                @foreach ($pacientes as $paciente)
                                    @php
                                        $asociacion = $paciente->asociacionActiva;
                                        $dispositivo = $asociacion?->dispositivo;
                                        $ubicacion = $dispositivo?->ubicacion;
                                        $serie = $dispositivo?->numero_serie;
                                        $alertasActivas = (int) ($paciente->alertas_activas_count ?? 0);
                                        $puedeDesactivar = (bool) $paciente->activo;
                                        $indices = $indicesTiempoReal[(string) $paciente->id] ?? null;
                                    @endphp
                                    <tr
                                        wire:key="paciente-row-{{ $paciente->id }}"
                                        wire:click="redirectToPaciente('{{ $paciente->id }}')"
                                        class="group cursor-pointer border-b border-neutral-100 transition-colors last:border-b-0 hover:bg-neutral-50">
                                        <td class="px-5 py-3.5">
                                            <p class="font-semibold text-text">{{ $paciente->nombre_completo }}</p>
                                            <p class="text-neutral-600">
                                                {{ $paciente->sexo === \App\Enums\Sexo::F ? __('portal/pacientes.sex_f') : __('portal/pacientes.sex_m') }}
                                                <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                                                {{ number_format((float) $paciente->peso, 1) }} kg
                                                <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                                                {{ number_format((float) $paciente->altura, 0) }} cm
                                                @if ($paciente->edad_dias !== null)
                                                    <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                                                    {{ trans_choice('portal/pacientes.age_days', $paciente->edad_dias, ['count' => $paciente->edad_dias]) }}
                                                @endif
                                            </p>
                                        </td>
                                        <td class="px-5 py-3.5 text-neutral-600">
                                            @if ($ubicacion && $serie)
                                                {{ $ubicacion }} <span class="text-neutral-300" aria-hidden="true">&bull;</span> {{ $serie }}
                                            @elseif ($ubicacion || $serie)
                                                {{ $ubicacion ?? $serie }}
                                            @else
                                                {{ __('portal/pacientes.unassigned_location') }}
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-5">
                                                <div class="min-w-[48px] text-center">
                                                    <div class="text-lg font-bold leading-none text-text">
                                                        {{ $indices ? number_format($indices['fc'], 0) : __('portal/pacientes.vital_placeholder') }}
                                                    </div>
                                                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-neutral-500">bpm</div>
                                                </div>
                                                <div class="min-w-[48px] text-center">
                                                    <div class="text-lg font-bold leading-none text-text">
                                                        {{ $indices ? number_format($indices['fr'], 0) : __('portal/pacientes.vital_placeholder') }}
                                                    </div>
                                                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-neutral-500">rpm</div>
                                                </div>
                                                @if ($alertasActivas > 0)
                                                    <button
                                                        type="button"
                                                        wire:click.stop="openAlertasModal('{{ $paciente->id }}')"
                                                        class="inline-flex items-center gap-1.5 rounded px-2 py-1 font-medium text-warning-text transition-colors hover:bg-warning-light"
                                                        aria-label="{{ __('portal/pacientes.show.alert_icon_aria') }}">
                                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                        </svg>
                                                        <span class="text-xs">{{ $alertasActivas }}</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-2 opacity-80 transition-opacity group-hover:opacity-100">
                                                <button
                                                    type="button"
                                                    wire:click.stop="openEditModal('{{ $paciente->id }}')"
                                                    class="rounded px-2 py-1 font-medium text-primary-600 transition-colors hover:bg-accent-50">
                                                    {{ __('portal/pacientes.edit_modal.action_edit') }}
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click.stop="desactivarPaciente('{{ $paciente->id }}')"
                                                    wire:confirm="{{ __('portal/pacientes.confirm_desactivar') }}"
                                                    @disabled(! $puedeDesactivar)
                                                    class="rounded px-2 py-1 font-medium text-error transition-colors hover:bg-error-light disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-transparent">
                                                    {{ __('portal/pacientes.action_desactivar') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($pacientes->hasPages())
                    <div class="mt-6 border-t border-neutral-200 pt-4">
                        {{ $pacientes->links() }}
                    </div>
                @endif
            @endif
        </div>

        @include('livewire.portal.pacientes.create-modal')
        @include('livewire.portal.pacientes.edit-modal')
        @include('livewire.portal.pacientes.success-modal')
        @include('livewire.portal.pacientes.alertas-modal')
    @endif
</div>
