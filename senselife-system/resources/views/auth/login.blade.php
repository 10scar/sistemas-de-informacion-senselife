<x-layouts.app title="{{ __('Iniciar sesión') }}">
    <div class="flex min-h-screen flex-col items-center justify-center bg-background p-6">
        <div class="w-full max-w-md rounded-lg border border-neutral-200 bg-neutral-0 p-8 shadow-sm">
            <h1 class="font-display text-xl font-semibold text-text">{{ config('app.name') }}</h1>
            <p class="mt-1 text-sm text-neutral-600">{{ __('Inicia sesión para continuar') }}</p>

            <form method="post" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-text">{{ __('Correo') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="mt-1 block w-full rounded-md border border-neutral-300 bg-background px-3 py-2 text-sm text-text shadow-sm focus:border-accent-500 focus:outline-none focus:ring-1 focus:ring-accent-500" />
                    @error('email')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-text">{{ __('Contraseña') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="mt-1 block w-full rounded-md border border-neutral-300 bg-background px-3 py-2 text-sm text-text shadow-sm focus:border-accent-500 focus:outline-none focus:ring-1 focus:ring-accent-500" />
                    @error('password')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox" value="1"
                        class="size-4 rounded border-neutral-300 text-accent-600 focus:ring-accent-500" />
                    <label for="remember" class="text-sm text-neutral-700">{{ __('Recordarme') }}</label>
                </div>

                <button type="submit"
                    class="w-full rounded-md bg-accent-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                    {{ __('Entrar') }}
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-neutral-500">
                <a href="{{ route('admin.login') }}" class="text-accent-600 underline-offset-2 hover:underline">{{ __('auth.admin_login_link') }}</a>
            </p>
        </div>
    </div>
</x-layouts.app>
