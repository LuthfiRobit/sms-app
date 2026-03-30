@props(['color' => 'primary', 'light' => false, 'pill' => false])

@php
    $bgClass = $light ? "bg-light-{$color} text-{$color}" : "bg-{$color}";
    $pillClass = $pill ? "rounded-pill" : "";
@endphp

<span {{ $attributes->merge(['class' => "badge {$bgClass} {$pillClass}"]) }}>
    {{ $slot }}
</span>
