@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-base-content/70']) }}>
    {{ $value ?? $slot }}
</label>
