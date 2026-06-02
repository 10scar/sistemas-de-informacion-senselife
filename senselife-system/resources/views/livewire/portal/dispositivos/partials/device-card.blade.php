@props([
    'dispositivo',
])

@php
    use App\Enums\DispositivoEstado;

    $nombre = $dispositivo->hardwareModelo?->nombre ?? __('portal/dispositivos.title');
    $serie = $dispositivo->numero_serie;
    $ubicacion = filled($dispositivo->ubicacion)
        ? $dispositivo->ubicacion
        : __('portal/dispositivos.ubicacion_empty');

    $badgeClase = match ($dispositivo->estado) {
        DispositivoEstado::Activo => 'border-success-border bg-success-light/60 text-success-text',
        DispositivoEstado::Mantenimiento => 'border-warning-border bg-warning-light/60 text-warning-text',
        DispositivoEstado::Inactivo => 'border-neutral-300 bg-neutral-100 text-neutral-600',
    };

    $iconBg = match ($dispositivo->estado) {
        DispositivoEstado::Activo => 'bg-success-light/70',
        DispositivoEstado::Mantenimiento => 'bg-warning-light/70',
        DispositivoEstado::Inactivo => 'bg-neutral-100',
    };
@endphp

<article class="rounded-2xl border border-neutral-200 bg-neutral-0 p-5 shadow-elev-card">
    <div class="flex items-start gap-4">
        <div @class(['flex size-12 shrink-0 items-center justify-center rounded-xl', $iconBg]) aria-hidden="true">
            <svg class="size-6 text-text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="5" y="2" width="14" height="20" rx="2" />
                <line x1="12" y1="18" x2="12.01" y2="18" />
            </svg>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="truncate text-base font-bold text-text">{{ $nombre }}</h3>
                    <p class="mt-0.5 text-sm font-medium text-neutral-500">{{ $serie }}</p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2">
                    <span @class([
                        'inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide',
                        $badgeClase,
                    ])>
                        {{ __('portal/dispositivos.estado.'.$dispositivo->estado->value) }}
                    </span>
                    <button
                        type="button"
                        wire:click="openEditModal({{ $dispositivo->id }})"
                        class="rounded-lg border border-neutral-200 bg-neutral-0 px-2.5 py-1 text-xs font-semibold text-primary-600 shadow-elev-control transition hover:bg-accent-50">
                        {{ __('portal/dispositivos.action_edit') }}
                    </button>
                </div>
            </div>

            <div class="mt-5 border-t border-neutral-100 pt-4">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-neutral-400">
                    {{ __('portal/dispositivos.ubicacion_label') }}
                </p>
                <p class="mt-1 text-sm font-medium text-text">{{ $ubicacion }}</p>
            </div>
        </div>
    </div>
</article>
