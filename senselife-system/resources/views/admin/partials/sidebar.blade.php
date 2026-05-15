@php
    $navItems = [
        [
            'pattern' => 'admin.dashboard',
            'url' => route('admin.dashboard'),
            'label' => __('admin/sidebar.nav_dashboard'),
            'icon' => 'dashboard',
        ],
        [
            'pattern' => 'admin.centros*',
            'url' => '#',
            'label' => __('admin/sidebar.nav_centros'),
            'icon' => 'building',
        ],
        [
            'pattern' => 'admin.pacientes*',
            'url' => '#',
            'label' => __('admin/sidebar.nav_pacientes'),
            'icon' => 'users',
        ],
        [
            'pattern' => 'admin.calendario*',
            'url' => '#',
            'label' => __('admin/sidebar.nav_calendario'),
            'icon' => 'calendar',
        ],
    ];
@endphp

<aside
    class="sticky top-0 flex h-screen w-60 shrink-0 flex-col overflow-y-auto border-r border-neutral-200 bg-neutral-0 [&::-webkit-scrollbar]:w-1 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-neutral-200">
    <div class="px-4 pb-2 pt-6">
        <span class="block px-2 text-[11px] font-bold uppercase tracking-wider text-neutral-400">
            {{ __('admin/sidebar.section_panel') }}
        </span>
    </div>

    <nav class="flex w-full flex-col" aria-label="{{ __('admin/sidebar.section_panel') }}">
        @foreach ($navItems as $item)
            @php
                $active = request()->routeIs($item['pattern']);
            @endphp
            <a
                href="{{ $item['url'] }}"
                @if ($item['url'] === '#') onclick="return false;" aria-disabled="true" @endif
                @class([
                    'flex items-center border-l-4 px-4 py-2 transition-all duration-200',
                    'border-accent-400 bg-accent-50' => $active,
                    'group border-transparent hover:bg-neutral-50' => ! $active,
                ])
            >
                <div class="flex items-center gap-3 px-2">
                    @if ($item['icon'] === 'dashboard')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                        </svg>
                    @elseif ($item['icon'] === 'building')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                            <path d="M12 11v6" />
                            <path d="M9 14h6" />
                        </svg>
                    @elseif ($item['icon'] === 'users')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    @elseif ($item['icon'] === 'calendar')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
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
