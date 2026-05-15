@props(['title' => null])

<x-layouts.app :title="$title ?? __('admin/header.layout_title')">
    <div class="flex min-h-screen bg-neutral-0">
        @include('admin.partials.sidebar')
        <div class="flex min-h-0 min-w-0 flex-1 flex-col">
            @include('admin.partials.header')
            <main class="min-h-0 flex-1 overflow-y-auto bg-neutral-50">
                <div class="mx-auto max-w-6xl px-6 py-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</x-layouts.app>
