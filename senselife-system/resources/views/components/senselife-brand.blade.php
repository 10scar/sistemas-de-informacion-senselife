@props([
    'size' => 'md',
    'tagline' => null,
])

@php
    $presets = [
        'sm' => ['box' => 'size-9 rounded-lg', 'icon' => 'size-5'],
        'md' => ['box' => 'size-12 rounded-xl', 'icon' => 'size-7'],
        'lg' => ['box' => 'size-11 rounded-xl', 'icon' => 'size-6'],
    ];
    $preset = $presets[$size] ?? $presets['md'];
@endphp

<div {{ $attributes->merge(['class' => 'flex min-w-0 items-center gap-3']) }}>
    <x-senselife-logo-mark :box-class="$preset['box']" :icon-class="$preset['icon']" />
    <div class="min-w-0">
        <p class="font-display text-xl font-bold leading-tight text-primary-600">
            {{ config('app.name') }}
        </p>
        @if ($tagline)
            <p class="mt-0.5 text-[11px] font-medium leading-snug text-neutral-600 sm:text-xs">
                {{ $tagline }}
            </p>
        @endif
    </div>
</div>
