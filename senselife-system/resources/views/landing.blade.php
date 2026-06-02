<x-layouts.app :title="__('landing.title')">
    <div class="relative min-h-screen overflow-hidden bg-background">
        <div
            class="pointer-events-none absolute -left-32 top-0 size-[420px] rounded-full bg-primary-100/60 blur-3xl"
            aria-hidden="true"></div>
        <div
            class="pointer-events-none absolute -right-24 bottom-0 size-[380px] rounded-full bg-secondary/40 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-10 lg:px-10 lg:py-14">
            <header class="flex items-center justify-between gap-4">
                <x-senselife-brand size="lg" />

                @auth
                    <a href="{{ route('dashboard') }}"
                        class="rounded-lg border border-neutral-200 bg-neutral-0 px-4 py-2 text-sm font-medium text-primary-600 shadow-elev-control transition hover:bg-accent-50">
                        {{ __('landing.dashboard') }}
                    </a>
                @endauth
            </header>

            <main class="mt-12 flex flex-1 flex-col items-center gap-12 lg:mt-16 lg:flex-row lg:items-center lg:gap-16">
                <div class="w-full max-w-xl lg:flex-1">
                    <p class="font-mono text-xs font-medium uppercase tracking-widest text-primary-600">
                        {{ __('landing.bpm_label') }}
                    </p>
                    <h1 class="mt-3 font-display text-4xl font-bold leading-tight tracking-tight text-text sm:text-5xl">
                        {{ config('app.name') }}
                    </h1>
                    <p class="mt-4 text-lg font-medium text-primary-700">
                        {{ __('landing.tagline') }}
                    </p>
                    <p class="mt-4 max-w-lg text-base leading-relaxed text-neutral-600">
                        {{ __('landing.description') }}
                    </p>

                    <ul class="mt-8 flex flex-wrap gap-3 text-sm text-neutral-700">
                        <li
                            class="rounded-full border border-info-border bg-info-light px-3 py-1 font-medium text-info-text">
                            {{ __('landing.feature_monitor') }}
                        </li>
                        <li
                            class="rounded-full border border-warning-border bg-warning-light px-3 py-1 font-medium text-warning-text">
                            {{ __('landing.feature_alerts') }}
                        </li>
                        <li
                            class="rounded-full border border-success-border bg-success-light px-3 py-1 font-medium text-success-text">
                            {{ __('landing.feature_centers') }}
                        </li>
                    </ul>

                    <div class="mt-10 grid gap-4 sm:grid-cols-2">
                        <a href="{{ route('portal.login') }}"
                            class="group flex flex-col rounded-2xl border border-neutral-200 bg-neutral-0 p-5 shadow-elev-card transition hover:border-primary-300 hover:shadow-elev-control">
                            <span class="font-display text-lg font-semibold text-primary-600">
                                {{ __('landing.portal_cta') }}
                            </span>
                            <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-accent-600">
                                {{ __('landing.enter') }}
                                <svg class="size-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </a>

                        <a href="{{ route('admin.login') }}"
                            class="group flex flex-col rounded-2xl border border-primary-200 bg-gradient-to-br from-primary-600 to-primary-800 p-5 text-neutral-0 shadow-elev-card transition hover:from-primary-700 hover:to-primary-900">
                            <span class="font-display text-lg font-semibold">
                                {{ __('landing.admin_cta') }}
                            </span>
                            <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-neutral-0">
                                {{ __('landing.enter') }}
                                <svg class="size-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>

                <div class="w-full max-w-lg lg:flex-1">
                    <div
                        class="relative overflow-hidden rounded-3xl border border-neutral-200 bg-neutral-0 p-6 shadow-elev-card sm:p-8">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-neutral-500">ECG</p>
                                <p class="mt-1 flex items-baseline gap-2">
                                    <span
                                        class="landing-pulse-dot font-display text-4xl font-extrabold tabular-nums text-error">72</span>
                                    <span class="text-sm font-semibold text-neutral-500">bpm</span>
                                </p>
                            </div>
                            <div
                                class="flex items-center gap-1.5 rounded-full border border-error-border bg-error-light px-2.5 py-1">
                                <span class="landing-pulse-dot size-2 rounded-full bg-error" aria-hidden="true"></span>
                                <span class="text-[10px] font-bold uppercase tracking-wide text-error">Live</span>
                            </div>
                        </div>

                        <div
                            class="relative mt-6 h-36 overflow-hidden rounded-2xl border border-neutral-100 bg-neutral-50">
                            <div
                                class="landing-ecg-scan pointer-events-none absolute inset-0 z-[1] w-16 bg-gradient-to-r from-transparent via-error/15 to-transparent"
                                aria-hidden="true"></div>
                            <svg class="relative z-0 h-full w-full" viewBox="0 0 400 120" preserveAspectRatio="none"
                                aria-hidden="true">
                                <path class="landing-ecg-path"
                                    d="M0,60 L30,60 L38,60 L42,20 L46,95 L50,45 L54,75 L58,60 L90,60 L98,60 L102,25 L106,90 L110,50 L114,70 L118,60 L150,60 L158,60 L162,18 L166,98 L170,42 L174,68 L178,60 L210,60 L218,60 L222,22 L226,88 L230,48 L234,72 L238,60 L270,60 L278,60 L282,28 L286,85 L290,52 L294,65 L298,60 L330,60 L338,60 L342,15 L346,100 L350,40 L354,70 L358,60 L400,60" />
                            </svg>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-info-light px-3 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-info-text">FR</p>
                                <p class="mt-0.5 font-display text-xl font-bold text-info">38 <span
                                        class="text-xs font-semibold text-neutral-500">rpm</span></p>
                            </div>
                            <div class="rounded-xl bg-success-light px-3 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-success-text">Estado</p>
                                <p class="mt-0.5 text-sm font-semibold text-success-text">Estable</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="mt-12 border-t border-neutral-200 pt-6 text-center text-xs text-neutral-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </footer>
        </div>
    </div>
</x-layouts.app>
