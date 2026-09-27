<x-public.layout :seo="$seo">
    @php($heroImage = data_get($hero, 'image'))
    <header class="relative isolate overflow-hidden bg-[#062d50] px-4 py-16 text-white sm:px-6 sm:py-24">
        @if($heroImage)
            <x-public.hero-image :image="$heroImage" :alt="data_get($hero, 'image.alt', $page->title)"
                class="absolute inset-0 -z-20 size-full object-cover" />
            <div class="absolute inset-0 -z-10 bg-[#041f37]/78"></div>
        @else
            <div class="absolute -right-20 -top-20 -z-10 size-80 rounded-full bg-cyan-400/10 blur-3xl"></div>
        @endif
        <div class="mx-auto max-w-7xl">
            <nav aria-label="Breadcrumb" class="text-sm text-cyan-200">
                <a href="{{ route('home') }}">Home</a> / <span aria-current="page">{{ $page->title }}</span>
            </nav>
            <p class="mt-8 text-xs font-bold uppercase tracking-[.24em] text-cyan-300">Stay in Sri Lanka</p>
            <h1 class="mt-3 max-w-4xl font-display text-4xl font-semibold sm:text-6xl">
                {{ data_get($hero, 'title') ?: $page->title }}
            </h1>
            @if(data_get($hero, 'text') || $page->summary)
                <p class="mt-5 max-w-2xl text-lg leading-8 text-sky-100/75">
                    {{ data_get($hero, 'text') ?: $page->summary }}
                </p>
            @endif
        </div>
    </header>

    @if($page->body)
        <section class="border-b border-slate-200 bg-white px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <div class="rich-content mx-auto max-w-4xl">{!! $page->body !!}</div>
        </section>
    @endif

    <section class="px-4 py-10 sm:px-6 sm:py-16">
        <div class="mx-auto max-w-7xl">
            <form method="GET"
                class="grid grid-cols-1 gap-4 rounded-3xl bg-white p-4 ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-5">
                <label><span class="mb-1 block text-sm font-semibold">Search</span><input name="search"
                        value="{{ $filters['search'] ?? '' }}" class="min-h-12 w-full rounded-xl border px-3"></label>
                <label><span class="mb-1 block text-sm font-semibold">Type</span><select name="type"
                        class="min-h-12 w-full rounded-xl border px-3">
                        <option value="">All types</option>
                        @foreach($types as $type)
                            <option value="{{ $type->slug }}" @selected(($filters['type'] ?? '') === $type->slug)>{{ $type->name }}</option>
                        @endforeach
                    </select></label>
                <label><span class="mb-1 block text-sm font-semibold">Location</span><select name="location"
                        class="min-h-12 w-full rounded-xl border px-3">
                        <option value="">All locations</option>
                        @foreach($locations as $location)
                            <option @selected(($filters['location'] ?? '') === $location)>{{ $location }}</option>
                        @endforeach
                    </select></label>
                <label><span class="mb-1 block text-sm font-semibold">Guests</span><input name="guests" type="number"
                        min="1" max="500" value="{{ $filters['guests'] ?? '' }}"
                        class="min-h-12 w-full rounded-xl border px-3"></label>
                <button class="min-h-12 self-end rounded-xl bg-[#082f57] px-5 font-bold text-white">Find
                    properties</button>
            </form>

            @if($properties->isEmpty())
                <div class="mt-8 rounded-3xl border border-dashed p-10 text-center sm:p-16">
                    <h2 class="text-2xl font-semibold">No properties match these filters</h2>
                </div>
            @else
                <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($properties as $property)
                        <x-public.property-card :property="$property" />
                    @endforeach
                </div>
                <div class="mt-10">{{ $properties->links() }}</div>
            @endif
        </div>
    </section>
</x-public.layout>
