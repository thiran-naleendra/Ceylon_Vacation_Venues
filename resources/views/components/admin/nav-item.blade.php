@props(['href', 'icon', 'active' => false, 'mobile' => false])

<a href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium outline-none transition focus:ring-2 focus:ring-[#f4c667]',
        'bg-[#0c477a] text-white shadow-sm' => $active && ! $mobile,
        'text-sky-50/80 hover:bg-white/10 hover:text-white' => ! $active && ! $mobile,
        'bg-sky-50 text-[#082f57]' => $active && $mobile,
        'text-slate-700 hover:bg-slate-100' => ! $active && $mobile,
    ]) }}>
    <span class="grid size-6 shrink-0 place-items-center" aria-hidden="true">
        @switch($icon)
            @case('dashboard')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z"/></svg>
                @break
            @case('packages')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="m4 7 8-4 8 4-8 4-8-4Zm0 5 8 4 8-4M4 17l8 4 8-4"/></svg>
                @break
            @case('vehicles')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M5 17h14l1-6-3-5H7l-3 5 1 6Zm2 0v2m10-2v2M6 11h12M8 14h.01M16 14h.01"/></svg>
                @break
            @case('properties')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M3 11 12 4l9 7M5 10v10h14V10M9 20v-6h6v6"/></svg>
                @break
            @case('inquiries')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M4 5h16v12H8l-4 4V5Zm4 4h8m-8 4h5"/></svg>
                @break
            @case('visa')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M6 3h12v18H6V3Zm3 5h6m-6 4h6m-6 4h4"/></svg>
                @break
            @case('baggage')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M7 7h10v14H7V7Zm3 0V4h4v3M10 11v6m4-6v6"/></svg>
                @break
            @case('gallery')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M4 4h16v16H4V4Zm3 12 4-4 3 3 2-2 4 4M8 8h.01"/></svg>
                @break
            @case('blog')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M5 4h14v16H5V4Zm3 4h8m-8 4h8m-8 4h5"/></svg>
                @break
            @case('pages')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M7 3h8l4 4v14H7V3Zm8 0v5h4M10 12h6m-6 4h6"/></svg>
                @break
            @case('seo')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><circle cx="10" cy="10" r="6"/><path d="m15 15 5 5M7 10h6m-3-3v6"/></svg>
                @break
            @case('redirects')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M5 7h11m0 0-3-3m3 3-3 3M19 17H8m0 0 3-3m-3 3 3 3"/></svg>
                @break
            @case('audit')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M7 3h10v3h3v15H4V6h3V3Zm0 7h10M8 14h8m-8 3h5M9 3v3h6V3"/></svg>
                @break
            @case('settings')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1l2-1.5-2-3.4-2.4 1A7 7 0 0 0 15 6l-.3-2.6h-4L10.4 6a7 7 0 0 0-1.6.9l-2.4-1-2 3.4 2 1.6a7 7 0 0 0 0 2.2l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 1.6.9l.3 2.6h4L15 18a7 7 0 0 0 1.6-.9l2.4 1 2-3.4-2-1.6c.1-.3.1-.7.1-1.1Z"/></svg>
                @break
            @case('users')
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m6-10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7 1a2.5 2.5 0 1 0 0-5m1 8a4 4 0 0 1 3 3.9V20"/></svg>
                @break
            @default
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8"><path d="M4 4h16v16H4V4Zm4 4h8m-8 4h8m-8 4h5"/></svg>
        @endswitch
    </span>
    <span class="truncate">{{ $slot }}</span>
</a>
