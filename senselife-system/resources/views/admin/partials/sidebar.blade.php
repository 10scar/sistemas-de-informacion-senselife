@php
    $navItems = [
        [
            'pattern' => 'admin.dashboard',
            'url' => route('admin.dashboard'),
            'label' => __('admin/sidebar.nav_dashboard'),
            'icon' => 'dashboard',
        ],
        [
            'pattern' => 'admin.centros-medicos.*',
            'url' => route('admin.centros-medicos.index'),
            'label' => __('admin/sidebar.nav_centros'),
            'icon' => 'building',
        ],
        [
            'pattern' => 'admin.dispositivos.*',
            'url' => route('admin.dispositivos.index'),
            'label' => __('admin/sidebar.nav_dispositivos'),
            'icon' => 'device',
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
                    @elseif ($item['icon'] === 'device')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <rect x="4" y="2" width="16" height="20" rx="3" ry="3" />
                            <line x1="10" y1="18" x2="14" y2="18" />
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
