@extends('ecommerce.layouts.app')

@section('content')
    @php
        $pageProducts = $products->getCollection();
        $visibleCategories = $pageProducts->pluck('category')->filter()->unique('id')->values();
        $palette = [
            ['tint' => 'var(--shop-gold-tint)', 'tone' => 'var(--shop-gold)', 'accent' => 'var(--shop-forest)'],
            ['tint' => 'var(--shop-clay-tint)', 'tone' => 'var(--shop-clay)', 'accent' => 'var(--shop-slate)'],
            ['tint' => 'var(--shop-forest-tint)', 'tone' => 'var(--shop-forest)', 'accent' => 'var(--shop-gold)'],
            ['tint' => 'var(--shop-slate-tint)', 'tone' => 'var(--shop-slate)', 'accent' => 'var(--shop-clay)'],
        ];
    @endphp

    <style>
        .shop-hero { position: relative; overflow: hidden; background: var(--shop-forest); color: #fff; }
        .shop-hero__mark { position: absolute; top: -70px; right: -70px; width: 360px; height: 360px; opacity: .14; pointer-events: none; }
        .shop-hero__inner { position: relative; z-index: 1; max-width: 1280px; margin: 0 auto; padding: 80px 48px 56px; display: grid; grid-template-columns: 1.15fr .7fr; gap: 60px; align-items: end; }
        .shop-hero__eyebrow { margin-bottom: 18px; color: #B9D0C4; font-size: 12.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
        .shop-hero h1 { max-width: 12ch; margin: 0; color: #fff; font-size: 48px; font-weight: 560; letter-spacing: 0; line-height: 1.1; }
        .shop-hero p { max-width: 42ch; margin: 18px 0 0; color: #D3E0DA; font-size: 16.5px; line-height: 1.55; }
        .shop-search { max-width: 480px; margin-top: 30px; padding: 5px; border-radius: var(--shop-radius-s); background: #fff; display: flex; gap: 8px; }
        .shop-search input { min-width: 0; flex: 1; border: 0; outline: 0; background: transparent; padding: 11px 14px; color: var(--shop-ink); font: inherit; font-size: 14.5px; }
        .shop-search input::placeholder { color: #9A968A; }
        .shop-search button, .shop-product__add { border: 0; border-radius: 6px; background: var(--shop-ink); color: #fff; font-weight: 700; }
        .shop-search button { padding: 0 22px; font-size: 14px; }
        .shop-search button:hover, .shop-product__add:hover { background: var(--shop-forest-dark); }
        .shop-hero-panel { border-radius: var(--shop-radius-m); background: #EFE7D8; color: var(--shop-ink); padding: 24px; }
        .shop-hero-panel__kicker { color: var(--shop-ink-soft); font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
        .shop-hero-panel__figure { margin-top: 6px; font-family: "Fraunces", Georgia, serif; font-size: 42px; font-weight: 560; line-height: 1; }
        .shop-hero-panel__label { margin-top: 5px; color: var(--shop-ink-soft); font-size: 13px; }
        .shop-hero-panel hr { margin: 16px 0; border: 0; border-top: 1px solid #DCD2B8; }
        .shop-hero-panel ul { margin: 0; padding: 0; list-style: none; }
        .shop-hero-panel li { padding: 5px 0; color: var(--shop-ink-soft); display: flex; align-items: center; gap: 9px; font-size: 13px; }
        .shop-hero-panel svg { color: var(--shop-forest); flex: 0 0 auto; }
        .shop-discover, .shop-categories, .shop-catalog, .shop-story { max-width: 1280px; margin: 0 auto; padding-inline: 48px; }
        .shop-discover { padding-top: 44px; padding-bottom: 8px; }
        .shop-discover h2 { margin: 0 0 20px; font-size: 20px; font-weight: 650; }
        .shop-discover__row { display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px; }
        .shop-discover__tile { border: 1px solid var(--shop-line); border-radius: var(--shop-radius-s); background: #fff; padding: 20px 10px; color: var(--shop-ink); display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center; }
        .shop-discover__tile:hover, .shop-discover__tile:focus { border-color: var(--shop-forest); color: var(--shop-ink); }
        .shop-discover__dot { width: 44px; height: 44px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
        .shop-discover__tile span { font-size: 12.5px; font-weight: 700; }
        .shop-categories { margin-top: 14px; padding-top: 26px; padding-bottom: 22px; border-bottom: 1px solid var(--shop-line); display: flex; gap: 26px; overflow-x: auto; }
        .shop-chip { border: 0; border-bottom: 2px solid transparent; background: transparent; color: var(--shop-ink-soft); padding: 0 0 4px; white-space: nowrap; font: inherit; font-size: 14px; font-weight: 500; }
        .shop-chip:hover, .shop-chip.is-active { color: var(--shop-ink); }
        .shop-chip.is-active { border-color: var(--shop-forest); font-weight: 700; }
        .shop-catalog { padding-top: 36px; padding-bottom: 20px; }
        .shop-catalog__head { margin-bottom: 26px; display: flex; align-items: baseline; justify-content: space-between; gap: 18px; }
        .shop-catalog__head h2 { margin: 0; font-size: 24px; font-weight: 560; }
        .shop-catalog__head span { color: var(--shop-ink-soft); font-size: 13.5px; }
        .shop-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px 24px; }
        .shop-product[hidden] { display: none; }
        .shop-product { min-width: 0; height: 100%; display: flex; flex-direction: column; }
        .shop-product__media { position: relative; aspect-ratio: 4 / 5; border-radius: var(--shop-radius-s); overflow: hidden; margin-bottom: 14px; display: flex; align-items: center; justify-content: center; }
        .product-image-trigger { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; background: transparent; padding: 0; cursor: zoom-in; }
        .shop-product__image-label { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
        .shop-product__image { width: 100%; height: 100%; object-fit: cover; display: block; }
        .shop-product__initial { font-family: "Fraunces", Georgia, serif; font-size: 96px; font-weight: 560; line-height: 1; text-transform: uppercase; }
        .shop-stock-tag, .shop-product-badge { border: 1px solid var(--shop-line); border-radius: 999px; background: #fff; color: var(--shop-ink); font-size: 11px; font-weight: 700; }
        .shop-stock-tag { position: absolute; top: 10px; left: 10px; padding: 4px 10px; }
        .shop-product-badge { display: inline-flex; width: fit-content; margin-top: 6px; padding: 3px 9px; }
        .shop-product__qty { width: 52px; min-height: 38px; border: 1px solid var(--shop-line); border-radius: 6px; background: #fff; text-align: center; font: inherit; font-size: 13px; padding: 7px 2px; }
        .shop-product__add { min-height: 38px; padding: 0 14px; font-size: 12.5px; }
        .shop-product h3 { margin: 0; color: var(--shop-ink); font-family: "Inter", system-ui, sans-serif; font-size: 14.5px; font-weight: 700; line-height: 1.35; }
        .shop-product__category, .shop-product__meta, .shop-configurator__hint { color: var(--shop-ink-soft); font-size: 12.5px; line-height: 1.45; }
        .shop-product__category { margin-top: 5px; }
        .shop-product__badges:empty { display: none; }
        .shop-price { display: inline-block; margin-top: 8px; border-radius: 6px; background: var(--shop-forest-tint); color: var(--shop-forest-dark); font-family: "Fraunces", Georgia, serif; font-size: 17px; font-weight: 560; line-height: 1.2; padding: 3px 10px; }
        .variant-choice-grid { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .variant-choice-input { position: absolute; opacity: 0; pointer-events: none; }
        .variant-choice-label { border: 1px solid var(--shop-line); border-radius: 999px; background: var(--shop-paper-soft); color: var(--shop-ink); cursor: pointer; font-size: 12px; font-weight: 700; line-height: 1.2; min-width: 32px; padding: 6px 9px; text-align: center; }
        .variant-choice-input:checked + .variant-choice-label { border-color: var(--shop-forest); background: var(--shop-forest); color: #fff; }
        .shop-product__form { margin-top: auto; padding-top: 14px; }
        .shop-product__buy-row { display: flex; align-items: center; gap: 8px; margin-top: 0; }
        .shop-product__buy-row .shop-product__add { flex: 1; }
        .kit-components-list { margin: 8px 0 0; padding-left: 18px; color: var(--shop-ink-soft); font-size: 12.5px; }
        .shop-configurator { margin-top: auto; padding-top: 14px; }
        .shop-configurator__trigger { width: 100%; border: 0; border-radius: 6px; background: var(--shop-ink); color: #fff; min-height: 38px; padding: 8px 16px; cursor: pointer; font: inherit; font-size: 12.5px; font-weight: 700; }
        .shop-configurator__trigger:hover, .shop-configurator__trigger:focus { background: var(--shop-forest-dark); }
        .shop-configurator__body { padding: 20px; }
        .shop-configurator__title { color: var(--shop-ink); font-size: 13.5px; font-weight: 800; }
        .shop-configurator__label { color: var(--shop-ink-soft); font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
        .shop-configurator__group { border: 1px solid var(--shop-line); border-radius: var(--shop-radius-s); background: #fff; padding: 12px; }
        .shop-configurator__options { display: grid; gap: 8px; margin-top: 10px; }
        .shop-configurator__option { border: 1px solid var(--shop-line); border-radius: var(--shop-radius-s); padding: 8px 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .shop-configurator__option label { margin: 0; color: var(--shop-ink); cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .shop-configurator__price { color: var(--shop-gold); font-size: 12px; font-weight: 800; white-space: nowrap; }
        .shop-empty { border: 1px solid var(--shop-line); border-radius: var(--shop-radius-s); background: var(--shop-paper-soft); padding: 22px; color: var(--shop-ink-soft); }
        .shop-story { margin-top: 24px; padding-top: 64px; padding-bottom: 64px; border-top: 1px solid var(--shop-line); display: grid; grid-template-columns: .85fr 1.15fr; gap: 60px; align-items: center; }
        .shop-story h2 { max-width: 12ch; margin: 0; font-size: 30px; font-weight: 560; line-height: 1.2; }
        .shop-story__list { display: flex; flex-direction: column; gap: 26px; }
        .shop-story__item { display: flex; gap: 16px; }
        .shop-story__num { width: 32px; flex: 0 0 auto; color: var(--shop-forest); font-family: "Fraunces", Georgia, serif; font-size: 22px; }
        .shop-story__item h3 { margin: 0; font-family: "Inter", system-ui, sans-serif; font-size: 15px; font-weight: 800; }
        .shop-story__item p { max-width: 46ch; margin: 4px 0 0; color: var(--shop-ink-soft); font-size: 13.5px; line-height: 1.5; }
        .product-image-modal, .product-config-modal { position: fixed; inset: 0; z-index: 1080; display: none; align-items: center; justify-content: center; padding: 24px; background: rgba(18, 51, 41, .72); }
        .product-image-modal.is-open, .product-config-modal.is-open { display: flex; }
        .product-image-modal__dialog { width: min(800px, 100%); max-height: 92vh; border-radius: var(--shop-radius-m); overflow: hidden; background: #fff; box-shadow: 0 24px 60px rgba(0, 0, 0, .28); }
        .product-image-modal__header, .product-config-modal__header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 14px 18px; border-bottom: 1px solid var(--shop-line); background: var(--shop-paper-soft); }
        .shop-modal-close { width: 34px; height: 34px; border: 1px solid var(--shop-line); border-radius: 50%; background: #fff; color: var(--shop-ink); cursor: pointer; display: inline-grid; place-items: center; font-size: 23px; font-weight: 400; line-height: 1; }
        .shop-modal-close:hover, .shop-modal-close:focus { border-color: var(--shop-forest); background: var(--shop-forest-tint); color: var(--shop-forest-dark); }
        .product-image-modal__title { margin: 0; color: var(--shop-ink); font-family: "Inter", system-ui, sans-serif; font-size: 1rem; font-weight: 800; }
        .product-image-modal__body { background: var(--shop-paper-soft); display: grid; place-items: center; max-height: calc(92vh - 62px); padding: 18px; }
        .product-image-modal__img { display: block; width: auto; max-width: 100%; max-height: calc(92vh - 98px); border-radius: var(--shop-radius-s); object-fit: contain; }
        .product-config-modal__dialog { width: min(880px, calc(100vw - 48px)); height: min(620px, calc(100vh - 48px)); overflow: hidden; border-radius: var(--shop-radius-m); background: #fff; box-shadow: 0 24px 60px rgba(0, 0, 0, .28); }
        .product-config-modal__title { margin: 0; color: var(--shop-ink); font-family: "Fraunces", Georgia, serif; font-size: 22px; font-weight: 560; }
        .product-config-modal__body { height: calc(100% - 61px); min-height: 0; display: grid; grid-template-columns: .8fr 1.2fr; }
        .product-config-modal__media { min-height: 100%; background: var(--shop-forest-tint); display: flex; align-items: center; justify-content: center; padding: 24px; }
        .product-config-modal__media img { width: 100%; max-height: 540px; border-radius: var(--shop-radius-s); object-fit: contain; }
        .product-config-modal__initial { color: var(--shop-forest); font-family: "Fraunces", Georgia, serif; font-size: 120px; font-weight: 560; }
        .product-config-modal__content { min-height: 0; overflow: auto; padding: 22px; }
        @media (max-width: 960px) { .shop-hero__inner, .shop-story { grid-template-columns: 1fr; } .shop-discover__row { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 640px) { .shop-hero__inner { padding: 44px 20px 40px; gap: 28px; } .shop-hero h1 { font-size: 34px; } .shop-search { flex-direction: column; } .shop-search button { min-height: 42px; } .shop-discover, .shop-categories, .shop-catalog, .shop-story { padding-inline: 20px; } .shop-discover { padding-top: 34px; } .shop-discover__row { grid-template-columns: repeat(2, 1fr); gap: 10px; } .shop-catalog__head { align-items: flex-start; flex-direction: column; } .shop-story { padding-top: 44px; padding-bottom: 44px; } .product-image-modal, .product-config-modal { padding: 14px; } .product-config-modal__dialog { width: calc(100vw - 28px); height: min(620px, calc(100vh - 28px)); } .product-config-modal__body { grid-template-columns: 1fr; } .product-config-modal__media { min-height: 180px; padding: 16px; } .product-config-modal__media img { max-height: 240px; } .product-config-modal__content { padding: 18px; } }
    </style>

    <section class="shop-hero">
        <svg class="shop-hero__mark" viewBox="0 0 32 32" aria-hidden="true">
            <rect x="4" y="4" width="12" height="12" rx="4" fill="none" stroke="#fff" stroke-width=".6"/>
            <rect x="16" y="4" width="12" height="12" rx="4" fill="none" stroke="#fff" stroke-width=".6"/>
            <rect x="4" y="16" width="12" height="12" rx="4" fill="none" stroke="#fff" stroke-width=".6"/>
            <rect x="16" y="16" width="12" height="12" rx="4" fill="none" stroke="#fff" stroke-width=".6"/>
        </svg>
        <div class="shop-hero__inner">
            <div>
                <div class="shop-hero__eyebrow">Compra verificada</div>
                <h1>Todo lo que buscas, en un solo lugar</h1>
                <p>Explora el catalogo disponible, agrega al carrito y finaliza tu pedido con confirmacion inmediata.</p>
                <form method="GET" action="{{ route('shop.index') }}" class="shop-search">
                    <input type="text" name="q" placeholder="Buscar productos por nombre o categoria" value="{{ $search }}">
                    <button type="submit">Buscar</button>
                </form>
            </div>
            <div class="shop-hero-panel">
                <div class="shop-hero-panel__kicker">Ahora mismo</div>
                <div class="shop-hero-panel__figure">{{ number_format($products->total()) }}</div>
                <div class="shop-hero-panel__label">productos disponibles</div>
                <hr>
                <ul>
                    <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Pago rapido y seguro</li>
                    <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Confirmacion inmediata</li>
                    <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Compra desde cualquier dispositivo</li>
                </ul>
            </div>
        </div>
    </section>

    @if ($visibleCategories->isNotEmpty())
        <section class="shop-discover" id="discover">
            <h2>Explora por categoria</h2>
            <div class="shop-discover__row">
                @foreach ($visibleCategories->take(6) as $category)
                    @php
                        $tone = $palette[$loop->index % count($palette)];
                    @endphp
                    <button type="button" class="shop-discover__tile js-shop-filter" data-category="{{ \Illuminate\Support\Str::slug($category->name) }}">
                        <span class="shop-discover__dot" style="background: {{ $tone['tint'] }}">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 2h9l6 6-11 11L2 11z" stroke="{{ $tone['tone'] }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="9" cy="7" r="1.4" fill="{{ $tone['tone'] }}"/>
                            </svg>
                        </span>
                        <span>{{ $category->name }}</span>
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    <nav class="shop-categories" aria-label="Filtros de categoria">
        <button type="button" class="shop-chip is-active js-shop-filter" data-category="all">Todos</button>
        @foreach ($visibleCategories as $category)
            <button type="button" class="shop-chip js-shop-filter" data-category="{{ \Illuminate\Support\Str::slug($category->name) }}">{{ $category->name }}</button>
        @endforeach
    </nav>

    <main class="shop-catalog">
        <div class="shop-catalog__head">
            <h2>Catalogo destacado</h2>
            <span id="shop-result-count">{{ $products->total() }} {{ $products->total() === 1 ? 'producto' : 'productos' }}</span>
        </div>

        <div class="shop-grid" id="shop-product-grid">
            @forelse ($products as $product)
                @php
                    $variantOptions = $product->variants;
                    $hasVariantOptions = $variantOptions->isNotEmpty();
                    $modifierGroups = ($product->uses_component_groups || $isRestaurantCatalog)
                        ? $product->modifierGroups->filter(fn ($group) => $group->options->isNotEmpty())->values()
                        : collect();
                    $hasMenuCustomizer = $modifierGroups->isNotEmpty();
                    $defaultProductId = $hasVariantOptions ? $variantOptions->first()->id : $product->id;
                    $minVariantPrice = $hasVariantOptions ? (float) $variantOptions->min('sale_price') : null;
                    $kitComponents = $product->product_type === \App\Models\Product::TYPE_KIT
                        ? $product->kitItems
                            ->map(function ($item) {
                                $name = $item->componentProduct?->name;
                                return $name ? ['name' => $name, 'quantity' => (float) $item->quantity] : null;
                            })
                            ->filter()
                            ->values()
                        : collect();
                    $tone = $palette[$loop->index % count($palette)];
                    $imageUrl = $product->image_url;
                    $categorySlug = $product->category ? \Illuminate\Support\Str::slug($product->category->name) : 'sin-categoria';
                @endphp

                <article class="shop-product" data-category="{{ $categorySlug }}" data-name="{{ \Illuminate\Support\Str::lower($product->name) }}">
                    <div class="shop-product__media" style="background: {{ $tone['tint'] }}">
                        @if ($product->image_url)
                            <button type="button" class="product-image-trigger" data-product-image="{{ $imageUrl }}" data-product-name="{{ $product->name }}" aria-label="Ver imagen de {{ $product->name }}">
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="shop-product__image">
                                <span class="shop-product__image-label">Ver imagen</span>
                            </button>
                        @else
                            <span class="shop-product__initial" style="color: {{ $tone['tone'] }}">{{ \Illuminate\Support\Str::substr($product->name, 0, 1) }}</span>
                        @endif
                        <span class="shop-stock-tag">En stock</span>
                    </div>

                    <h3>{{ $product->name }}</h3>
                    <div class="shop-product__category">{{ $product->category?->name }}</div>
                    <div class="shop-product__badges">
                        @if ($product->product_type === \App\Models\Product::TYPE_KIT)
                            <span class="shop-product-badge">Kit</span>
                        @elseif ($product->product_type === \App\Models\Product::TYPE_VARIANT)
                            <span class="shop-product-badge">Variante</span>
                        @endif
                    </div>
                    <div class="shop-product__meta">
                        @if ($hasVariantOptions)
                            Selecciona talla o presentacion antes de agregar.
                        @elseif ($product->product_type === \App\Models\Product::TYPE_KIT)
                            Revisa lo que incluye y elige tus opciones antes de agregar.
                        @endif
                    </div>

                    <span class="shop-price">
                        @if ($hasVariantOptions)
                            Desde ${{ number_format($minVariantPrice, 2) }}
                        @else
                            ${{ number_format((float) $product->sale_price, 2) }}
                        @endif
                    </span>

                    @if ($hasMenuCustomizer || $hasVariantOptions || $product->product_type === \App\Models\Product::TYPE_KIT)
                        <div class="shop-configurator">
                            <button type="button" class="shop-configurator__trigger" data-config-modal-open="product-config-modal-{{ $product->id }}">
                                Agregar
                            </button>

                            <div id="product-config-modal-{{ $product->id }}" class="product-config-modal" aria-hidden="true">
                                <div class="product-config-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="product-config-title-{{ $product->id }}">
                                    <div class="product-config-modal__header">
                                        <h2 id="product-config-title-{{ $product->id }}" class="product-config-modal__title">{{ $product->name }}</h2>
                                        <button type="button" class="shop-modal-close" data-config-modal-close aria-label="Cerrar modal" title="Cerrar">&times;</button>
                                    </div>
                                    <div class="product-config-modal__body">
                                        <div class="product-config-modal__media">
                                            @if ($product->image_url)
                                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}">
                                            @else
                                                <span class="product-config-modal__initial">{{ \Illuminate\Support\Str::substr($product->name, 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div class="product-config-modal__content">
                                            <div class="shop-configurator__body">
                                @if ($product->product_type === \App\Models\Product::TYPE_KIT && $kitComponents->isNotEmpty())
                                    <div class="shop-configurator__title">Este kit incluye</div>
                                    <ul class="kit-components-list">
                                        @foreach ($kitComponents as $component)
                                            <li>{{ rtrim(rtrim(number_format($component['quantity'], 3, '.', ''), '0'), '.') }} x {{ $component['name'] }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                <form method="POST" action="{{ route('shop.cart.add') }}" class="shop-product__form d-flex flex-column gap-3">
                                    @csrf
                                    @include('ecommerce.partials.product-variant-options', ['variantOptions' => $variantOptions, 'product' => $product, 'defaultProductId' => $defaultProductId, 'hasVariantOptions' => $hasVariantOptions])

                                    @foreach ($modifierGroups as $group)
                                        @php
                                            $isSingle = $group->selection_type === \App\Models\ProductModifierGroup::TYPE_SINGLE;
                                            $isRemove = $group->selection_type === \App\Models\ProductModifierGroup::TYPE_REMOVE;
                                            $inputType = $isSingle ? 'radio' : 'checkbox';
                                            $inputName = $isSingle ? "modifier_groups[{$group->id}]" : "modifier_groups[{$group->id}][]";
                                        @endphp
                                        <div class="shop-configurator__group">
                                            <div class="shop-configurator__title">
                                                {{ $group->name }}
                                                @if ($group->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </div>
                                            <div class="shop-configurator__hint">
                                                @if ($isRemove)
                                                    Marca lo que no quieres en el plato.
                                                @elseif ($isSingle)
                                                    Elige una opcion.
                                                @elseif ($group->is_required && (int) $group->min_select > 0)
                                                    Debes elegir al menos {{ (int) $group->min_select }} opciones.
                                                @else
                                                    Puedes elegir varias opciones.
                                                @endif
                                            </div>
                                            <div class="shop-configurator__options">
                                                @foreach ($group->options as $option)
                                                    <div class="shop-configurator__option">
                                                        <label for="modifier-{{ $product->id }}-{{ $group->id }}-{{ $option->id }}">
                                                            <input id="modifier-{{ $product->id }}-{{ $group->id }}-{{ $option->id }}" type="{{ $inputType }}" name="{{ $inputName }}" value="{{ $option->id }}" @checked(! $isRemove && $option->is_default) @required($group->is_required && $isSingle)>
                                                            <span>{{ $isRemove ? 'Sin '.$option->label : $option->label }}</span>
                                                        </label>
                                                        @if (! $isRemove && (float) $option->price_delta > 0)
                                                            <span class="shop-configurator__price">+ ${{ number_format((float) $option->price_delta, 2) }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="shop-product__buy-row">
                                        <input type="number" min="1" max="999" name="quantity" value="1" class="shop-product__qty" aria-label="Cantidad">
                                        <button type="submit" class="shop-product__add">Agregar al carrito</button>
                                    </div>
                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('shop.cart.add') }}" class="shop-product__form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $defaultProductId }}">
                            <div class="shop-product__buy-row">
                                <input type="number" min="1" max="999" name="quantity" value="1" class="shop-product__qty" aria-label="Cantidad">
                                <button type="submit" class="shop-product__add">Agregar</button>
                            </div>
                        </form>
                    @endif
                </article>
            @empty
                <div class="shop-empty">No hay productos para mostrar.</div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </main>

    <section class="shop-story">
        <h2>Compra con tranquilidad, de principio a fin</h2>
        <div class="shop-story__list">
            <div class="shop-story__item">
                <span class="shop-story__num">01</span>
                <div>
                    <h3>Encuentra lo que necesitas</h3>
                    <p>Explora el catalogo por categorias, revisa las opciones de cada producto y agrega solo lo que buscas.</p>
                </div>
            </div>
            <div class="shop-story__item">
                <span class="shop-story__num">02</span>
                <div>
                    <h3>Tu pedido, a tu ritmo</h3>
                    <p>Elige cantidades, variantes o componentes antes de llevar cada producto al carrito.</p>
                </div>
            </div>
            <div class="shop-story__item">
                <span class="shop-story__num">03</span>
                <div>
                    <h3>Confirmacion clara</h3>
                    <p>Finaliza tu compra y consulta el estado de tus pedidos desde tu cuenta cuando lo necesites.</p>
                </div>
            </div>
        </div>
    </section>

    <div id="product-image-modal" class="product-image-modal" aria-hidden="true">
        <div class="product-image-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="product-image-modal-title">
            <div class="product-image-modal__header">
                <h2 id="product-image-modal-title" class="product-image-modal__title">Imagen del producto</h2>
                <button type="button" class="shop-modal-close" id="close-product-image-modal" aria-label="Cerrar modal" title="Cerrar">&times;</button>
            </div>
            <div class="product-image-modal__body">
                <img id="product-image-modal-img" class="product-image-modal__img" src="" alt="">
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('product-image-modal');
            const modalImage = document.getElementById('product-image-modal-img');
            const modalTitle = document.getElementById('product-image-modal-title');
            const closeButton = document.getElementById('close-product-image-modal');
            const filters = document.querySelectorAll('.js-shop-filter');
            const chips = document.querySelectorAll('.shop-chip');
            const cards = document.querySelectorAll('.shop-product');
            const resultCount = document.getElementById('shop-result-count');
            const configModals = document.querySelectorAll('.product-config-modal');

            function productLabel(count) {
                return count + (count === 1 ? ' producto' : ' productos');
            }

            filters.forEach(filter => {
                filter.addEventListener('click', function () {
                    const category = filter.dataset.category || 'all';
                    let visible = 0;
                    cards.forEach(card => {
                        const match = category === 'all' || card.dataset.category === category;
                        card.hidden = !match;
                        if (match) {
                            visible++;
                        }
                    });
                    if (resultCount) {
                        resultCount.textContent = productLabel(visible);
                    }
                    chips.forEach(chip => {
                        chip.classList.toggle('is-active', chip.dataset.category === category);
                    });
                });
            });

            function closeModal() {
                if (!modal || !modalImage) {
                    return;
                }
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                modalImage.src = '';
                modalImage.alt = '';
            }

            if (modal && modalImage && modalTitle && closeButton) {
                document.querySelectorAll('.product-image-trigger').forEach(button => {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        const imageUrl = button.dataset.productImage || '';
                        const productName = button.dataset.productName || 'Producto';
                        modalImage.src = imageUrl;
                        modalImage.alt = productName;
                        modalTitle.textContent = productName;
                        modal.classList.add('is-open');
                        modal.setAttribute('aria-hidden', 'false');
                        closeButton.focus();
                    });
                });

                closeButton.addEventListener('click', closeModal);
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
            }

            function closeConfigModal(configModal) {
                configModal.classList.remove('is-open');
                configModal.setAttribute('aria-hidden', 'true');
            }

            document.querySelectorAll('[data-config-modal-open]').forEach(button => {
                button.addEventListener('click', function () {
                    const configModal = document.getElementById(button.dataset.configModalOpen);
                    if (!configModal) {
                        return;
                    }
                    configModal.classList.add('is-open');
                    configModal.setAttribute('aria-hidden', 'false');
                    configModal.querySelector('[data-config-modal-close]')?.focus();
                });
            });

            configModals.forEach(configModal => {
                configModal.querySelector('[data-config-modal-close]')?.addEventListener('click', () => closeConfigModal(configModal));
                configModal.addEventListener('click', function (event) {
                    if (event.target === configModal) {
                        closeConfigModal(configModal);
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                    closeModal();
                }
                if (event.key === 'Escape') {
                    configModals.forEach(closeConfigModal);
                }
            });
        })();
    </script>
@endsection
