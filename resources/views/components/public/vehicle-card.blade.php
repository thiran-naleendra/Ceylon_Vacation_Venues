@props(['vehicle'])
<article {{ $attributes->class('group flex h-full min-w-0 flex-col overflow-hidden rounded-[1.5rem] bg-white shadow-[0_18px_60px_-34px_rgba(8,45,79,.45)] ring-1 ring-slate-200/80') }}>
    <a href="{{ route('vehicles.show', $vehicle) }}"
        class="block overflow-hidden bg-slate-100">@if($vehicle->featuredImage)<img
            src="{{ $vehicle->featuredImage->url('medium') }}"
            srcset="{{ $vehicle->featuredImage->url('small') }} 480w, {{ $vehicle->featuredImage->url('medium') }} 960w, {{ $vehicle->featuredImage->url() }} 1600w"
            sizes="(min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw"
            alt="{{ $vehicle->featuredImage->alt_text ?: $vehicle->title }}" width="640" height="480" loading="lazy"
        class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-[1.03]">@else<div
                class="flex aspect-[4/3] items-center justify-center bg-gradient-to-br from-[#e3f5f3] to-[#c9e7ef] text-[#0b5570]">
            <x-public.icon name="map" class="size-12" /></div>@endif</a>
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div><span
                    class="text-xs font-bold uppercase tracking-[.16em] text-cyan-700">{{ $vehicle->category->name }}</span><span
                    class="ml-2 text-xs font-semibold text-emerald-700">{{ $vehicle->availability_status->label() }}</span>
            </div>@if($vehicle->rental_rate)<span class="text-sm font-bold text-[#082d4f]">{{ $vehicle->currency }}
            {{ number_format((float) $vehicle->rental_rate, 0) }}/{{ $vehicle->rate_unit }}</span>@endif
        </div>
        <h3 class="mt-3 font-display text-2xl font-semibold text-[#082d4f]"><a
                href="{{ route('vehicles.show', $vehicle) }}">{{ $vehicle->title }}</a></h3>@if($vehicle->summary)
                <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $vehicle->summary }}</p>@endif<div
            class="mt-auto flex flex-wrap gap-3 pt-5 text-xs font-semibold text-slate-500">
            @if($vehicle->seats)<span>{{ $vehicle->seats }} seats</span>@endif
            @if($vehicle->transmission)<span>{{ ucfirst($vehicle->transmission) }}</span>@endif
            @if($vehicle->has_air_conditioning)<span>Air conditioned</span>@endif</div>
    </div>
</article>