@php
    $navItems = [
        [
            'pattern' => 'portal.dashboard',
            'url' => route('portal.dashboard'),
            'label' => __('portal/sidebar.nav_dashboard'),
            'icon' => 'dashboard',
        ],
        [
            'pattern' => 'portal.pacientes.*',
            'url' => route('portal.pacientes.index'),
            'label' => __('portal/sidebar.nav_pacientes'),
            'icon' => 'patients',
        ],
        [
            'pattern' => 'portal.alertas.*',
            'url' => route('portal.alertas.index'),
            'label' => __('portal/sidebar.nav_alertas'),
            'icon' => 'alertas',
        ],
    ];

    if (auth()->user()?->can('access-centro-portal')) {
        $navItems[] = [
            'pattern' => 'portal.personal.*',
            'url' => route('portal.personal.index'),
            'label' => __('portal/sidebar.nav_personal'),
            'icon' => 'personal',
        ];
    }
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
                    @if ($item['icon'] === 'dashboard')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <rect x="3" y="3" width="7" height="9" rx="1" />
                            <rect x="14" y="3" width="7" height="5" rx="1" />
                            <rect x="14" y="12" width="7" height="9" rx="1" />
                            <rect x="3" y="16" width="7" height="5" rx="1" />
                        </svg>
                    @elseif ($item['icon'] === 'patients')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    @elseif ($item['icon'] === 'alertas')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    @elseif ($item['icon'] === 'personal')
                        <svg @class(['size-[18px] shrink-0', 'text-accent-600' => $active, 'text-neutral-600 group-hover:text-primary-600' => ! $active]) viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
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
