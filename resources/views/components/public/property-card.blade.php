@props(['property', 'headingLevel' => 'h2'])
@php($headingLevel = in_array($headingLevel, ['h2', 'h3'], true) ? $headingLevel : 'h2')
<article {{ $attributes->class('group flex h-full min-w-0 flex-col overflow-hidden rounded-[1.5rem] bg-white shadow-[0_18px_60px_-34px_rgba(8,45,79,.45)] ring-1 ring-slate-200/80') }}>
    <a href="{{ route('properties.show', $property) }}" class="relative block overflow-hidden bg-slate-100">
        @if($property->featuredImage)
            <img src="{{ $property->featuredImage->url('medium') }}"
                srcset="{{ $property->featuredImage->url('small') }} 480w, {{ $property->featuredImage->url('medium') }} 960w"
                sizes="(min-width:1024px) 33vw, (min-width:768px) 50vw, 100vw"
                alt="{{ $property->featuredImage->alt_text ?: $property->name }}"
                width="{{ $property->featuredImage->width }}" height="{{ $property->featuredImage->height }}"
                loading="lazy" decoding="async"
                class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        @else
            <div class="flex aspect-[4/3] items-center justify-center bg-gradient-to-br from-[#dff5f5] to-[#c8e5ef] text-[#0b5570]"><x-public.icon name="home" class="size-12" /></div>
        @endif
        @if($property->location)<span class="absolute bottom-3 left-3 max-w-[calc(100%-1.5rem)] truncate rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-[#083354] shadow-sm">{{ $property->location }}</span>@endif
        @if($property->is_negotiable)<span class="absolute right-3 top-3 rounded-full bg-amber-400 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-amber-950 shadow-sm">Negotiable</span>@endif
    </a>
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <p class="text-xs font-bold uppercase tracking-[.16em] text-cyan-700">{{ $property->type->name }}</p>
        <{{ $headingLevel }} class="mt-3 break-words font-display text-2xl font-semibold leading-tight text-[#082d4f]"><a href="{{ route('properties.show', $property) }}">{{ $property->name }}</a></{{ $headingLevel }}>
        @if($property->short_description)<p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $property->short_description }}</p>@endif
        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-600">
            @if($property->bedrooms !== null)<span>{{ $property->bedrooms }} {{ Str::plural('bedroom', $property->bedrooms) }}</span>@endif
            @if($property->max_guests)<span>Up to {{ $property->max_guests }} guests</span>@endif
        </div>
        <div class="mt-auto flex items-end justify-between gap-4 pt-5">
            @if($property->price !== null)<p class="font-bold text-[#082d4f]">{{ $property->currency }} {{ number_format((float) $property->price, 0) }}<span class="block text-xs font-normal text-slate-500">{{ str_replace('_', ' ', $property->pricing_unit) }}</span></p>@endif
            <span class="inline-flex min-h-11 items-center gap-2 text-sm font-bold text-cyan-700">View stay <x-public.icon name="arrow" class="size-4" /></span>
        </div>
    </div>
</article>
