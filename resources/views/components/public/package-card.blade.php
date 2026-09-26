@props(['package'])
<article {{ $attributes->class('group flex h-full min-w-0 flex-col overflow-hidden rounded-[1.5rem] bg-white shadow-[0_18px_60px_-34px_rgba(8,45,79,.45)] ring-1 ring-slate-200/80') }}>
    <a href="{{ route('packages.show', $package) }}" class="relative block overflow-hidden bg-slate-100">
        @if($package->featuredImage)<img src="{{ $package->featuredImage->url('medium') }}"
            srcset="{{ $package->featuredImage->url('small') }} 480w, {{ $package->featuredImage->url('medium') }} 960w, {{ $package->featuredImage->url() }} 1600w"
            sizes="(min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw"
            alt="{{ $package->featuredImage->alt_text ?: $package->title }}" width="640" height="480" loading="lazy"
        class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-[1.03]">@else<div
                class="flex aspect-[4/3] items-center justify-center bg-gradient-to-br from-[#dff5f5] to-[#c8e5ef] text-[#0b5570]">
            <x-public.icon name="map" class="size-12" /></div>@endif
        @if($package->destination)<span
        class="absolute bottom-3 left-3 max-w-[calc(100%-1.5rem)] truncate rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-[#083354] shadow-sm">{{ $package->destination }}</span>@endif
    </a>
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold text-slate-500">
            <span>{{ $package->duration_days }} {{ Str::plural('day', $package->duration_days) }}</span><span
                class="rounded-full bg-emerald-50 px-2 py-1 text-emerald-700">{{ $package->availability_status->label() }}</span>@if($package->starting_price)<span
                    class="text-cyan-700">From {{ $package->currency }}
                {{ number_format((float) $package->starting_price, 0) }}</span>@endif</div>
        <h3 class="mt-3 font-display text-2xl font-semibold leading-tight text-[#082d4f]"><a
                href="{{ route('packages.show', $package) }}"
                class="focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600">{{ $package->title }}</a>
        </h3>@if($package->summary)
        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $package->summary }}</p>@endif<a
            href="{{ route('packages.show', $package) }}"
            class="mt-auto inline-flex min-h-11 items-center gap-2 pt-5 text-sm font-bold text-cyan-700">Explore package
            <x-public.icon name="arrow" class="size-4" /></a>
    </div>
</article>