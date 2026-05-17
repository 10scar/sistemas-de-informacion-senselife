@php
    use App\Enums\CentroEstado;
    use App\Enums\DispositivoEstado;

    $estadoValor = $centro->estado instanceof CentroEstado
        ? $centro->estado->value
        : (string) $centro->estado;

    $badgeCentro = match ($estadoValor) {
        CentroEstado::Activo->value => [
            'wrap'  => 'bg-success-light text-success-text border-success-mid',
            'dot'   => 'bg-success',
            'label' => __('admin/centros-medicos.estado.activo'),
        ],
        default => [
            'wrap'  => 'bg-neutral-100 text-neutral-600 border-neutral-300',
            'dot'   => 'bg-neutral-600',
            'label' => __('admin/centros-medicos.estado.inactivo'),
        ],
    };

    $ciudad = $centro->municipio?->name ?? '—';
    $departamento = $centro->departamento?->nombre ?? '—';
    $idCentroFormat = '#'.str_pad((string) $centro->id, 3, '0', STR_PAD_LEFT);

    $cardClass = 'rounded-2xl border border-neutral-200 bg-neutral-0 shadow-elev-control';
@endphp

<div class="flex flex-col gap-8 pb-12 text-text">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
        <div class="flex flex-col gap-1.5">
            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-sm font-medium text-neutral-600">
                <a
                    href="{{ route('admin.centros-medicos.index') }}"
                    wire:navigate
                    class="transition-colors hover:text-primary-600">
                    {{ __('admin/centros-medicos.show.breadcrumb_parent') }}
                </a>
                <span class="text-neutral-400">›</span>
                <span class="text-text">{{ $centro->nombre }}</span>
            </div>

            {{-- Título y subtítulo --}}
            <h1 class="mt-1 font-display text-3xl font-bold text-text">{{ $centro->nombre }}</h1>
            <p class="flex items-center gap-1.5 text-sm text-neutral-600">
                <span>{{ $ciudad }}, {{ $departamento }}</span>
                @if ($centro->contacto_celular)
                    <span class="font-bold text-neutral-400">·</span>
                    <span>{{ $centro->contacto_celular }}</span>
                @endif
            </p>
        </div>

        {{-- Acciones --}}
        <div class="flex shrink-0 items-center gap-3">
            <button
                type="button"
                wire:click="volver"
                class="inline-flex items-center gap-2 rounded-lg border border-neutral-200 bg-neutral-0 px-4 py-2 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                {{ __('admin/centros-medicos.show.back') }}
            </button>
            <button
                type="button"
                wire:click="editar"
                class="inline-flex items-center gap-2 rounded-lg border border-accent-100 bg-accent-50 px-4 py-2 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-accent-100">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                {{ __('admin/centros-medicos.show.edit') }}
            </button>
        </div>
    </div>

    {{-- Información general --}}
    <section class="{{ $cardClass }} p-6">
        <div class="mb-4 flex items-center justify-between border-b border-neutral-200 pb-4">
            <div class="flex items-center gap-3">
                <h2 class="text-base font-semibold text-text">
                    {{ __('admin/centros-medicos.show.general') }}
                </h2>
                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium {{ $badgeCentro['wrap'] }}">
                    <span class="size-1.5 rounded-full {{ $badgeCentro['dot'] }}"></span>
                    {{ $badgeCentro['label'] }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="flex flex-col gap-1">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                    {{ __('admin/centros-medicos.show.fields.direccion') }}
                </span>
                <span class="text-[13px] font-medium text-text">{{ $centro->direccion ?: '—' }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                    {{ __('admin/centros-medicos.show.fields.ciudad') }}
                </span>
                <span class="text-[13px] font-medium text-text">{{ $ciudad }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                    {{ __('admin/centros-medicos.show.fields.departamento') }}
                </span>
                <span class="text-[13px] font-medium text-text">{{ $departamento }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                    {{ __('admin/centros-medicos.show.fields.correo') }}
                </span>
                <span class="text-[13px] font-medium text-text">{{ $centro->correo ?: '—' }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                    {{ __('admin/centros-medicos.show.fields.telefono') }}
                </span>
                <span class="text-[13px] font-medium text-text">{{ $centro->contacto_celular ?: '—' }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                    {{ __('admin/centros-medicos.show.fields.id') }}
                </span>
                <span class="text-[13px] font-medium text-text">{{ $idCentroFormat }}</span>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="flex items-center gap-4 {{ $cardClass }} p-5">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-500">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl font-bold leading-tight text-text">{{ $totalDispositivos }}</span>
                <span class="text-sm text-neutral-600">
                    {{ __('admin/centros-medicos.show.stats.dispositivos_registrados') }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-4 {{ $cardClass }} p-5">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-success-light text-success-text">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl font-bold leading-tight text-text">{{ $dispositivosActivos }}</span>
                <span class="text-sm text-neutral-600">
                    {{ __('admin/centros-medicos.show.stats.dispositivos_activos') }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-4 {{ $cardClass }} p-5">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl font-bold leading-tight text-text">{{ $totalMedicos }}</span>
                <span class="text-sm text-neutral-600">
                    {{ __('admin/centros-medicos.show.stats.medicos_registrados') }}
                </span>
            </div>
        </div>
    </section>

    {{-- Sección Dispositivos --}}
    <section class="mt-2 flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-text">
                {{ __('admin/centros-medicos.show.dispositivos.title') }}
            </h3>
            <button
                type="button"
                wire:click="openVincularModal"
                class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-neutral-0 px-3 py-1.5 text-[13px] font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                {{ __('admin/centros-medicos.show.dispositivos.cta_link') }}
            </button>
        </div>

        <div class="overflow-hidden {{ $cardClass }}">
            <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
                <table class="w-full min-w-[700px] border-collapse whitespace-nowrap text-left">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-neutral-50/50 text-[13px] font-medium text-neutral-600">
                            <th class="px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.dispositivos.th_id') }}</th>
                            <th class="px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.dispositivos.th_modelo') }}</th>
                            <th class="px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.dispositivos.th_asignacion') }}</th>
                            <th class="px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.dispositivos.th_estado') }}</th>
                            <th class="px-5 py-3.5 font-medium">{{ __('admin/centros-medicos.show.dispositivos.th_acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-[13px]">
                        @forelse ($dispositivos as $dispositivo)
                            @php
                                $estadoDisp = $dispositivo->estado instanceof DispositivoEstado
                                    ? $dispositivo->estado->value
                                    : (string) $dispositivo->estado;

                                $badgeDisp = match ($estadoDisp) {
                                    DispositivoEstado::Activo->value => [
                                        'wrap' => 'bg-success-light text-success-text border-success-mid',
                                        'dot'  => 'bg-success',
                                        'label' => __('admin/centros-medicos.show.estado_dispositivo.activo'),
                                    ],
                                    DispositivoEstado::Mantenimiento->value => [
                                        'wrap' => 'bg-warning-light text-warning-text border-warning-mid',
                                        'dot'  => 'bg-warning',
                                        'label' => __('admin/centros-medicos.show.estado_dispositivo.mantenimiento'),
                                    ],
                                    default => [
                                        'wrap' => 'bg-neutral-100 text-neutral-600 border-neutral-300',
                                        'dot'  => 'bg-neutral-600',
                                        'label' => __('admin/centros-medicos.show.estado_dispositivo.inactivo'),
                                    ],
                                };
                            @endphp
                            <tr
                                wire:key="centro-dispositivo-{{ $dispositivo->id }}"
                                class="group border-b border-neutral-100 transition-colors last:border-b-0 hover:bg-neutral-50">
                                <td class="px-5 py-3.5 font-medium text-text">{{ $dispositivo->numero_serie }}</td>
                                <td class="px-5 py-3.5 text-neutral-600">{{ $dispositivo->hardwareModelo?->nombre ?? '—' }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($dispositivo->ubicacion)
                                        <span class="font-medium text-text">{{ $dispositivo->ubicacion }}</span>
                                    @else
                                        <span class="italic text-neutral-400">
                                            {{ __('admin/centros-medicos.show.dispositivos.sin_asignar') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-medium {{ $badgeDisp['wrap'] }}">
                                        <span class="size-1.5 rounded-full {{ $badgeDisp['dot'] }}"></span>
                                        {{ $badgeDisp['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2 opacity-80 transition-opacity group-hover:opacity-100">
                                        <button
                                            type="button"
                                            wire:click="openEditDispositivoModal({{ $dispositivo->id }})"
                                            class="rounded px-2 py-1 font-medium text-primary-600 transition-colors hover:bg-accent-50">
                                            {{ __('admin/centros-medicos.show.actions.edit') }}
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded px-2 py-1 font-medium text-error transition-colors hover:bg-error-light">
                                            {{ __('admin/centros-medicos.show.actions.delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-neutral-500">
                                    {{ __('admin/centros-medicos.show.dispositivos.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Sección Personal --}}
    <section class="mt-2 flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="text-lg font-bold text-text">
                    {{ __('admin/centros-medicos.show.personal.title') }}
                </h3>
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
                                wire:key="centro-personal-{{ $medico->id }}"
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

    @include('livewire.admin.centros-medicos.vincular-dispositivo-modal')
    @include('livewire.admin.centros-medicos.edit-dispositivo-modal')
    @include('livewire.admin.centros-medicos.desvincular-dispositivo-modal')
    @include('livewire.admin.centros-medicos.agregar-medico-modal')
    @include('livewire.admin.centros-medicos.editar-personal-modal')
    @include('livewire.admin.centros-medicos.desactivar-personal-modal')
    @include('livewire.admin.centros-medicos.success-modal')
</div>
