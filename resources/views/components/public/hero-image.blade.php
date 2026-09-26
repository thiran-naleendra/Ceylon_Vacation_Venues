@props(['image', 'alt', 'sizes' => '100vw'])
@php
    $disk = data_get($image, 'disk', 'public');
    $path = data_get($image, 'path');
    $source = $path ? Storage::disk($disk)->url($path) : null;
    $smallPath = data_get($image, 'variants.small.path');
    $mediumPath = data_get($image, 'variants.medium.path');
    $sources = array_filter([
        $smallPath ? Storage::disk($disk)->url($smallPath).' '.data_get($image, 'variants.small.width', 640).'w' : null,
        $mediumPath ? Storage::disk($disk)->url($mediumPath).' '.data_get($image, 'variants.medium.width', 1280).'w' : null,
        $source ? $source.' '.data_get($image, 'width', 1920).'w' : null,
    ]);
@endphp
@if($source)
    <img src="{{ $mediumPath ? Storage::disk($disk)->url($mediumPath) : $source }}"
        @if(count($sources) > 1) srcset="{{ implode(', ', $sources) }}" sizes="{{ $sizes }}" @endif
        alt="{{ $alt }}" width="{{ data_get($image, 'width', 1920) }}"
        height="{{ data_get($image, 'height', 900) }}" fetchpriority="high" decoding="async"
        {{ $attributes }}>
@endif
