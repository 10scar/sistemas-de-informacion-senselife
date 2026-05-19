<x-layouts.app :title="__('Acceso panel clínico')">
    <div class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-primary-900 to-accent-800 px-4 py-12 sm:px-6 sm:py-16">
        <div class="w-full max-w-[420px] rounded-3xl bg-neutral-0 p-10 shadow-elev-card">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-neutral-0">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75" />
                        <path d="M10 8.5v7l6-3.5-6-3.5z" fill="currentColor" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="font-display text-xl font-bold leading-tight text-primary-600">
                        {{ config('app.name') }}
                    </p>
                    <p class="mt-0.5 text-[11px] font-medium leading-snug text-neutral-600 sm:text-xs">
                        {{ __('auth.portal_login_tagline') }}
                    </p>
                </div>
            </div>

            <p class="mt-7 font-mono text-xs font-medium uppercase tracking-wider text-neutral-500">
                {{ __('auth.portal_login_section') }}
            </p>

            <p class="mt-2 text-sm leading-relaxed text-neutral-600">
                {{ __('auth.portal_login_intro') }}
            </p>

            @error('inactive_account')
                <div class="mt-5 rounded-lg border border-warning-border bg-warning-light px-4 py-3" role="alert">
                    <p class="flex items-center gap-2 text-sm font-semibold text-warning-text">
                        <svg class="size-4 shrink-0" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true">
                            <path d="M8 2.5L14.5 13H1.5L8 2.5z" stroke="currentColor" stroke-width="1.33"
                                stroke-linejoin="round" />
                            <path d="M8 6.5v3.5M8 11.5h.01" stroke="currentColor" stroke-width="1.33"
                                stroke-linecap="round" />
                        </svg>
                        {{ __('auth.portal_inactive_title') }}
                    </p>
                    <p class="mt-1 text-sm text-warning-text">
                        {{ $message }}
                    </p>
                </div>
            @enderror

            <form method="post" action="{{ route('portal.login.store') }}" class="mt-5 space-y-5">
                @csrf

                <div>
                    <label for="portal-email" class="flex items-baseline gap-1 text-sm font-medium text-text">
                        <span>{{ __('auth.portal_email_label') }}</span>
                        <span class="text-sm font-semibold text-error" aria-hidden="true">*</span>
                    </label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-400"
                            aria-hidden="true">
                            <svg class="size-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.5 3.5h11v9h-11v-9z" stroke="currentColor" stroke-width="1.33"
                                    stroke-linejoin="round" />
                                <path d="M2.5 5.5l5.5 3.5 5.5-3.5" stroke="currentColor" stroke-width="1.33"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <input id="portal-email" name="email" type="email" value="{{ old('email') }}" required
                            autofocus autocomplete="username" placeholder="nombre@institución.org"
                            class="block h-[42px] w-full rounded-lg border border-neutral-300 bg-neutral-0 pl-10 pr-3 text-sm text-text placeholder:text-neutral-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="portal-password" class="flex items-baseline gap-1 text-sm font-medium text-text">
                        <span>{{ __('auth.portal_password_label') }}</span>
                        <span class="text-sm font-semibold text-error" aria-hidden="true">*</span>
                    </label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-400"
                            aria-hidden="true">
                            <svg class="size-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="5" y="7" width="6" height="6" rx="1" stroke="currentColor"
                                    stroke-width="1.33" />
                                <path d="M5 7V5a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.33"
                                    stroke-linecap="round" />
                            </svg>
                        </span>
                        <input id="portal-password" name="password" type="password" required
                            autocomplete="current-password" placeholder="••••••••"
                            class="block h-[42px] w-full rounded-lg border border-neutral-300 bg-neutral-0 pl-10 pr-3 text-sm text-text placeholder:text-neutral-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input id="portal-remember" name="remember" type="checkbox" value="1"
                        class="size-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500" />
                    <label for="portal-remember" class="text-sm text-neutral-600">{{ __('Recordarme') }}</label>
                </div>

                <button type="submit"
                    class="flex h-[50px] w-full items-center justify-center rounded-lg bg-primary-600 text-sm font-semibold text-neutral-0 shadow-elev-control transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-neutral-0">
                    {{ __('auth.portal_login_submit') }}
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-neutral-500">
                <a href="{{ route('admin.login') }}"
                    class="text-primary-600 underline-offset-2 hover:underline">{{ __('auth.portal_admin_login_link') }}</a>
            </p>
        </div>
    </div>
</x-layouts.app>
