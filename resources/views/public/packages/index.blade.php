<x-public.layout title="Sri Lanka Tour Packages"
    meta-description="Explore published Sri Lanka tour packages by destination, duration, price and availability."
    :canonical="route('packages.index')" :schema="$schema">
    <section class="relative overflow-hidden bg-[#062d50] px-4 py-16 text-white sm:px-6 sm:py-24">
        <div class="absolute -right-20 -top-20 size-80 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl">
            <nav aria-label="Breadcrumb" class="text-sm text-cyan-200"><a href="{{ route('home') }}">Home</a> / <span
                    aria-current="page">Tour packages</span></nav>
            <p class="mt-8 text-xs font-bold uppercase tracking-[.24em] text-cyan-300">Curated journeys</p>
            <h1 class="mt-3 max-w-4xl font-display text-4xl font-semibold tracking-tight sm:text-6xl">Sri Lanka tour
                packages</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-sky-100/75">Find a published itinerary that matches your
                destination, schedule and travel plans.</p>
        </div>
    </section>
    <section class="px-4 py-10 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <form method="GET" action="{{ route('packages.index') }}"
                class="rounded-[1.5rem] bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6"><label class="sm:col-span-2"><span
                            class="mb-1.5 block text-sm font-semibold text-[#082d4f]">Search</span><input name="search"
                            value="{{ $filters['search'] ?? '' }}" placeholder="Package or destination"
                            class="min-h-12 w-full rounded-xl border border-slate-300 px-4"></label><label><span
                            class="mb-1.5 block text-sm font-semibold text-[#082d4f]">Destination</span><select
                            name="destination" class="min-h-12 w-full rounded-xl border border-slate-300 px-3">
                            <option value="">All destinations</option>@foreach($destinations as $destination)
                                <option value="{{ $destination }}" @selected(($filters['destination'] ?? '') === $destination)>
                            {{ $destination }}</option>@endforeach
                        </select></label><label><span
                            class="mb-1.5 block text-sm font-semibold text-[#082d4f]">Duration</span><select
                            name="duration" class="min-h-12 w-full rounded-xl border border-slate-300 px-3">
                            <option value="">Any length</option>
                            <option value="1-3" @selected(($filters['duration'] ?? '') === '1-3')>1–3 days</option>
                            <option value="4-7" @selected(($filters['duration'] ?? '') === '4-7')>4–7 days</option>
                            <option value="8+" @selected(($filters['duration'] ?? '') === '8+')>8+ days</option>
                        </select></label><label><span
                            class="mb-1.5 block text-sm font-semibold text-[#082d4f]">Availability</span><select
                            name="availability" class="min-h-12 w-full rounded-xl border border-slate-300 px-3">
                            <option value="">Any status</option>
                            @foreach(App\Enums\PackageAvailability::cases() as $status)
                                <option value="{{ $status->value }}"
                                    @selected(($filters['availability'] ?? '') === $status->value)>{{ $status->label() }}
                            </option>@endforeach
                        </select></label><label><span
                            class="mb-1.5 block text-sm font-semibold text-[#082d4f]">Sort</span><select name="sort"
                            class="min-h-12 w-full rounded-xl border border-slate-300 px-3">
                            <option value="recommended">Recommended</option>
                            <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Price: low first
                            </option>
                            <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Price: high
                                first</option>
                            <option value="duration_short" @selected(($filters['sort'] ?? '') === 'duration_short')>Shortest
                                first</option>
                            <option value="duration_long" @selected(($filters['sort'] ?? '') === 'duration_long')>Longest
                                first</option>
                        </select></label></div>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    @if(collect($filters)->filter()->isNotEmpty())<a href="{{ route('packages.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl px-5 font-semibold text-slate-600">Clear
                    filters</a>@endif<button class="min-h-12 rounded-xl bg-[#082d4f] px-6 font-bold text-white">Show
                        packages</button></div>
            </form>
            <div class="mt-8 flex items-center justify-between gap-4">
                <p class="text-sm text-slate-600"><strong class="text-[#082d4f]">{{ $packages->total() }}</strong>
                    {{ Str::plural('package', $packages->total()) }}</p>
            </div>
            @if($packages->isEmpty())
                <div class="mt-6 rounded-[2rem] border border-dashed border-slate-300 bg-white py-20 text-center">
                    <h2 class="font-display text-2xl font-semibold text-[#082d4f]">No packages match these filters</h2>
                    <p class="mt-2 text-slate-600">Clear the filters or contact us to discuss a custom trip.</p><a
                        href="{{ route('packages.index') }}"
                        class="mt-6 inline-flex min-h-12 items-center rounded-full bg-[#082d4f] px-6 font-bold text-white">View
                        all packages</a>
            </div>@else<div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $package)<x-public.package-card :package="$package" />@endforeach</div>
            <div class="mt-10">{{ $packages->links() }}</div>@endif
        </div>
    </section>
</x-public.layout>