@props([
    'alertas',
    'showActions' => true,
    'compact' => false,
])

@php
    use App\Support\AlertaPresentacion;
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm">
        <thead>
            <tr class="border-b border-neutral-200 bg-neutral-50/80">
                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ __('portal/alertas.col_timestamp') }}
                </th>
                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ __('portal/alertas.col_cuna') }}
                </th>
                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ __('portal/alertas.col_valor') }}
                </th>
                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ __('portal/alertas.col_estado') }}
                </th>
                @if ($showActions)
                    <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                        {{ __('portal/alertas.col_acciones') }}
                    </th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100">
            @forelse ($alertas as $alerta)
                @php
                    $valor = AlertaPresentacion::valorCritico($alerta);
                @endphp
                <tr wire:key="alerta-row-{{ $alerta->id }}" class="transition-colors hover:bg-neutral-50/70">
                    <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs tabular-nums text-neutral-500">
                        {{ $alerta->fecha_creacion->format('d/m/y H:i:s') }}
                    </td>
                    <td class="px-4 py-3.5 font-semibold text-text">
                        {{ AlertaPresentacion::etiquetaCuna($alerta) }}
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="{{ $valor['clase'] }}">{{ $valor['etiqueta'] }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        <span @class([
                            'inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide',
                            $this->claseBadgeEstadoAlerta($alerta->estado),
                        ])>
                            {{ $this->etiquetaEstadoAlerta($alerta->estado) }}
                        </span>
                    </td>
                    @if ($showActions)
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                @if ($alerta->estado->value === 'pendiente')
                                    <button
                                        type="button"
                                        wire:click="solicitarAtenderAlerta({{ $alerta->id }})"
                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-primary-600 transition hover:bg-accent-50">
                                        {{ __('portal/alertas.action_atender') }}
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="solicitarIgnorarAlerta({{ $alerta->id }})"
                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-error transition hover:bg-error-light">
                                        {{ __('portal/alertas.action_ignorar') }}
                                    </button>
                                @elseif ($alerta->estado->value === 'vista')
                                    <button
                                        type="button"
                                        wire:click="solicitarConfirmarAtendido({{ $alerta->id }})"
                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-primary-600 transition hover:bg-accent-50">
                                        {{ __('portal/alertas.action_confirmar_atendido') }}
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="solicitarIgnorarAlerta({{ $alerta->id }})"
                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-error transition hover:bg-error-light">
                                        {{ __('portal/alertas.action_ignorar') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showActions ? 5 : 4 }}" class="px-4 py-10 text-center text-sm text-neutral-500">
                        {{ __('portal/alertas.empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
