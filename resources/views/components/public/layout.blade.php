@props(['title' => null, 'metaDescription' => null, 'canonical' => null, 'robots' => 'index,follow', 'ogImage' => null, 'schema' => null, 'seo' => null])
@php
    $title = $seo['title'] ?? $title ?? $siteSettings->get('business.name', 'Ceylon Vacation Venues');
    $metaDescription = $seo['description'] ?? $metaDescription;
    $canonical = $seo['canonical'] ?? $canonical ?? url()->current();
    $robots = $seo['robots'] ?? $robots;
    $ogTitle = $seo['og_title'] ?? $title;
    $ogDescription = $seo['og_description'] ?? $metaDescription;
    $ogImage = $seo['og_image'] ?? $ogImage;
    $ogImageAlt = $seo['og_image_alt'] ?? null;
    $schemas = $seo['schemas'] ?? ($schema ? [$schema] : []);
    $businessName = $siteSettings->get('business.name', 'Ceylon Vacation Venues');
    $logoUrl = $siteSettings->get('branding.logo_url');
    $faviconUrl = $siteSettings->get('branding.favicon_url');
    $phone = $siteSettings->get('contact.phone');
    $email = $siteSettings->get('contact.email');
    $address = $siteSettings->get('contact.address');
    $whatsApp = $siteSettings->get('contact.whatsapp_number');
    $whatsAppDigits = is_string($whatsApp) ? preg_replace('/\D+/', '', $whatsApp) : null;
    $aboutUrl = $sitePages->get('about')['url'] ?? null;
    $visaUrl = $sitePages->get('visa-extension')['url'] ?? null;
    $baggageUrl = $sitePages->get('baggage-transport')['url'] ?? null;
    $privacyUrl = $sitePages->get('privacy-policy')['url'] ?? null;
    $termsUrl = $sitePages->get('terms-and-conditions')['url'] ?? null;
    $navigation = array_filter([
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'About', 'url' => $aboutUrl],
        ['label' => 'Packages', 'url' => route('packages.index')],
        ['label' => 'Vehicle Rental', 'url' => route('vehicles.index')],
        ['label' => 'Visa Extension', 'url' => $visaUrl],
        ['label' => 'Baggage Transport', 'url' => $baggageUrl],
        ['label' => 'Gallery', 'url' => route('gallery.index')],
        ['label' => 'Blog', 'url' => route('blog.index')],
        ['label' => 'Contact', 'url' => route('inquiries.contact.create')],
    ], fn($item) => filled($item['url']));
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="{{ $robots }}">
    <meta name="theme-color" content="#062d50">
    <title>{{ $title }}</title>@if($metaDescription)
    <meta name="description" content="{{ $metaDescription }}">@endif
    <link rel="canonical" href="{{ $canonical }}">@if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">@endif
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $businessName }}">
    <meta property="og:title" content="{{ $ogTitle }}">@if($ogDescription)
    <meta property="og:description" content="{{ $ogDescription }}">@endif
    <meta property="og:url" content="{{ $canonical }}">@if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">@if($ogImageAlt)
    <meta property="og:image:alt" content="{{ $ogImageAlt }}">@endif @endif
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $ogTitle }}">@if($ogDescription)
    <meta name="twitter:description" content="{{ $ogDescription }}">@endif @if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">@endif
    @foreach(array_filter($schemas) as $item)
        <script
            type="application/ld+json">{!! json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endforeach
    @vite(['resources/css/app.css', 'resources/js/public.js'])
</head>

<body class="min-h-screen overflow-x-hidden bg-[#f8fbfb] font-sans text-slate-800 antialiased">
    <a href="#main-content"
        class="fixed left-3 top-3 z-[100] -translate-y-20 rounded-lg bg-white px-4 py-3 font-bold text-[#082d4f] shadow-xl focus:translate-y-0">Skip
        to content</a>
    <header class="relative z-50 bg-[#062d50] text-white">
        <div class="border-b border-white/10 bg-[#052642]">
            <div
                class="mx-auto flex min-h-9 max-w-[90rem] items-center justify-between gap-4 px-4 text-xs text-sky-100 sm:px-6 lg:px-8">
                <p class="truncate">Sri Lanka travel, thoughtfully arranged</p>
                <div class="flex shrink-0 items-center gap-4">@if($phone)<a
                    href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}"
                    class="hidden items-center gap-1.5 sm:flex"><x-public.icon name="phone"
                class="size-3.5" />{{ $phone }}</a>@endif @if($email)<a href="mailto:{{ $email }}"
                            class="hidden md:block">{{ $email }}</a>@endif</div>
            </div>
        </div>
        <div class="mx-auto flex min-h-20 max-w-[90rem] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3"
                aria-label="{{ $businessName }} home">@if($logoUrl)<span class="rounded-xl bg-white px-3 py-1.5"><img
                    src="{{ $logoUrl }}" alt="{{ $businessName }}" width="190" height="64"
                class="max-h-12 w-auto max-w-[170px] object-contain"></span>@else<span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full border border-cyan-300/30 bg-white/10 font-display text-xl font-bold text-cyan-200">CV</span><span
                        class="max-w-48 text-sm font-bold leading-tight tracking-wide sm:text-base">{{ $businessName }}</span>@endif</a>
            <nav aria-label="Primary navigation" class="hidden items-center gap-0.5 xl:flex">
                @foreach($navigation as $item)<a href="{{ $item['url'] }}"
                class="inline-flex min-h-11 items-center rounded-lg px-2.5 text-[13px] font-semibold text-sky-50 transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-300">{{ $item['label'] }}</a>@endforeach
            </nav>
            <div class="flex shrink-0 items-center gap-2">@if($whatsAppDigits)<a
                href="https://wa.me/{{ $whatsAppDigits }}" target="_blank" rel="noopener noreferrer"
                class="hidden min-h-11 items-center gap-2 rounded-full bg-[#31b879] px-4 text-sm font-bold text-white shadow-lg shadow-black/10 sm:inline-flex"><x-public.icon
            name="whatsapp" class="size-5" /> WhatsApp</a>@endif<button type="button" data-navigation-toggle
                    aria-expanded="false" aria-controls="mobile-navigation"
                    class="inline-flex size-11 items-center justify-center rounded-xl border border-white/20 xl:hidden"><span
                        class="sr-only">Toggle navigation</span><x-public.icon name="menu" class="size-6"
                        data-navigation-open-icon /><x-public.icon name="close" class="size-6"
                        data-navigation-close-icon hidden /></button></div>
        </div>
        <div id="mobile-navigation" data-mobile-navigation hidden
            class="absolute inset-x-0 top-full max-h-[calc(100svh-7rem)] overflow-y-auto border-t border-white/10 bg-[#062d50] px-4 pb-6 shadow-2xl xl:hidden">
            <nav aria-label="Mobile navigation" class="mx-auto grid max-w-3xl gap-1 py-4">
                @foreach($navigation as $item)<a href="{{ $item['url'] }}"
                    class="flex min-h-12 items-center justify-between rounded-xl px-4 font-semibold text-sky-50 hover:bg-white/10">{{ $item['label'] }}<x-public.icon
                name="arrow" class="size-4 text-cyan-300" /></a>@endforeach @if($whatsAppDigits)<a
                            href="https://wa.me/{{ $whatsAppDigits }}" target="_blank" rel="noopener noreferrer"
                            class="mt-3 flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#31b879] px-4 font-bold"><x-public.icon
                        name="whatsapp" /> Chat on WhatsApp</a>@endif</nav>
        </div>
    </header>
    <main id="main-content">{{ $slot }}</main>
    <footer class="relative overflow-hidden bg-[#052642] text-sky-50">
        <div class="absolute -right-24 -top-24 size-80 rounded-full bg-cyan-500/10 blur-3xl"></div>
        <div
            class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-2 lg:grid-cols-[1.2fr_.8fr_.8fr_1fr] lg:px-8 lg:py-20">
            <div><a href="{{ route('home') }}" class="inline-flex items-center gap-3">@if($logoUrl)<img
                src="{{ $logoUrl }}" alt="{{ $businessName }}" width="190" height="64" loading="lazy"
            class="max-h-16 max-w-[210px] object-contain">@else<span
                        class="font-display text-2xl font-semibold">{{ $businessName }}</span>@endif</a>@if($siteSettings->get('footer.content'))
                            <div class="footer-content mt-5 max-w-sm text-sm leading-6 text-sky-100/70">
                        {!! $siteSettings->get('footer.content') !!}</div>@else<p
                        class="mt-5 max-w-sm text-sm leading-6 text-sky-100/70">Personal travel services for discovering Sri
                    Lanka with confidence and ease.</p>@endif @if($siteSocialLinks->isNotEmpty())
                        <div class="mt-6 flex flex-wrap gap-2">@foreach($siteSocialLinks as $social)<a href="{{ $social->url }}"
                            target="_blank" rel="noopener noreferrer"
                            aria-label="{{ $social->label ?: ucfirst($social->platform) }}"
                        class="inline-flex min-h-11 items-center rounded-full border border-white/15 px-4 text-xs font-bold uppercase tracking-wider hover:bg-white/10">{{ $social->label ?: $social->platform }}</a>@endforeach
                    </div>@endif
            </div>
            <div>
                <h2 class="font-display text-lg font-semibold">Explore</h2>
                <ul class="mt-5 space-y-3 text-sm text-sky-100/70">
                    <li><a href="{{ route('packages.index') }}">Tour packages</a></li>
                    <li><a href="{{ route('vehicles.index') }}">Vehicle rental</a></li><li><a href="{{ route('properties.index') }}">Villas & Houses</a></li>@if($visaUrl)
                    <li><a href="{{ $visaUrl }}">Visa extension</a></li>@endif @if($baggageUrl)
                    <li><a href="{{ $baggageUrl }}">Baggage transport</a></li>@endif<li><a
                            href="{{ route('gallery.index') }}">Gallery</a></li>
                </ul>
            </div>
            <div>
                <h2 class="font-display text-lg font-semibold">Quick links</h2>
                <ul class="mt-5 space-y-3 text-sm text-sky-100/70">
                    <li><a href="{{ route('home') }}">Home</a></li>@if($aboutUrl)
                    <li><a href="{{ $aboutUrl }}">About us</a></li>@endif<li><a href="{{ route('blog.index') }}">Travel
                            blog</a></li>
                    <li><a href="{{ route('inquiries.contact.create') }}">Contact us</a></li>@if($privacyUrl)
                    <li><a href="{{ $privacyUrl }}">Privacy policy</a></li>@endif @if($termsUrl)
                    <li><a href="{{ $termsUrl }}">Terms & conditions</a></li>@endif
                </ul>
            </div>
            <div>
                <h2 class="font-display text-lg font-semibold">Contact</h2>
                <address class="mt-5 space-y-4 text-sm not-italic text-sky-100/70">@if($phone)<a
                    href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}"
                    class="flex min-h-11 items-start gap-3"><x-public.icon name="phone"
                class="mt-0.5 size-5 shrink-0 text-cyan-300" /><span>{{ $phone }}</span></a>@endif
                    @if($email)<a href="mailto:{{ $email }}"
                        class="flex min-h-11 items-start gap-3 break-all"><x-public.icon name="mail"
                    class="mt-0.5 size-5 shrink-0 text-cyan-300" /><span>{{ $email }}</span></a>@endif
                    @if($address)
                        <p class="flex items-start gap-3"><x-public.icon name="pin"
                    class="mt-0.5 size-5 shrink-0 text-cyan-300" /><span>{{ $address }}</span></p>@endif
                </address>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-5 text-xs text-sky-100/50 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>© {{ date('Y') }} {{ $businessName }}. All rights reserved.</p>
                <p>Travel across Sri Lanka, your way.</p>
            </div>
        </div>
    </footer>
</body>

</html>