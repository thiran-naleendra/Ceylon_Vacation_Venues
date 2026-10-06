<x-public.layout :seo="$seo">@php($heroImage = data_get($hero, 'image'))
    <header class="relative isolate overflow-hidden bg-[#062d50] px-4 py-16 text-white sm:px-6 sm:py-24">
        @if($heroImage)<x-public.hero-image :image="$heroImage" :alt="data_get($hero, 'image.alt', $page->title)"
            class="absolute inset-0 -z-20 size-full object-cover" />
        <div class="absolute inset-0 -z-10 bg-[#041f37]/78"></div>@endif<div class="mx-auto max-w-6xl">
            <nav aria-label="Breadcrumb" class="text-sm text-cyan-200"><a href="{{ route('home') }}">Home</a> / <span
                    aria-current="page">{{ $page->title }}</span></nav>
            <p class="mt-8 text-xs font-bold uppercase tracking-[.24em] text-cyan-300">Travel service</p>
            <h1 class="mt-3 max-w-4xl font-display text-4xl font-semibold tracking-tight sm:text-6xl">
                {{ data_get($hero, 'title') ?: $page->title }}</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-sky-100/80">{{ data_get($hero, 'text') ?: $page->summary }}</p>
            <a href="#inquiry"
                class="mt-8 inline-flex min-h-12 items-center gap-2 rounded-full bg-white px-6 font-bold text-[#082d4f]">Start
                your inquiry <x-public.icon name="arrow" class="size-4" /></a>
        </div>
    </header>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-20 xl:grid-cols-[minmax(0,1fr)_30rem] xl:gap-14 xl:px-8">
        <div class="min-w-0">
            <div class="rich-content">@if($page->body){!! $page->body !!}@elseif($type === App\Enums\InquiryType::Visa)
                <h2>Visa assistance for your stay in Sri Lanka</h2>
                <p>Coming to Sri Lanka? Need to extend your visa? Planning to do business in Sri Lanka? We can help with
                    your visa assistance needs and guide you through the next steps.</p>
                <h2>Information to prepare</h2>
                <ul>
                    <li>Your current visa expiry date</li>
                    <li>Your arrival date and nationality</li>
                    <li>The extension period you want to request</li>
            </ul>@else<h2>Missed your baggage at the airport?</h2>
                    <p>Report your missing airport baggage to our team. Share the airport, flight and baggage reference
                        details you have, and we will help with the recovery process.</p>
                    <h2>Information to prepare</h2>
                    <ul>
                        <li>Your arrival airport and flight number</li>
                        <li>The airport baggage report reference, if available</li>
                        <li>A short description of each missing bag</li>
                </ul>@endif
            </div>
        </div>
        <aside class="min-w-0 w-full max-w-3xl justify-self-center xl:max-w-none xl:justify-self-stretch"><x-public.service-inquiry-form :type="$type" :token="$token" :whats-app="$whatsApp" /></aside>
    </div>
</x-public.layout>
