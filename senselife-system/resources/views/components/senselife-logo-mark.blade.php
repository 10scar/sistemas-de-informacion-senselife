@props([
    'boxClass' => 'size-12 rounded-xl',
    'iconClass' => 'size-7',
])

<span
    {{ $attributes->class([
        'flex shrink-0 items-center justify-center bg-primary-600 text-neutral-0 shadow-elev-control',
        $boxClass,
    ]) }}
    aria-hidden="true">
    <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75" />
        <path d="M10 8.5v7l6-3.5-6-3.5z" fill="currentColor" />
    </svg>
</span>
