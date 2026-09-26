@props(['title', 'eyebrow' => 'Administration'])

@php
    $navigation = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Packages', 'route' => 'admin.packages.index', 'icon' => 'packages'],
        ['label' => 'Vehicles', 'route' => 'admin.vehicles.index', 'icon' => 'vehicles'],
        ['label' => 'Villas & Houses', 'route' => 'admin.properties.index', 'icon' => 'properties'],
        ['label' => 'Inquiries', 'route' => 'admin.inquiries.index', 'icon' => 'inquiries'],
        ['label' => 'Visa', 'route' => 'admin.visa.index', 'icon' => 'visa'],
        ['label' => 'Baggage', 'route' => 'admin.baggage.index', 'icon' => 'baggage'],
        ['label' => 'Gallery', 'route' => 'admin.gallery.index', 'icon' => 'gallery'],
        ['label' => 'Blog', 'route' => 'admin.blog.index', 'icon' => 'blog'],
        ['label' => 'Pages', 'route' => 'admin.pages.index', 'icon' => 'pages'],
        ['label' => 'SEO', 'route' => 'admin.seo.index', 'icon' => 'seo'],
        ['label' => 'Redirects', 'route' => 'admin.redirects.index', 'icon' => 'redirects'],
        ['label' => 'Settings', 'route' => 'admin.settings.index', 'icon' => 'settings'],
        ...(auth()->user()->can('viewAny', App\Models\User::class) ? [['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users']] : []),
        ['label' => 'Audit Logs', 'route' => 'admin.audit-logs.index', 'icon' => 'audit'],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} | Ceylon Vacation Venues</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen overflow-x-hidden bg-[#f5f7fa] font-sans text-slate-900 antialiased">
    <a href="#admin-content"
        class="sr-only z-50 rounded-lg bg-white px-4 py-3 text-[#082f57] focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip
        to content</a>

    <aside
        class="fixed inset-y-0 left-0 z-30 hidden w-72 overflow-y-auto bg-[#062b50] text-white shadow-xl lg:flex lg:flex-col">
        <div class="flex min-h-24 items-center gap-3 border-b border-white/10 px-6">
            <div class="grid size-11 shrink-0 place-items-center rounded-xl bg-white/10 ring-1 ring-white/15">
                <svg aria-hidden="true" viewBox="0 0 64 64" class="size-7 fill-none stroke-[#f4c667]" stroke-width="3"
                    stroke-linecap="round">
                    <path
                        d="M32 52V24M32 28C22 27 17 21 15 14c8 0 14 3 17 10M32 28c9-2 15-8 17-16-9 1-14 5-17 12M22 53c6-4 14-4 20 0" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="truncate font-semibold tracking-wide">Ceylon Vacation</p>
                <p class="text-xs uppercase tracking-[0.2em] text-sky-100/60">Venues Admin</p>
            </div>
        </div>

        <nav aria-label="Admin navigation" class="flex-1 space-y-1 px-4 py-6">
            @foreach ($navigation as $item)
                <x-admin.nav-item :href="route($item['route'])" :icon="$item['icon']"
                    :active="request()->routeIs($item['route'], str_replace('.index', '.*', $item['route']))">
                    {{ $item['label'] }}
                </x-admin.nav-item>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="mb-3 min-w-0 px-3">
                <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-sky-100/60">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                    class="flex min-h-11 w-full items-center justify-center rounded-xl border border-white/15 px-4 py-2.5 text-sm font-semibold text-white transition focus:outline-none focus:ring-2 focus:ring-[#f4c667] hover:bg-white/10">
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <div class="min-w-0 lg:pl-72">
        <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur-sm">
            <div class="flex min-h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:min-h-20 lg:px-8">
                <details class="group min-w-0 flex-1 lg:hidden">
                    <summary
                        class="-ml-2 flex min-h-11 cursor-pointer list-none items-center gap-3 rounded-xl px-2 text-[#082f57] outline-none focus:ring-2 focus:ring-sky-600 [&::-webkit-details-marker]:hidden">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#082f57] text-white">
                            <svg aria-hidden="true" viewBox="0 0 24 24" class="size-6 fill-none stroke-current"
                                stroke-width="2" stroke-linecap="round">
                                <path d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold">Ceylon Vacation Venues</span>
                            <span class="block text-xs text-slate-500">Menu</span>
                        </span>
                    </summary>
                    <div
                        class="absolute inset-x-0 top-full max-h-[calc(100dvh-4rem)] overflow-y-auto border-t border-slate-200 bg-white p-3 shadow-xl">
                        <nav aria-label="Mobile admin navigation" class="grid gap-1">
                            @foreach ($navigation as $item)
                                <x-admin.nav-item :href="route($item['route'])" :icon="$item['icon']"
                                    :active="request()->routeIs($item['route'], str_replace('.index', '.*', $item['route']))" mobile>
                                    {{ $item['label'] }}
                                </x-admin.nav-item>
                            @endforeach
                        </nav>
                        <form method="POST" action="{{ route('admin.logout') }}"
                            class="mt-3 border-t border-slate-200 pt-3">
                            @csrf
                            <button type="submit"
                                class="min-h-11 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-600">Sign
                                out</button>
                        </form>
                    </div>
                </details>

                <div class="hidden min-w-0 lg:block">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">{{ $eyebrow }}</p>
                    <h1 class="mt-1 truncate text-xl font-semibold tracking-tight text-slate-950">{{ $title }}</h1>
                </div>

                <div class="hidden min-w-0 items-center gap-3 sm:flex">
                    <div class="min-w-0 text-right">
                        <p class="max-w-48 truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs capitalize text-slate-500">
                            {{ str_replace('_', ' ', auth()->user()->role->value) }}</p>
                    </div>
                    <div class="grid size-10 shrink-0 place-items-center rounded-full bg-sky-100 font-semibold text-[#082f57] ring-1 ring-sky-200"
                        aria-hidden="true">
                        {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                    </div>
                </div>
            </div>
        </header>

        <main id="admin-content" class="min-w-0 px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
            <div class="mx-auto w-full max-w-[1600px] min-w-0">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>
