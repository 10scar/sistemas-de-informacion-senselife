@php
    use App\Enums\DispositivoEstado;
@endphp

@if ($showVincularModal)
    <div
        wire:key="vincular-dispositivo-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeVincularModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="vincular-dispositivo-title">
        <div
            wire:click.stop
            class="relative flex max-h-[90vh] w-full max-w-2xl flex-col gap-5 overflow-hidden rounded-3xl bg-neutral-0 p-7 shadow-elev-card">

            <div class="flex shrink-0 items-start justify-between gap-4">
                <div class="flex min-w-0 flex-col gap-1">
                    <h2 id="vincular-dispositivo-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                        {{ __('admin/centros-medicos.show.vincular_modal.title') }}
                    </h2>
                    <p class="text-sm text-neutral-600">
                        {{ __('admin/centros-medicos.show.vincular_modal.description', ['centro' => $centro->nombre]) }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="closeVincularModal"
                    aria-label="{{ __('admin/centros-medicos.show.vincular_modal.close_aria') }}"
                    class="flex size-7 shrink-0 items-center justify-center rounded-lg border border-neutral-200 text-neutral-600 transition-colors hover:bg-neutral-50">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="10.5" y1="3.5" x2="3.5" y2="10.5" />
                        <line x1="3.5" y1="3.5" x2="10.5" y2="10.5" />
                    </svg>
                </button>
            </div>

            @error('vincular')
                <p class="text-sm text-error">{{ $message }}</p>
            @enderror

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain rounded-xl border border-neutral-200">
                <table class="w-full min-w-[520px] border-collapse text-left text-[13px]">
                    <thead class="sticky top-0 z-[1] border-b border-neutral-200 bg-neutral-50/95 backdrop-blur-sm">
                        <tr class="font-medium text-neutral-600">
                            <th class="px-4 py-3">{{ __('admin/centros-medicos.show.vincular_modal.th_serie') }}</th>
                            <th class="px-4 py-3">{{ __('admin/centros-medicos.show.vincular_modal.th_modelo') }}</th>
                            <th class="px-4 py-3">{{ __('admin/centros-medicos.show.vincular_modal.th_estado') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('admin/centros-medicos.show.vincular_modal.th_accion') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dispositivosSinCentro as $disp)
                            @php
                                $estadoDisp = $disp->estado instanceof DispositivoEstado
                                    ? $disp->estado->value
                                    : (string) $disp->estado;

                                $labelEstado = match ($estadoDisp) {
                                    DispositivoEstado::Activo->value => __('admin/centros-medicos.show.estado_dispositivo.activo'),
                                    DispositivoEstado::Mantenimiento->value => __('admin/centros-medicos.show.estado_dispositivo.mantenimiento'),
                                    default => __('admin/centros-medicos.show.estado_dispositivo.inactivo'),
                                };
                            @endphp
                            <tr wire:key="sin-centro-{{ $disp->id }}" class="border-b border-neutral-100 last:border-b-0">
                                <td class="px-4 py-3 font-medium text-text">{{ $disp->numero_serie }}</td>
                                <td class="px-4 py-3 text-neutral-600">{{ $disp->hardwareModelo?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-neutral-600">{{ $labelEstado }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        wire:click="vincularDispositivo({{ $disp->id }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-70">
                                        {{ __('admin/centros-medicos.show.vincular_modal.vincular') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-neutral-500">
                                    {{ __('admin/centros-medicos.show.vincular_modal.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex shrink-0 justify-end border-t border-neutral-200 pt-4">
                <button
                    type="button"
                    wire:click="closeVincularModal"
                    class="rounded-lg border border-neutral-200 bg-neutral-0 px-5 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('admin/centros-medicos.show.vincular_modal.cancel') }}
                </button>
            </div>
        </div>
    </div>
@endif
