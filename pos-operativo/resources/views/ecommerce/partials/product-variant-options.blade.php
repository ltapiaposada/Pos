@if ($hasVariantOptions)
    <div>
        <div class="shop-configurator__title">Presentacion</div>
        <div class="variant-choice-grid">
            @foreach ($variantOptions as $variant)
                @php
                    $variantLabel = $variant->name;
                    if (preg_match('/talla\s+([a-z0-9]+)/i', $variant->name, $matches)) {
                        $variantLabel = strtoupper($matches[1]);
                    }
                @endphp
                <input
                    class="variant-choice-input"
                    type="radio"
                    id="variant-{{ $product->id }}-{{ $variant->id }}"
                    name="product_id"
                    value="{{ $variant->id }}"
                    @checked((int) $variant->id === (int) $defaultProductId)
                >
                <label class="variant-choice-label" for="variant-{{ $product->id }}-{{ $variant->id }}">
                    {{ $variantLabel }}
                </label>
            @endforeach
        </div>
    </div>
@else
    <input type="hidden" name="product_id" value="{{ $defaultProductId }}">
@endif
