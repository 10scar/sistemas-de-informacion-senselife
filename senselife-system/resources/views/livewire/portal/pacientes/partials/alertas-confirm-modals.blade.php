@if ($showConfirmIniciarAtencionModal)
    <div
        wire:key="portal-confirm-iniciar-atencion-alerta"
        class="pointer-events-auto fixed inset-0 z-[90] flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeConfirmIniciarAtencionModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-iniciar-atencion-alerta-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-[416px] flex-col items-center justify-center overflow-hidden rounded-3xl bg-neutral-0 p-8 pt-10 text-center shadow-elev-card">
            <div
                class="mb-6 flex size-14 shrink-0 items-center justify-center rounded-full bg-accent-50"
                aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="text-primary-600" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" />
                    <path d="M9 17v1a3 3 0 006 0v-1" />
                </svg>
            </div>

            <div class="mb-8 flex flex-col gap-2">
                <h2 id="confirm-iniciar-atencion-alerta-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                    {{ __('portal/alertas.confirm_iniciar_atencion_title') }}
                </h2>
                <p class="px-2 text-sm leading-relaxed text-neutral-600">
                    {{ __('portal/alertas.confirm_iniciar_atencion_description') }}
                </p>
            </div>

            <div class="flex w-full items-center gap-3">
                <button
                    type="button"
                    wire:click="closeConfirmIniciarAtencionModal"
                    class="flex-1 rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('portal/alertas.confirm_iniciar_atencion_cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="confirmarIniciarAtencionAlerta"
                    wire:loading.attr="disabled"
                    class="flex-[1.2] rounded-lg bg-primary-600 py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-70">
                    {{ __('portal/alertas.confirm_iniciar_atencion_confirm') }}
                </button>
            </div>
        </div>
    </div>
@endif

@if ($showConfirmIgnorarAlertaModal)
    <div
        wire:key="portal-confirm-ignorar-alerta"
        class="pointer-events-auto fixed inset-0 z-[90] flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeConfirmIgnorarAlertaModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-ignorar-alerta-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-[416px] flex-col items-center justify-center overflow-hidden rounded-3xl bg-neutral-0 p-8 pt-10 text-center shadow-elev-card">
            <div
                class="mb-6 flex size-14 shrink-0 items-center justify-center rounded-full bg-error-light"
                aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="text-error" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
            </div>

            <div class="mb-8 flex flex-col gap-2">
                <h2 id="confirm-ignorar-alerta-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                    {{ __('portal/alertas.confirm_ignorar_title') }}
                </h2>
                <p class="px-2 text-sm leading-relaxed text-neutral-600">
                    {{ __('portal/alertas.confirm_ignorar_description') }}
                </p>
            </div>

            <div class="flex w-full items-center gap-3">
                <button
                    type="button"
                    wire:click="closeConfirmIgnorarAlertaModal"
                    class="flex-1 rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('portal/alertas.confirm_ignorar_cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="confirmarIgnorarAlerta"
                    wire:loading.attr="disabled"
                    class="flex-[1.2] rounded-lg bg-error py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-error-text disabled:cursor-not-allowed disabled:opacity-70">
                    {{ __('portal/alertas.confirm_ignorar_confirm') }}
                </button>
            </div>
        </div>
    </div>
@endif

@if ($showConfirmAtenderAlertaModal)
    <div
        wire:key="portal-confirm-atender-alerta"
        class="pointer-events-auto fixed inset-0 z-[90] flex items-center justify-center bg-text/40 p-4"
        wire:click.self="closeConfirmAtenderAlertaModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-atender-alerta-title">
        <div
            wire:click.stop
            class="relative flex w-full max-w-[416px] flex-col items-center justify-center overflow-hidden rounded-3xl bg-neutral-0 p-8 pt-10 text-center shadow-elev-card">
            <div
                class="mb-6 flex size-14 shrink-0 items-center justify-center rounded-full bg-success-light"
                aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="text-success-text" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div class="mb-8 flex flex-col gap-2">
                <h2 id="confirm-atender-alerta-title" class="text-xl font-bold leading-tight text-text md:text-[22px]">
                    {{ __('portal/alertas.confirm_atender_title') }}
                </h2>
                <p class="px-2 text-sm leading-relaxed text-neutral-600">
                    {{ __('portal/alertas.confirm_atender_description') }}
                </p>
            </div>

            <div class="flex w-full items-center gap-3">
                <button
                    type="button"
                    wire:click="closeConfirmAtenderAlertaModal"
                    class="flex-1 rounded-lg border border-neutral-200 bg-neutral-0 py-2.5 text-sm font-medium text-primary-600 shadow-elev-control transition-colors hover:bg-neutral-50">
                    {{ __('portal/alertas.confirm_atender_cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="confirmarAtenderAlerta"
                    wire:loading.attr="disabled"
                    class="flex-[1.2] rounded-lg bg-primary-600 py-2.5 text-sm font-medium text-neutral-0 shadow-elev-control transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-70">
                    {{ __('portal/alertas.confirm_atender_confirm') }}
                </button>
            </div>
        </div>
    </div>
@endif
