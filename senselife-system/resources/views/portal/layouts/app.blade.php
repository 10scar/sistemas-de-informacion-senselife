@props(['title' => null])

<x-layouts.app :title="$title ?? __('portal/header.layout_title')">
    <div class="flex min-h-screen bg-neutral-0">
        @include('portal.partials.sidebar')
        <div class="flex min-h-0 min-w-0 flex-1 flex-col">
            @include('portal.partials.header')
            <main class="min-h-0 flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>
    @auth
        @can('access-portal-panel')
            @livewire('portal.alertas.notifier')
        @endcan
    @endauth
</x-layouts.app>
