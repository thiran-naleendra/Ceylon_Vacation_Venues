@props(['name', 'label', 'required' => false, 'hint' => null])

<div {{ $attributes->class('min-w-0') }}>
    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-slate-700">
            {{ $label }}
            @if ($required)<span class="text-rose-600" aria-hidden="true">*</span>@endif
        </span>
        {{ $slot }}
    </label>
    @if ($hint)
    <p class="mt-1.5 text-xs leading-5 text-slate-500">{{ $hint }}</p>@endif
    @error($name)
    <p class="mt-1.5 text-sm text-rose-600" role="alert">{{ $message }}</p>@enderror
</div>