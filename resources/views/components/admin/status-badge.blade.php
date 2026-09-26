@props(['status'])

@php
    $classes = match ($status->value) {
        'new' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'contacted' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'in_progress' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'awaiting_customer' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'closed' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'spam' => 'bg-rose-50 text-rose-700 ring-rose-200',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$classes}") }}>
    {{ $status->label() }}
</span>