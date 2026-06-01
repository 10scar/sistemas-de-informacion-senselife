<div class="flex items-start justify-between gap-3 p-4 transition-colors hover:bg-neutral-50">
    <div class="flex min-w-0 items-start gap-3">
        <div class="mt-1 size-2 rounded-full {{ $this->clasePuntoAlerta($alerta->tipo) }}"></div>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-text">
                {{ __('portal/pacientes.show.alerta_tipo_' . $alerta->tipo->value) }}
            </p>
            <p class="text-[11px] text-neutral-500">
                {{ __('portal/pacientes.show.alerta_meta', [
                    'id' => $alerta->id_telemetria,
                    'time' => $alerta->fecha_creacion->diffForHumans(),
                ]) }}
            </p>
            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                <span class="rounded-md border px-2 py-0.5 text-[10px] font-bold {{ $this->claseBadgeAlerta($alerta->tipo) }}">
                    {{ $this->etiquetaAlerta($alerta->tipo) }}
                </span>
                <span class="rounded-md border px-2 py-0.5 text-[10px] font-medium {{ $this->claseBadgeEstadoAlerta($alerta->estado) }}">
                    {{ $this->etiquetaEstadoAlerta($alerta->estado) }}
                </span>
            </div>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2">
        @if ($alerta->estado->value === 'pendiente')
            <button
                type="button"
                wire:click="atenderAlerta({{ $alerta->id }})"
                class="rounded px-2 py-1 font-medium text-primary-600 transition-colors hover:bg-accent-50">
                {{ __('portal/pacientes.show.action_atender') }}
            </button>
            <button
                type="button"
                wire:click="ignorarAlerta({{ $alerta->id }})"
                class="rounded px-2 py-1 font-medium text-error transition-colors hover:bg-error-light">
                {{ __('portal/pacientes.show.action_ignorar') }}
            </button>
        @elseif ($alerta->estado->value === 'vista')
            <span class="rounded px-2 py-1 text-xs font-medium text-success-text">
                {{ __('portal/pacientes.show.action_revisado') }}
            </span>
            <button
                type="button"
                wire:click="ignorarAlerta({{ $alerta->id }})"
                class="rounded px-2 py-1 font-medium text-error transition-colors hover:bg-error-light">
                {{ __('portal/pacientes.show.action_ignorar') }}
            </button>
        @else
            <span class="rounded px-2 py-1 text-xs font-medium text-neutral-500">
                {{ __('portal/pacientes.show.estado_cerrada') }}
            </span>
        @endif
    </div>
</div>
