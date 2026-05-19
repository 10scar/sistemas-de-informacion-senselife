@php
    $navItems = [
        [
            'pattern' => 'portal.pacientes.*',
            'url' => route('portal.pacientes.index'),
            'label' => __('portal/sidebar.nav_pacientes'),
            'icon' => 'patients',
        ],
    ];
@endphp

<aside
    class="sticky top-0 flex h-screen w-60 shrink-0 flex-col overflow-y-auto border-r border-neutral-200 bg-neutral-0 [&::-webkit-scrollbar]:w-1 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-neutral-200">
    <div class="px-4 pb-2 pt-6">
        <span class="block px-2 text-[11px] font-bold uppercase tracking-wider text-neutral-400">
            {{ __('portal/sidebar.section_panel') }}
        </span>
    </div>

    <nav class="flex w-full flex-col" aria-label="{{ __('portal/sidebar.section_panel') }}">
        @foreach ($navItems as $item)
            @php
                $active = request()->routeIs($item['pattern']);
            @endphp
            <a
                href="{{ $item['url'] }}"
                @class([
                    'flex items-center border-l-4 px-4 py-2 transition-all duration-200',
                    'border-accent-400 bg-accent-50' => $active,
                    'group border-transparent hover:bg-neutral-50' => ! $active,
                ])
            >
                <div class="flex items-center gap-3 px-2">
                    @if ($item['icon'] === 'patients')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    @endif
                    <span
                        @class([
                            'text-[13px] leading-tight',
                            'font-semibold text-accent-600' => $active,
                            'font-medium text-neutral-600 group-hover:text-primary-600' => ! $active,
                        ])>{{ $item['label'] }}</span>
                </div>
            </a>
        @endforeach
    </nav>
</aside>
