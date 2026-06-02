@php
    $centroNombre = auth()->user()?->medicoPerfil?->centroMedico?->nombre;
@endphp

<header
    class="sticky top-0 z-50 flex h-[60px] w-full items-center justify-between border-b border-neutral-200 bg-neutral-0 px-6 shadow-elev-control">
    <div class="flex min-w-0 items-center">
        <a href="{{ route('portal.pacientes.index') }}" class="flex shrink-0 items-center gap-3">
            <x-senselife-logo-mark box-class="size-9 rounded-lg" icon-class="size-5" />
            <span class="font-display text-xl font-bold leading-none tracking-tight text-primary-600">
                {{ config('app.name') }}
            </span>
        </a>

        <span class="mx-[18px] h-7 w-px shrink-0 bg-neutral-200" aria-hidden="true"></span>

        <span class="truncate text-sm font-medium text-neutral-600">
            {{ $centroNombre ?? __('portal/header.panel_title') }}
        </span>
    </div>

    <details class="relative shrink-0">
        <summary
            class="flex cursor-pointer list-none items-center gap-2 rounded-lg border border-neutral-200 bg-neutral-0 px-3 py-1.5 transition-colors hover:bg-neutral-50 [&::-webkit-details-marker]:hidden">
            <span
                class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600"
                aria-hidden="true">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path
                        d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </span>
            <div class="min-w-0 text-left">
                <span class="block truncate text-[13px] font-semibold leading-tight text-text">
                    {{ auth()->user()->name }}
                </span>
                <span class="block truncate text-[11px] font-medium leading-tight text-neutral-400">
                    {{ auth()->user()->rol?->nombre ?? '' }}
                </span>
            </div>
            <svg
                class="ml-1 size-3 shrink-0 text-neutral-400"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true">
                <path
                    d="M6 9l6 6 6-6"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </summary>

        <div
            class="absolute right-0 top-full z-50 mt-1 min-w-[12rem] rounded-lg border border-neutral-200 bg-neutral-0 py-1 shadow-elev-card">
            <form method="post" action="{{ route('portal.logout') }}" class="block px-1">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-neutral-600 transition hover:bg-neutral-50 hover:text-text"
                    aria-label="{{ __('portal/header.logout_aria') }}">
                    {{ __('portal/header.logout') }}
                </button>
            </form>
        </div>
    </details>
</header>
