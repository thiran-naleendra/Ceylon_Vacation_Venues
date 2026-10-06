<x-public.layout :seo="$seo">
    @php
        $heroImage = data_get($hero, 'image');
        $heroTitle = data_get($hero, 'title') ?: ($page->title === 'Home' ? 'Discover Sri Lanka, beautifully arranged' : $page->title);
        $heroText = data_get($hero, 'text') ?: ($page->summary ?: 'Thoughtful tours and practical travel services for a smooth journey across the island.');
        $visaUrl = route('services.visa');
        $baggageUrl = route('services.baggage');
    @endphp
    <section
        class="relative isolate min-h-[36rem] overflow-hidden bg-[#062d50] text-white sm:min-h-[42rem] lg:min-h-[46rem]">
        @if($heroImage)<x-public.hero-image :image="$heroImage" :alt="data_get($hero, 'image.alt', 'Sri Lanka travel experience')" class="absolute inset-0 -z-20 size-full object-cover" />@endif
        <div
            class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(4,31,55,.93)_0%,rgba(5,42,72,.75)_48%,rgba(5,42,72,.25)_100%)]">
        </div>
        <div class="absolute inset-x-0 bottom-0 -z-10 h-36 bg-gradient-to-t from-[#062d50]/60 to-transparent"></div>
        <div
            class="mx-auto flex min-h-[36rem] max-w-7xl items-center px-4 py-20 sm:min-h-[42rem] sm:px-6 lg:min-h-[46rem] lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-5 flex items-center gap-3 text-xs font-bold uppercase tracking-[.28em] text-cyan-200"><span
                        class="h-px w-10 bg-cyan-300"></span>Welcome to Sri Lanka</p>
                <h1 class="font-display text-4xl font-semibold leading-[1.08] tracking-tight sm:text-6xl lg:text-7xl">
                    {{ $heroTitle }}</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-sky-50/85 sm:text-xl">{{ $heroText }}</p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row"><a href="{{ route('packages.index') }}"
                        class="inline-flex min-h-13 items-center justify-center gap-2 rounded-full bg-white px-6 font-bold text-[#082d4f] shadow-xl shadow-black/10">Explore
                        our tours <x-public.icon name="arrow" class="size-5" /></a><a
                        href="{{ route('inquiries.contact.create') }}"
                        class="inline-flex min-h-13 items-center justify-center rounded-full border border-white/40 bg-white/10 px-6 font-bold text-white backdrop-blur-sm">Plan
                        your journey</a></div>
            </div>
        </div>
        <div class="absolute bottom-0 right-0 hidden rounded-tl-[2rem] bg-[#f8fbfb] px-10 py-6 text-[#082d4f] lg:block">
            <p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-700">Island services</p>
            <p class="mt-1 font-display text-xl font-semibold">Tours · Transport · Travel support</p>
        </div>
    </section>

    @if($featuredPackages->isNotEmpty())
        <section class="bg-gradient-to-b from-white to-[#eef7fb] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between"><x-public.section-heading
                        eyebrow="Curated journeys" title="Explore Sri Lanka tours"
                        description="Compare published itineraries, destinations, duration and pricing before choosing your journey." /><a
                        href="{{ route('packages.index') }}"
                        class="inline-flex min-h-11 items-center gap-2 self-start font-bold text-cyan-700">View every
                        package <x-public.icon name="arrow" class="size-4" /></a></div>
                <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($featuredPackages as $package)<x-public.package-card :package="$package" />@endforeach</div>
            </div>
    </section>@endif

    <section class="overflow-hidden bg-gradient-to-br from-[#e5f5f1] via-[#edf8f5] to-[#dff1f3] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <x-public.section-heading eyebrow="Stay your way" title="Villas & houses for your Sri Lanka stay"
                    description="Explore published properties with location, capacity, amenities and pricing details, then inquire directly about availability." />
                <a href="{{ route('properties.index') }}" class="inline-flex min-h-11 items-center gap-2 self-start font-bold text-cyan-700">View all villas & houses <x-public.icon name="arrow" class="size-4" /></a>
            </div>
            @if($featuredProperties->isNotEmpty())
                <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($featuredProperties as $property)<x-public.property-card :property="$property" heading-level="h3" />@endforeach
                </div>
            @else
                <div class="mt-10 grid gap-6 overflow-hidden rounded-[2rem] bg-[#082d4f] p-7 text-white sm:grid-cols-[auto_1fr_auto] sm:items-center sm:p-9">
                    <span class="grid size-14 place-items-center rounded-2xl bg-white/10"><x-public.icon name="home" class="size-7" /></span>
                    <div><h3 class="font-display text-2xl font-semibold">Find a comfortable base for your journey</h3><p class="mt-2 text-sm leading-6 text-sky-100/75">Browse the accommodation page as new villas and houses become available.</p></div>
                    <a href="{{ route('properties.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-full bg-white px-5 font-bold text-[#082d4f]">Explore stays</a>
                </div>
            @endif
        </div>
    </section>

    @if($destinations->isNotEmpty() || filled($page->body))
        <section class="overflow-hidden bg-[#dff1ed] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[.9fr_1.1fr] lg:items-center">
                <div><x-public.section-heading eyebrow="One island, many stories" title="Find your corner of Sri Lanka"
                        description="Coastlines, culture, wildlife and hill-country scenery can all become part of one thoughtfully planned journey." />@if($destinations->isNotEmpty())
                            <div class="mt-8 flex flex-wrap gap-2">@foreach($destinations as $destination)<span
                            class="rounded-full border border-cyan-800/10 bg-white px-4 py-2 text-sm font-semibold text-[#0a5068] shadow-sm">{{ $destination }}</span>@endforeach
                        </div>@endif
                </div>
                <div class="rounded-[2rem] bg-white p-6 shadow-[0_25px_80px_-45px_rgba(8,45,79,.5)] sm:p-9">
                    <div class="rich-content">
                        {!! $page->body ?: '<h2>Travel with room to breathe</h2><p>Choose a complete tour or combine transport and practical travel support around your own plans.</p>' !!}
                    </div><a href="{{ route('inquiries.contact.create') }}"
                        class="mt-7 inline-flex min-h-12 items-center gap-2 rounded-full bg-[#082d4f] px-6 font-bold text-white">Talk
                        to our team <x-public.icon name="arrow" class="size-4" /></a>
                </div>
            </div>
    </section>@endif

    <section class="bg-[#f4f8fc] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mx-auto max-w-7xl"><x-public.section-heading eyebrow="Travel with confidence"
                title="Practical support for your island journey"
                description="Bring the essential parts of your Sri Lanka trip together in one place." align="center" />
            <div class="mt-12 grid grid-cols-1 gap-5 md:grid-cols-3">
                <article class="rounded-[1.75rem] border border-slate-200 bg-white p-7"><span
                        class="flex size-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700"><x-public.icon
                            name="map" class="size-6" /></span>
                    <h3 class="mt-6 font-display text-2xl font-semibold text-[#082d4f]">Local trip planning</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Published itineraries, clear package details and a
                        direct way to ask questions before you travel.</p>
                </article>
                <article class="rounded-[1.75rem] border border-slate-200 bg-white p-7"><span
                        class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700"><x-public.icon
                            name="shield" class="size-6" /></span>
                    <h3 class="mt-6 font-display text-2xl font-semibold text-[#082d4f]">Connected services</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Tours, vehicle rental, visa assistance and baggage
                        transport arranged around the same journey.</p>
                </article>
                <article class="rounded-[1.75rem] border border-slate-200 bg-white p-7"><span
                        class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-700"><x-public.icon
                            name="sparkle" class="size-6" /></span>
                    <h3 class="mt-6 font-display text-2xl font-semibold text-[#082d4f]">Direct communication</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Send a detailed inquiry or use the configured
                        WhatsApp contact for a convenient conversation.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="border-y border-amber-100 bg-gradient-to-b from-[#fffaf0] to-[#fff6e6] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <x-public.section-heading eyebrow="Simple and personal" title="From an idea to a clear travel plan"
                description="Browse the published options, tell us what you need, and continue the conversation directly with our team." align="center" />
            <ol class="mt-12 grid grid-cols-1 gap-5 md:grid-cols-3">
                <li class="relative rounded-[1.75rem] bg-white p-7 shadow-sm ring-1 ring-amber-100">
                    <span class="font-display text-5xl font-semibold text-cyan-700/25">01</span>
                    <h3 class="mt-5 font-display text-2xl font-semibold text-[#082d4f]">Explore your options</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Compare tours, accommodation and vehicles using the details published on each page.</p>
                </li>
                <li class="relative rounded-[1.75rem] bg-white p-7 shadow-sm ring-1 ring-amber-100">
                    <span class="font-display text-5xl font-semibold text-cyan-700/25">02</span>
                    <h3 class="mt-5 font-display text-2xl font-semibold text-[#082d4f]">Send your requirements</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Share your dates, group size and questions through the relevant inquiry form.</p>
                </li>
                <li class="relative rounded-[1.75rem] bg-white p-7 shadow-sm ring-1 ring-amber-100">
                    <span class="font-display text-5xl font-semibold text-cyan-700/25">03</span>
                    <h3 class="mt-5 font-display text-2xl font-semibold text-[#082d4f]">Confirm the details</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Our team can respond with availability and the practical information needed for your plans.</p>
                </li>
            </ol>
            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('inquiries.contact.create') }}" class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full bg-[#082d4f] px-6 font-bold text-white sm:w-auto">Tell us about your trip <x-public.icon name="arrow" class="size-4" /></a>
                <a href="{{ route('services.visa') }}" class="inline-flex min-h-12 w-full items-center justify-center rounded-full border border-slate-300 bg-white px-6 font-bold text-[#082d4f] sm:w-auto">View travel assistance</a>
            </div>
        </div>
    </section>

    <section class="bg-[#062d50] px-4 py-16 text-white sm:px-6 sm:py-24 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[.85fr_1.15fr] lg:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.24em] text-cyan-300">Move your way</p>
                <h2 class="mt-3 font-display text-4xl font-semibold tracking-tight sm:text-5xl">Vehicle rental for the
                    road ahead</h2>
                <p class="mt-5 max-w-xl leading-7 text-sky-100/75">Browse available cars, tuk tuks and bikes with
                    practical capacity, transmission and pricing information.</p><a href="{{ route('vehicles.index') }}"
                    class="mt-8 inline-flex min-h-12 items-center gap-2 rounded-full bg-white px-6 font-bold text-[#082d4f]">Browse
                    vehicles <x-public.icon name="arrow" class="size-4" /></a>
            </div>@if($featuredVehicles->isNotEmpty())
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach($featuredVehicles->take(2) as $vehicle)<x-public.vehicle-card :vehicle="$vehicle" />@endforeach
            </div>@else<div class="rounded-[2rem] border border-white/15 bg-white/5 p-10 text-center"><x-public.icon
                    name="map" class="mx-auto size-14 text-cyan-300" />
                <p class="mt-4 text-sky-100/70">Published rental vehicles will appear here.</p>
            </div>@endif
        </div>
    </section>

    <section class="bg-gradient-to-br from-[#f8fbfc] via-white to-[#e9f6f2] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-2">
            <article class="relative overflow-hidden rounded-[2rem] bg-[#dff4f2] p-7 sm:p-10">
                <div class="relative z-10 max-w-lg">
                    <p class="text-xs font-bold uppercase tracking-[.22em] text-cyan-800">Travel assistance</p>
                    <h2 class="mt-3 font-display text-3xl font-semibold text-[#082d4f] sm:text-4xl">Visa assistance for
                        Sri Lanka</h2>
                    <p class="mt-4 leading-7 text-slate-600">Coming to Sri Lanka, extending your stay or planning to do
                        business here? We can help with your visa assistance needs.</p><a href="{{ $visaUrl }}"
                        class="mt-7 inline-flex min-h-12 items-center gap-2 rounded-full bg-[#082d4f] px-6 font-bold text-white">Visa
                        assistance <x-public.icon name="arrow" class="size-4" /></a>
                </div>
                <div class="absolute -bottom-20 -right-16 size-64 rounded-full bg-cyan-400/20"></div>
            </article>
            <article class="relative overflow-hidden rounded-[2rem] bg-[#fff2d7] p-7 sm:p-10">
                <div class="relative z-10 max-w-lg">
                    <p class="text-xs font-bold uppercase tracking-[.22em] text-amber-800">Travel lighter</p>
                    <h2 class="mt-3 font-display text-3xl font-semibold text-[#082d4f] sm:text-4xl">Missed your baggage
                        at the airport?</h2>
                    <p class="mt-4 leading-7 text-slate-600">No worries—we will bring it back to you, so you do not
                        have to waste time retrieving it.</p><a href="{{ $baggageUrl }}"
                        class="mt-7 inline-flex min-h-12 items-center gap-2 rounded-full bg-[#082d4f] px-6 font-bold text-white">Baggage
                        recovery service <x-public.icon name="arrow" class="size-4" /></a>
                </div>
                <div class="absolute -bottom-20 -right-16 size-64 rounded-full bg-amber-400/20"></div>
            </article>
        </div>
    </section>

    @if($galleryImages->isNotEmpty())
        <section class="bg-[#fffaf1] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between"><x-public.section-heading
                        eyebrow="Island moments" title="A glimpse of Sri Lanka"
                        description="Scenes from our published travel gallery." /><a href="{{ route('gallery.index') }}"
                        class="inline-flex min-h-11 items-center gap-2 self-start font-bold text-cyan-700">View gallery
                        <x-public.icon name="arrow" class="size-4" /></a></div>
                <div class="mt-10 grid grid-cols-2 gap-3 md:grid-cols-4 md:grid-rows-2">@foreach($galleryImages as $image)
                    <figure
                        class="group relative overflow-hidden rounded-2xl {{ $loop->first ? 'col-span-2 row-span-2' : '' }}">
                        <img src="{{ $image->url($loop->first ? 'large' : 'medium') }}"
                            srcset="{{ $image->url('small') }} 480w, {{ $image->url('medium') }} 960w, {{ $image->url() }} 1800w"
                            sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                            alt="{{ $image->alt_text ?: $image->title }}" width="800" height="600" loading="lazy"
                            class="aspect-square size-full object-cover transition duration-500 group-hover:scale-[1.03]">@if($image->caption)
                                <figcaption
                                    class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent px-4 pb-4 pt-10 text-sm text-white">
                            {{ $image->caption }}</figcaption>@endif
                </figure>@endforeach
                </div>
            </div>
    </section>@endif

    @if($latestPosts->isNotEmpty())
        <section class="bg-[#eef6fa] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between"><x-public.section-heading
                        eyebrow="Travel journal" title="Latest from the blog"
                        description="Ideas and practical reading for planning time in Sri Lanka." /><a
                        href="{{ route('blog.index') }}"
                        class="inline-flex min-h-11 items-center gap-2 self-start font-bold text-cyan-700">Read all articles
                        <x-public.icon name="arrow" class="size-4" /></a></div>
                <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">@foreach($latestPosts as $post)<x-public.blog-card
                :post="$post" />@endforeach</div>
            </div>
    </section>@endif

    <section class="bg-[#eef6fa] px-4 pb-16 sm:px-6 sm:pb-24 lg:px-8">
        <div
            class="mx-auto overflow-hidden rounded-[2rem] bg-[linear-gradient(120deg,#0b6b7e,#082d4f)] px-6 py-12 text-center text-white shadow-2xl shadow-cyan-950/20 sm:px-12 sm:py-16">
            <p class="text-xs font-bold uppercase tracking-[.24em] text-cyan-200">Start planning</p>
            <h2 class="mx-auto mt-3 max-w-3xl font-display text-3xl font-semibold sm:text-5xl">Ready to shape your Sri
                Lanka journey?</h2>
            <p class="mx-auto mt-5 max-w-2xl leading-7 text-sky-100/80">Tell us where you want to go and which services
                you need.</p><a href="{{ route('inquiries.contact.create') }}"
                class="mt-8 inline-flex min-h-13 items-center gap-2 rounded-full bg-white px-7 font-bold text-[#082d4f]">Send
                an inquiry <x-public.icon name="arrow" class="size-5" /></a>
        </div>
    </section>
</x-public.layout>
