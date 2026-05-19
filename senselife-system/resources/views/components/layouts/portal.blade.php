@props(['title' => null, 'section' => null])

<x-layouts.app :title="$title ?? __('Portal clínico')">
    <div class="min-h-screen bg-background">
        <header class="border-b border-neutral-200 bg-neutral-0 px-6 py-4">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-6">
                    <a href="{{ route('portal.dashboard') }}" class="font-display text-lg font-semibold text-primary-600 hover:text-primary-700">
                        {{ config('app.name') }}
                    </a>
                    @if ($section)
                        <span class="rounded-md bg-secondary px-2 py-0.5 text-xs font-medium text-primary-800">{{ $section }}</span>
                    @endif
                </div>
                <nav class="flex flex-wrap items-center gap-4 text-sm">
                    <form method="post" action="{{ route('portal.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-neutral-600 underline-offset-4 hover:text-text hover:underline">
                            {{ __('Cerrar sesión') }}
                        </button>
                    </form>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-6xl px-6 py-8">
            {{ $slot }}
        </main>
    </div>
</x-layouts.app>
