@php
    use App\Enums\CentroEstado;
@endphp

<div class="flex flex-col gap-6 text-text">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
        <div class="flex flex-col gap-1.5">
            <div class="flex items-center gap-2 text-sm font-medium">
                <span class="text-text">{{ __('admin/centros-medicos.breadcrumb') }}</span>
            </div>
            <h1 class="font-display text-3xl font-bold text-text">
                {{ __('admin/centros-medicos.title') }}
            </h1>
            <p class="text-sm text-neutral-600">
                {{ __('admin/centros-medicos.subtitle') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                {{ __('admin/centros-medicos.cta_new') }}
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        {{-- Total --}}
        <div
            class="flex items-center gap-4 rounded-2xl border border-neutral-200 bg-neutral-0 p-6 shadow-elev-control">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-accent-50">
                <svg class="size-[22px] text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-3xl font-bold leading-none text-text">{{ $totales['total'] }}</span>
                <span class="mt-1 text-sm text-neutral-600">{{ __('admin/centros-medicos.stats.total') }}</span>
            </div>
        </div>

        {{-- Activos --}}
        <div
            class="flex items-center gap-4 rounded-2xl border border-neutral-200 bg-neutral-0 p-6 shadow-elev-control">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-success-light">
                <svg class="size-[22px] text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-3xl font-bold leading-none text-text">{{ $totales['activos'] }}</span>
                <span class="mt-1 text-sm text-neutral-600">{{ __('admin/centros-medicos.stats.activos') }}</span>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col gap-3 md:flex-row">
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg class="size-[18px] text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('admin/centros-medicos.filters.search_placeholder') }}"
                class="w-full rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-10 pr-4 text-sm text-text placeholder-neutral-400 shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
        </div>

        <div class="relative min-w-[180px]">
            <select
                wire:model.live="estado"
                class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-text shadow-elev-control focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                <option value="">{{ __('admin/centros-medicos.filters.all_states') }}</option>
                <option value="{{ CentroEstado::Activo->value }}">
                    {{ __('admin/centros-medicos.filters.state_activo') }}
                </option>
                <option value="{{ CentroEstado::Inactivo->value }}">
                    {{ __('admin/centros-medicos.filters.state_inactivo') }}
                </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="size-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-0 shadow-elev-control">
        <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
            <table class="w-full min-w-[1000px] border-collapse whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-neutral-200 bg-neutral-50/50 text-[13px] font-medium text-neutral-600">
                        <th class="w-[25%] px-5 py-4 font-medium">
                            {{ __('admin/centros-medicos.table.centro') }}
                        </th>
                        <th class="w-[25%] px-5 py-4 font-medium">
                            {{ __('admin/centros-medicos.table.ciudad') }}
                        </th>
                        <th class="w-[25%] px-5 py-4 font-medium">
                            {{ __('admin/centros-medicos.table.contacto') }}
                        </th>
                        <th class="w-[12%] px-5 py-4 font-medium">
                            {{ __('admin/centros-medicos.table.estado') }}
                        </th>
                        <th class="w-[13%] px-5 py-4 font-medium">
                            {{ __('admin/centros-medicos.table.acciones') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="text-[13px]">
                    @forelse ($centros as $centro)
                        @php
                            $estadoValor = $centro->estado instanceof CentroEstado
                                ? $centro->estado->value
                                : (string) $centro->estado;

                            $badge = match ($estadoValor) {
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

                            $ciudad = $centro->municipio?->name;
                            $detalle = $centro->direccion;
                        @endphp
                        <tr
                            wire:key="centro-row-{{ $centro->id }}"
                            wire:click="viewCentro({{ $centro->id }})"
                            class="cursor-pointer border-b border-neutral-200 transition-colors last:border-b-0 hover:bg-neutral-50">
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="font-medium text-text">{{ $centro->nombre }}</span>
                                    <span class="text-xs text-neutral-600">
                                        #{{ str_pad((string) $centro->id, 3, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-text">{{ $ciudad ?: '—' }}</span>
                                    @if ($detalle)
                                        <span class="text-xs text-neutral-600">{{ $detalle }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-text">{{ $centro->contacto_celular ?? '—' }}</span>
                                    @if ($centro->correo)
                                        <span class="text-xs text-neutral-600">{{ $centro->correo }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium {{ $badge['wrap'] }}">
                                    <span class="size-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click.stop="openEditModal({{ $centro->id }})"
                                        class="rounded border border-transparent px-3 py-1.5 font-medium text-primary-600 transition-all hover:border-neutral-200 hover:bg-neutral-0">
                                        {{ __('admin/centros-medicos.actions.edit') }}
                                    </button>
                                    <button
                                        type="button"
                                        wire:click.stop="openDeleteModal({{ $centro->id }})"
                                        class="rounded border border-transparent px-3 py-1.5 font-medium text-error transition-all hover:border-error-mid hover:bg-error-light">
                                        {{ __('admin/centros-medicos.actions.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-neutral-500">
                                {{ __('admin/centros-medicos.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        <div
            class="flex items-center justify-between gap-3 border-t border-neutral-200 bg-neutral-50/30 px-5 py-4 text-[13px] text-neutral-600">
            <span>
                {{ __('admin/centros-medicos.pagination_summary', [
                    'shown' => $centros->count(),
                    'total' => $centros->total(),
                ]) }}
            </span>

            @if ($centros->hasPages())
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        wire:click="previousPage"
                        @disabled($centros->onFirstPage())
                        class="flex size-8 items-center justify-center rounded border border-neutral-200 bg-neutral-0 text-neutral-400 transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                    </button>

                    @foreach (range(1, $centros->lastPage()) as $page)
                        <button
                            type="button"
                            wire:click="gotoPage({{ $page }})"
                            @class([
                                'flex size-8 items-center justify-center rounded border text-sm font-medium transition-colors',
                                'border-primary-600 bg-primary-600 text-neutral-0 shadow-elev-control'
                                    => $page === $centros->currentPage(),
                                'border-neutral-200 bg-neutral-0 text-text hover:bg-neutral-50'
                                    => $page !== $centros->currentPage(),
                            ])>
                            {{ $page }}
                        </button>
                    @endforeach

                    <button
                        type="button"
                        wire:click="nextPage"
                        @disabled(! $centros->hasMorePages())
                        class="flex size-8 items-center justify-center rounded border border-neutral-200 bg-neutral-0 text-neutral-400 transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>

    @include('livewire.admin.centros-medicos.create-modal')
    @include('livewire.admin.centros-medicos.edit-modal')
    @include('livewire.admin.centros-medicos.delete-modal')
    @include('livewire.admin.centros-medicos.success-modal')
</div>
