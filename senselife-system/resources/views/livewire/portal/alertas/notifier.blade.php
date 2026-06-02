@php
    use App\Enums\AlertaTipo;
    use App\Support\AlertaPresentacion;
@endphp

<div wire:poll.{{ $pollSeconds }}s="verificarAlertasNuevas">
    @include('livewire.portal.pacientes.partials.alertas-confirm-modals')

    <div
        x-data="{
            toastIds: @entangle('toastIds'),
            sonidoTipo: @entangle('sonidoTipo'),
            soundEnabled: localStorage.getItem('senselife_alert_sound') === '1',
            alarmInterval: null,
            init() {
                this.$watch('toastIds', () => this.syncAlarm());
                this.$watch('sonidoTipo', () => this.syncAlarm());
                if (this.soundEnabled) {
                    this.syncAlarm();
                }
            },
            enableSound() {
                this.soundEnabled = true;
                localStorage.setItem('senselife_alert_sound', '1');
                this.playTone(this.sonidoTipo || 'alerta');
                this.syncAlarm();
            },
            syncAlarm() {
                if (this.soundEnabled && this.toastIds.length > 0) {
                    if (!this.alarmInterval) {
                        this.playTone(this.sonidoTipo || 'alerta');
                        this.alarmInterval = setInterval(() => {
                            if (this.toastIds.length === 0) {
                                this.stopAlarm();
                                return;
                            }
                            this.playTone(this.sonidoTipo || 'alerta');
                        }, 4000);
                    }
                } else {
                    this.stopAlarm();
                }
            },
            stopAlarm() {
                if (this.alarmInterval) {
                    clearInterval(this.alarmInterval);
                    this.alarmInterval = null;
                }
            },
            playTone(tipo) {
                if (!this.soundEnabled) return;
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.value = tipo === 'critico' ? 880 : 440;
                    gain.gain.setValueAtTime(0.25, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + (tipo === 'critico' ? 0.6 : 0.35));
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + (tipo === 'critico' ? 0.6 : 0.35));
                    if (tipo === 'critico') {
                        setTimeout(() => {
                            const o2 = ctx.createOscillator();
                            const g2 = ctx.createGain();
                            o2.connect(g2);
                            g2.connect(ctx.destination);
                            o2.type = 'sine';
                            o2.frequency.value = 880;
                            g2.gain.setValueAtTime(0.2, ctx.currentTime);
                            g2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                            o2.start(ctx.currentTime);
                            o2.stop(ctx.currentTime + 0.4);
                        }, 350);
                    }
                } catch (e) {}
            },
        }"
        x-on:alerta-nueva.window="if (soundEnabled) { playTone($event.detail.tipo); syncAlarm(); }"
        class="pointer-events-none fixed inset-0 z-[80]">
        <div class="pointer-events-none absolute right-4 top-4 flex w-full max-w-sm flex-col gap-3 sm:right-6 sm:top-6">
            <div
                x-show="!soundEnabled"
                x-cloak
                class="pointer-events-auto rounded-xl border border-info-border bg-info-light px-4 py-3 text-sm text-info-text shadow-elev-card">
                <p class="font-medium">{{ __('portal/alertas.toast_enable_sound') }}</p>
                <button
                    type="button"
                    x-on:click="enableSound()"
                    class="mt-2 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-neutral-0 transition hover:bg-primary-700">
                    {{ __('portal/alertas.toast_enable_sound_action') }}
                </button>
            </div>

            @foreach ($toasts as $alerta)
                @php
                    $valor = AlertaPresentacion::valorCritico($alerta);
                    $esCritico = $alerta->tipo === AlertaTipo::Critico;
                @endphp
                <article
                    wire:key="alerta-toast-{{ $alerta->id }}"
                    @class([
                        'pointer-events-auto overflow-hidden rounded-2xl border bg-neutral-0 shadow-elev-card',
                        'border-error-border ring-1 ring-error-border/30' => $esCritico,
                        'border-warning-border ring-1 ring-warning-border/20' => ! $esCritico,
                    ])
                    role="alert"
                    aria-live="assertive">
                    <div @class([
                        'flex items-center gap-2 border-b px-4 py-2.5',
                        'border-error-border/40 bg-error-light/60' => $esCritico,
                        'border-warning-border/40 bg-warning-light/60' => ! $esCritico,
                    ])>
                        <svg @class(['size-4 shrink-0', 'text-error' => $esCritico, 'text-warning-text' => ! $esCritico]) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                        <span class="text-xs font-bold uppercase tracking-wide text-text">
                            {{ __('portal/alertas.toast_nueva_alerta') }}
                        </span>
                        <span @class([
                            'ml-auto inline-flex rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase',
                            $this->claseBadgeAlerta($alerta->tipo),
                        ])>
                            {{ $this->etiquetaAlerta($alerta->tipo) }}
                        </span>
                    </div>

                    <div class="space-y-1.5 px-4 py-3">
                        <p class="text-sm font-semibold text-text">
                            {{ $alerta->paciente?->nombre_completo ?? __('portal/alertas.cuna_no_disponible') }}
                        </p>
                        <p class="text-xs text-neutral-600">
                            {{ AlertaPresentacion::etiquetaCuna($alerta) }}
                            <span class="text-neutral-300" aria-hidden="true">&bull;</span>
                            <span class="{{ $valor['clase'] }}">{{ $valor['etiqueta'] }}</span>
                        </p>
                        <p class="font-mono text-[11px] tabular-nums text-neutral-500">
                            {{ $alerta->fecha_creacion->format('d/m/y H:i:s') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 border-t border-neutral-100 px-4 py-2.5">
                        <button
                            type="button"
                            wire:click="solicitarAtenderAlerta({{ $alerta->id }})"
                            class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-neutral-0 transition hover:bg-primary-700">
                            {{ __('portal/alertas.action_atender') }}
                        </button>
                        <button
                            type="button"
                            wire:click="solicitarIgnorarAlerta({{ $alerta->id }})"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold text-error transition hover:bg-error-light">
                            {{ __('portal/alertas.action_ignorar') }}
                        </button>
                        <button
                            type="button"
                            wire:click="descartarToast({{ $alerta->id }})"
                            class="ml-auto rounded-lg px-3 py-1.5 text-xs font-semibold text-neutral-600 transition hover:bg-neutral-100">
                            {{ __('portal/alertas.toast_cerrar') }}
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>
