@props(['type' => 'button', 'color' => 'primary', 'size' => '', 'outline' => false, 'icon' => ''])

@php
    $btnClass = $outline ? "btn-outline-{$color}" : "btn-{$color}";
    $sizeClass = $size ? "btn-{$size}" : "";
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "btn {$btnClass} {$sizeClass}"]) }}>
    @if($icon)
        <i class="{{ $icon }} me-1"></i>
    @endif
    {{ $slot }}
</button>
