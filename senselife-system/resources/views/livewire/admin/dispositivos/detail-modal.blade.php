@php
    use App\Enums\DispositivoEstado;
@endphp

{{-- Modal de detalle del dispositivo: mismo formato que el de crear, pero solo lectura. --}}
@if ($showDetailModal && $selectedDispositivo)
    @php
        $estadoValor = $selectedDispositivo->estado instanceof DispositivoEstado
            ? $selectedDispositivo->estado->value
            : (string) $selectedDispositivo->estado;

        $estadoBadge = match ($estadoValor) {
            DispositivoEstado::Activo->value => [
                'wrap'  => 'bg-success-light text-success-text border-success-mid',
                'dot'   => 'bg-success',
                'label' => __('admin/dispositivos.estado.activo'),
            ],
            DispositivoEstado::Mantenimiento->value => [
                'wrap'  => 'bg-warning-light text-warning-text border-warning-mid',
                'dot'   => 'bg-warning',
                'label' => __('admin/dispositivos.estado.mantenimiento'),
            ],
            default => [
                'wrap'  => 'bg-neutral-100 text-neutral-600 border-neutral-200',
                'dot'   => 'bg-neutral-400',
                'label' => __('admin/dispositivos.estado.inactivo'),
            ],
        };

        $puedeEditar = $estadoValor === DispositivoEstado::Inactivo->value
            || $selectedDispositivo->centro_medico_id === null;
        $emptyValue = __('admin/dispositivos.detail_modal.empty_value');
    @endphp

    <div
        wire:key="detail-dispositivo-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeDetailModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="detail-dispositivo-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-xl flex-col gap-5 rounded-3xl bg-neutral-0 p-7 shadow-elev-card">

            {{-- Cabecera --}}
            <div class="flex items-start justify-between">
                <div class="flex flex-col gap-1">
                    <h2 id="detail-dispositivo-title"
                        class="text-xl font-bold leading-tight text-text md:text-[22px]">
                        {{ __('admin/dispositivos.detail_modal.title') }}
                    </h2>
                    <p class="text-sm text-neutral-600">
                        {{ __('admin/dispositivos.detail_modal.subtitle') }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="closeDetailModal"
                    aria-label="{{ __('admin/dispositivos.create_modal.close_aria') }}"
                    class="flex size-7 shrink-0 items-center justify-center rounded-lg border border-neutral-200 text-neutral-600 transition-colors hover:bg-neutral-50">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="10.5" y1="3.5" x2="3.5" y2="10.5" />
                        <line x1="3.5" y1="3.5" x2="10.5" y2="10.5" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-col gap-5">

                {{-- Identificación --}}
                <div class="flex flex-col gap-4">
                    <h3 class="text-sm font-semibold text-neutral-500">
                        {{ __('admin/dispositivos.detail_modal.section_id') }}
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-xs font-medium uppercase tracking-wide text-neutral-500">
                                {{ __('admin/dispositivos.detail_modal.modelo_label') }}
                            </span>
                            <span class="rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-2.5 text-sm text-text shadow-elev-control">
                                {{ $selectedDispositivo->hardwareModelo?->nombre ?? $emptyValue }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <span class="text-xs font-medium uppercase tracking-wide text-neutral-500">
                                {{ __('admin/dispositivos.detail_modal.serie_label') }}
                            </span>
                            <span class="rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-2.5 text-sm text-text shadow-elev-control">
                                {{ $selectedDispositivo->numero_serie ?? $emptyValue }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr class="my-1 border-t border-neutral-200">

                {{-- Asignación --}}
                <div class="flex flex-col gap-4">
                    <h3 class="text-sm font-semibold text-neutral-500">
                        {{ __('admin/dispositivos.detail_modal.section_assign') }}
                    </h3>

                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-medium uppercase tracking-wide text-neutral-500">
                            {{ __('admin/dispositivos.detail_modal.centro_label') }}
                        </span>
                        <span class="rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-2.5 text-sm text-text shadow-elev-control">
                            {{ $selectedDispositivo->centroMedico?->nombre ?? $emptyValue }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-medium uppercase tracking-wide text-neutral-500">
                            {{ __('admin/dispositivos.detail_modal.estado_label') }}
                        </span>
                        <span class="inline-flex w-fit items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-medium {{ $estadoBadge['wrap'] }}">
                            <span class="size-1.5 rounded-full {{ $estadoBadge['dot'] }}"></span>
                            {{ $estadoBadge['label'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-2 flex items-center justify-end gap-3 border-t border-neutral-200 pt-6">
                <button
                    type="button"
                    wire:click="closeDetailModal"
                    class="rounded-lg border border-neutral-200 bg-neutral-0 px-5 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('admin/dispositivos.detail_modal.close') }}
                </button>

                @if ($puedeEditar)
                    <button
                        type="button"
                        wire:click="editDispositivo({{ $selectedDispositivo->id }})"
                        class="flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                        </svg>
                        {{ __('admin/dispositivos.detail_modal.edit') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
