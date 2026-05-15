@php
    use App\Enums\DispositivoEstado;
@endphp

<div class="flex flex-col gap-6 text-text">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
        <div class="flex flex-col gap-1.5">
            <div class="flex items-center gap-2 text-sm font-medium">
                <span class="text-text">{{ __('admin/dispositivos.breadcrumb') }}</span>
            </div>
            <h1 class="font-display text-3xl font-bold text-text">
                {{ __('admin/dispositivos.title') }}
            </h1>
            <p class="text-sm text-neutral-600">
                {{ __('admin/dispositivos.subtitle') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span
                class="inline-flex items-center rounded-full border border-accent-100 bg-accent-50 px-3 py-1.5 text-xs font-semibold tracking-wide text-accent-500">
                {{ __('admin/dispositivos.tag') }}
            </span>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                {{ __('admin/dispositivos.cta_new') }}
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        {{-- Total --}}
        <div
            class="flex items-center gap-4 rounded-2xl border border-neutral-200 bg-neutral-0 p-5 shadow-elev-control">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-accent-50">
                <svg class="size-[22px] text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl font-bold leading-tight text-text">{{ $totales['total'] }}</span>
                <span class="text-sm text-neutral-600">{{ __('admin/dispositivos.stats.total') }}</span>
            </div>
        </div>

        {{-- En uso --}}
        <div
            class="flex items-center gap-4 rounded-2xl border border-neutral-200 bg-neutral-0 p-5 shadow-elev-control">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-success-light">
                <svg class="size-[22px] text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl font-bold leading-tight text-text">{{ $totales['en_uso'] }}</span>
                <span class="text-sm text-neutral-600">{{ __('admin/dispositivos.stats.en_uso') }}</span>
            </div>
        </div>

        {{-- Sin asignar / inactivos --}}
        <div
            class="flex items-center gap-4 rounded-2xl border border-neutral-200 bg-neutral-0 p-5 shadow-elev-control">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-warning-light">
                <svg class="size-[22px] text-warning" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl font-bold leading-tight text-text">{{ $totales['sin_asignar'] }}</span>
                <span class="text-sm text-neutral-600">{{ __('admin/dispositivos.stats.sin_asignar') }}</span>
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
                placeholder="{{ __('admin/dispositivos.filters.search_placeholder') }}"
                class="w-full rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-10 pr-4 text-sm text-text placeholder-neutral-400 focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
        </div>

        <div class="flex gap-3">
            <div class="relative min-w-[180px]">
                <select
                    wire:model.live="estado"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-text focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                    <option value="">{{ __('admin/dispositivos.filters.all_states') }}</option>
                    <option value="{{ DispositivoEstado::Activo->value }}">
                        {{ __('admin/dispositivos.filters.state_activo') }}
                    </option>
                    <option value="{{ DispositivoEstado::Mantenimiento->value }}">
                        {{ __('admin/dispositivos.filters.state_mantenimiento') }}
                    </option>
                    <option value="{{ DispositivoEstado::Inactivo->value }}">
                        {{ __('admin/dispositivos.filters.state_inactivo') }}
                    </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="size-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
            </div>

            <div class="relative min-w-[200px]">
                <select
                    wire:model.live="centro"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-neutral-300 bg-neutral-0 py-2.5 pl-4 pr-10 text-sm text-text focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600">
                    <option value="">{{ __('admin/dispositivos.filters.all_centers') }}</option>
                    @foreach ($centros as $centroOpt)
                        <option value="{{ $centroOpt->id }}">{{ $centroOpt->nombre }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="size-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-0 shadow-elev-control">
        <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
            <table class="w-full min-w-[680px] border-collapse whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-neutral-200 text-sm font-medium text-neutral-600">
                        <th class="w-[18%] px-6 py-4 font-medium">
                            {{ __('admin/dispositivos.table.id') }}
                        </th>
                        <th class="w-[30%] px-6 py-4 font-medium">
                            {{ __('admin/dispositivos.table.modelo') }}
                        </th>
                        <th class="w-[40%] px-6 py-4 font-medium">
                            {{ __('admin/dispositivos.table.centro') }}
                        </th>
                        <th class="w-[12%] px-6 py-4 font-medium">
                            {{ __('admin/dispositivos.table.estado') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse ($dispositivos as $dispositivo)
                        <tr
                            class="cursor-pointer border-b border-neutral-100 transition-colors last:border-b-0 hover:bg-neutral-50">
                            <td class="px-6 py-4 font-medium text-text">
                                {{ $dispositivo->numero_serie ?? 'SL-'.str_pad((string) $dispositivo->id, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-4 text-text">
                                {{ $dispositivo->hardwareModelo?->nombre ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-neutral-600">
                                {{ $dispositivo->centroMedico?->nombre ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $estadoValor = $dispositivo->estado instanceof DispositivoEstado
                                        ? $dispositivo->estado->value
                                        : (string) $dispositivo->estado;

                                    $badge = match ($estadoValor) {
                                        DispositivoEstado::Activo->value => [
                                            'wrap' => 'bg-success-light text-success-text border-success-mid',
                                            'dot'  => 'bg-success',
                                            'label' => __('admin/dispositivos.estado.activo'),
                                        ],
                                        DispositivoEstado::Mantenimiento->value => [
                                            'wrap' => 'bg-warning-light text-warning-text border-warning-mid',
                                            'dot'  => 'bg-warning',
                                            'label' => __('admin/dispositivos.estado.mantenimiento'),
                                        ],
                                        default => [
                                            'wrap' => 'bg-neutral-100 text-neutral-600 border-neutral-200',
                                            'dot'  => 'bg-neutral-400',
                                            'label' => __('admin/dispositivos.estado.inactivo'),
                                        ],
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-medium {{ $badge['wrap'] }}">
                                    <span class="size-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-neutral-500">
                                {{ __('admin/dispositivos.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        <div
            class="flex items-center justify-between gap-3 border-t border-neutral-200 px-6 py-4 text-sm text-neutral-600">
            <span>
                {{ __('admin/dispositivos.pagination_summary', [
                    'shown' => $dispositivos->count(),
                    'total' => $dispositivos->total(),
                ]) }}
            </span>

            @if ($dispositivos->hasPages())
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        wire:click="previousPage"
                        @disabled($dispositivos->onFirstPage())
                        class="flex size-8 items-center justify-center rounded border border-neutral-200 text-neutral-400 transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                    </button>

                    @foreach (range(1, $dispositivos->lastPage()) as $page)
                        <button
                            type="button"
                            wire:click="gotoPage({{ $page }})"
                            @class([
                                'flex size-8 items-center justify-center rounded border text-sm font-medium transition-colors',
                                'border-primary-600 bg-primary-600 text-neutral-0 shadow-elev-control'
                                    => $page === $dispositivos->currentPage(),
                                'border-neutral-200 text-text hover:bg-neutral-50'
                                    => $page !== $dispositivos->currentPage(),
                            ])>
                            {{ $page }}
                        </button>
                    @endforeach

                    <button
                        type="button"
                        wire:click="nextPage"
                        @disabled(! $dispositivos->hasMorePages())
                        class="flex size-8 items-center justify-center rounded border border-neutral-200 text-text transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
