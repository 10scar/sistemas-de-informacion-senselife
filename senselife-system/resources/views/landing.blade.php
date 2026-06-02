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
                    <x-landing-monitor-live :payload="$monitorPayload" />
                </div>
            </main>

            <footer class="mt-12 border-t border-neutral-200 pt-6 text-center text-xs text-neutral-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </footer>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/landing-monitor.js'])
    @endpush
</x-layouts.app>
