@props(['title' => null])

<x-layouts.app :title="$title ?? __('admin/header.layout_title')">
    <div class="flex min-h-screen bg-neutral-0">
        @include('admin.partials.sidebar')
        <div class="flex min-h-0 min-w-0 flex-1 flex-col">
            @include('admin.partials.header')
            {{-- Sin overflow aquí: un scrollport en <main> recorta overlays position:fixed (modales parecen insertados al final del contenido). El scroll va en el viewport. --}}
            <main class="min-h-0 flex-1 bg-neutral-50">
                <div class="mx-auto max-w-[1272px] px-4 py-6 md:px-8 md:py-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</x-layouts.app>
