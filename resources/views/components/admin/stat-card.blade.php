@props(['label', 'value', 'icon', 'tone' => 'blue'])

@php
    $toneClasses = match ($tone) {
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'cyan' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        default => 'bg-sky-50 text-sky-700 ring-sky-100',
    };
@endphp

<article {{ $attributes->class('min-w-0 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 sm:p-6') }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium leading-5 text-slate-500">{{ $label }}</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums tracking-tight text-slate-950">{{ number_format($value) }}</p>
        </div>
        <div class="grid size-11 shrink-0 place-items-center rounded-xl ring-1 {{ $toneClasses }}" aria-hidden="true">
            @switch($icon)
                @case('package')
                    <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="m4 7 8-4 8 4-8 4-8-4Zm0 5 8 4 8-4M4 17l8 4 8-4"/></svg>
                    @break
                @case('vehicle')
                    <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M5 17h14l1-6-3-5H7l-3 5 1 6Zm2 0v2m10-2v2M6 11h12"/></svg>
                    @break
                @case('visa')
                    <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M6 3h12v18H6V3Zm3 5h6m-6 4h6m-6 4h4"/></svg>
                    @break
                @case('baggage')
                    <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M7 7h10v14H7V7Zm3 0V4h4v3M10 11v6m4-6v6"/></svg>
                    @break
                @default
                    <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M4 5h16v12H8l-4 4V5Zm4 4h8m-8 4h5"/></svg>
            @endswitch
        </div>
    </div>
</article>
