@props([
    'logoUrl' => null,
    'size' => 40,
])

<img
    src="{{ $logoUrl ?: asset('images/product-placeholder.svg') }}"
    alt="{{ $logoUrl ? 'Logo' : 'Sin logo' }}"
    width="{{ (int) $size }}"
    height="{{ (int) $size }}"
    style="width: {{ (int) $size }}px; height: {{ (int) $size }}px; object-fit: contain; display: block;"
    {{ $attributes->merge(['class' => 'object-contain']) }}
>
