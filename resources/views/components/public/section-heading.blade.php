@props(['eyebrow' => null, 'title', 'description' => null, 'align' => 'left'])
<div {{ $attributes->class([$align === 'center' ? 'mx-auto max-w-3xl text-center' : 'max-w-2xl']) }}>
    @if($eyebrow)
    <p class="text-xs font-bold uppercase tracking-[.24em] text-cyan-700">{{ $eyebrow }}</p>@endif
    <h2 class="mt-3 font-display text-3xl font-semibold tracking-tight text-[#082d4f] sm:text-4xl lg:text-5xl">
        {{ $title }}
    </h2>
    @if($description)
    <p class="mt-4 text-base leading-7 text-slate-600 sm:text-lg">{{ $description }}</p>@endif
</div>