<x-layouts.app :title="__('Acceso administración')">
    {{-- Colores y sombras: tokens @theme en resources/css/app.css (design.md) --}}
    <div
        class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-primary-900 to-accent-800 px-4 py-12 sm:px-6 sm:py-16">
        <div class="w-full max-w-[420px] rounded-3xl bg-neutral-0 p-10 shadow-elev-card">
            <x-senselife-brand size="md" :tagline="__('auth.admin_login_tagline')" />

            <p class="mt-7 text-sm leading-relaxed text-neutral-600">
                {{ __('auth.admin_login_intro') }}
            </p>

            <form method="post" action="{{ route('admin.login.store') }}" class="mt-5 space-y-5">
                @csrf

                <div>
                    <label for="admin-email" class="flex items-baseline gap-1 text-sm font-medium text-text">
                        <span>{{ __('auth.admin_email_label') }}</span>
                        <span class="text-sm font-semibold text-error" aria-hidden="true">*</span>
                    </label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-400"
                            aria-hidden="true">
                            <svg class="size-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.5 3.5h11v9h-11v-9z"
                                    stroke="currentColor"
                                    stroke-width="1.33"
                                    stroke-linejoin="round" />
                                <path d="M2.5 5.5l5.5 3.5 5.5-3.5" stroke="currentColor" stroke-width="1.33"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <input id="admin-email" name="email" type="email" value="{{ old('email') }}" required
                            autofocus autocomplete="username" placeholder="nombre@institución.org"
                            class="block h-[42px] w-full rounded-lg border border-neutral-300 bg-neutral-0 pl-10 pr-3 text-sm text-text placeholder:text-neutral-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admin-password" class="flex items-baseline gap-1 text-sm font-medium text-text">
                        <span>{{ __('auth.admin_password_label') }}</span>
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
                        <input id="admin-password" name="password" type="password" required
                            autocomplete="current-password" placeholder="••••••••"
                            class="block h-[42px] w-full rounded-lg border border-neutral-300 bg-neutral-0 pl-10 pr-3 text-sm text-text placeholder:text-neutral-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25" />
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input id="admin-remember" name="remember" type="checkbox" value="1"
                        class="size-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500" />
                    <label for="admin-remember" class="text-sm text-neutral-600">{{ __('Recordarme') }}</label>
                </div>

                <button type="submit"
                    class="flex h-[50px] w-full items-center justify-center rounded-lg bg-primary-600 text-sm font-semibold text-neutral-0 shadow-elev-control transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-neutral-0">
                    {{ __('auth.admin_login_submit') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
