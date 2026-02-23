@extends('ecommerce.layouts.app')

@section('content')
    <style>
        .shop-hero {
            background: radial-gradient(1200px 400px at 10% 10%, #c7d2fe 0%, rgba(199,210,254,0) 60%),
                        linear-gradient(135deg, #0f172a 0%, #1d4ed8 45%, #0ea5e9 100%);
            border-radius: 1.25rem;
            color: #fff;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
        }
        .shop-hero::after {
            content: "";
            position: absolute;
            right: -70px;
            top: -90px;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            background: rgba(255,255,255,0.18);
            filter: blur(1px);
        }
        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.52);
            border: 1px solid rgba(191, 219, 254, 0.45);
            color: #f8fafc;
            font-weight: 700;
            font-size: .78rem;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .hero-search {
            background: #fff;
            border-radius: .95rem;
            padding: .5rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        }
        .hero-kpi {
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: .9rem;
            padding: .75rem .9rem;
        }
        .hero-benefits {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 1rem;
            padding: 1rem;
            backdrop-filter: blur(4px);
        }
        .hero-benefits__item {
            border-radius: .75rem;
            padding: .55rem .7rem;
            background: rgba(255,255,255,.10);
            font-size: .85rem;
        }
        .product-card-modern {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease;
            box-shadow: 0 8px 20px rgba(15,23,42,.06);
        }
        .product-card-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px rgba(15,23,42,.12);
        }
        .product-img-modern {
            height: 210px;
            object-fit: cover;
            width: 100%;
            background: #e2e8f0;
        }
        .price-tag {
            background: #e0f2fe;
            color: #0c4a6e;
            border-radius: .7rem;
            padding: .3rem .6rem;
            font-weight: 700;
            font-size: .95rem;
        }
        .product-kind-badge {
            display: inline-flex;
            align-items: center;
            padding: .22rem .58rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .02em;
            width: fit-content;
        }
        .product-kind-badge--kit {
            background: #dcfce7;
            color: #166534;
        }
        .product-kind-badge--variant {
            background: #ede9fe;
            color: #5b21b6;
        }
        .product-kind-meta {
            margin-top: .3rem;
            font-size: .78rem;
            color: #64748b;
            line-height: 1.35;
        }
        .shop-cart-cta {
            color: #0f172a;
            border-color: #94a3b8;
            background: #fff;
        }
        .shop-cart-cta:hover,
        .shop-cart-cta:focus {
            color: #0f172a;
            background: #e2e8f0;
            border-color: #64748b;
        }
        .variant-choice-grid {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: .6rem;
        }
        .variant-choice-input {
            display: none;
        }
        .variant-choice-label {
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #f8fafc;
            color: #0f172a;
            font-size: .75rem;
            font-weight: 700;
            padding: .35rem .6rem;
            cursor: pointer;
            line-height: 1.2;
            min-width: 2rem;
            text-align: center;
            flex: 0 0 auto;
        }
        .variant-choice-input:checked + .variant-choice-label {
            border-color: #2563eb;
            background: #dbeafe;
            color: #1d4ed8;
        }
        .kit-components-list {
            margin-top: .4rem;
            margin-bottom: 0;
            padding-left: 1.1rem;
            font-size: .78rem;
            color: #475569;
            list-style-type: disc;
            list-style-position: outside;
            text-align: left;
        }
        .kit-components-list li {
            margin: .15rem 0;
        }
        body.dark-mode .shop-hero {
            background: radial-gradient(1200px 420px at 10% 10%, rgba(59, 130, 246, .25) 0%, rgba(59,130,246,0) 60%),
                        linear-gradient(135deg, #0b1220 0%, #1e3a8a 50%, #0369a1 100%);
            box-shadow: 0 24px 50px rgba(2, 6, 23, .55);
        }
        body.dark-mode .hero-pill {
            background: rgba(15, 23, 42, .72);
            border-color: rgba(125, 211, 252, .4);
        }
        body.dark-mode .hero-search {
            background: rgba(15, 23, 42, .88);
            box-shadow: 0 12px 28px rgba(2, 6, 23, .55);
        }
        body.dark-mode .hero-search .form-control {
            background: transparent;
            color: #e2e8f0;
        }
        body.dark-mode .hero-search .form-control::placeholder {
            color: #94a3b8;
        }
        body.dark-mode .hero-benefits,
        body.dark-mode .hero-benefits__item,
        body.dark-mode .hero-kpi {
            background: rgba(15, 23, 42, .36);
            border-color: rgba(125, 211, 252, .25);
            color: #e2e8f0;
        }
        body.dark-mode .product-card-modern {
            border-color: rgba(71, 85, 105, .5);
            background: rgba(15, 23, 42, .84) !important;
            box-shadow: 0 12px 26px rgba(2, 6, 23, .45);
        }
        body.dark-mode .product-card-modern:hover {
            box-shadow: 0 18px 34px rgba(2, 6, 23, .65);
        }
        body.dark-mode .product-img-modern {
            background: #1e293b;
        }
        body.dark-mode .product-card-modern .h6,
        body.dark-mode .product-card-modern .text-muted {
            color: #e2e8f0 !important;
        }
        body.dark-mode .product-kind-meta,
        body.dark-mode .kit-components-list {
            color: #94a3b8;
        }
        body.dark-mode .price-tag {
            background: rgba(30, 58, 138, .45);
            color: #bfdbfe;
            border: 1px solid rgba(147, 197, 253, .35);
        }
        body.dark-mode .variant-choice-label {
            border-color: #475569;
            background: rgba(15, 23, 42, .7);
            color: #e2e8f0;
        }
        body.dark-mode .variant-choice-input:checked + .variant-choice-label {
            border-color: #3b82f6;
            background: rgba(30, 58, 138, .52);
            color: #bfdbfe;
        }
        body.dark-mode .shop-cart-cta {
            color: #e2e8f0;
            border-color: #475569;
            background: rgba(15, 23, 42, .75);
        }
        body.dark-mode .shop-cart-cta:hover,
        body.dark-mode .shop-cart-cta:focus {
            color: #f8fafc;
            background: rgba(30, 41, 59, .95);
            border-color: #64748b;
        }
        body.dark-mode .alert-secondary {
            background: rgba(15, 23, 42, .78);
            color: #e2e8f0;
            border-color: rgba(71, 85, 105, .5);
        }
    </style>

    <section class="shop-hero mb-4">
        <div class="row g-4 align-items-center position-relative" style="z-index:2;">
            <div class="col-lg-8">
                <span class="hero-pill">Tienda oficial</span>
                <h1 class="display-6 fw-bold mt-3 mb-2">Compra rapido, seguro y sin filas</h1>
                <p class="mb-3 text-white-50">Explora el catalogo, agrega al carrito y finaliza tu pedido en minutos.</p>
                <form method="GET" action="{{ route('shop.index') }}" class="hero-search d-flex gap-2">
                    <input type="text" name="q" class="form-control border-0 shadow-none" placeholder="Buscar productos por nombre o categoria" value="{{ $search }}">
                    <button class="btn btn-primary px-4" type="submit">Buscar</button>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="hero-benefits">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="small text-white-50">Productos disponibles</div>
                        <div class="fw-semibold">{{ number_format($products->total()) }}</div>
                    </div>
                    <div class="d-grid gap-2">
                        <div class="hero-benefits__item">Pago rapido y seguro</div>
                        <div class="hero-benefits__item">Confirmacion inmediata del pedido</div>
                        <div class="hero-benefits__item">Compra en minutos desde cualquier dispositivo</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h5 mb-0">Catalogo destacado</h2>
        <a href="{{ route('shop.cart') }}" class="btn btn-outline-secondary btn-sm shop-cart-cta d-inline-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 4h2l2.4 10.2a1 1 0 0 0 1 .8h8.8a1 1 0 0 0 1-.8L20 7H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="10" cy="19" r="1.5" fill="currentColor"/>
                <circle cx="17" cy="19" r="1.5" fill="currentColor"/>
            </svg>
            <span>Ver carrito</span>
        </a>
    </div>

    <div class="row g-4">
        @forelse ($products as $product)
            @php
                $variantOptions = $product->variants;
                $hasVariantOptions = $variantOptions->isNotEmpty();
                $defaultProductId = $hasVariantOptions
                    ? $variantOptions->first()->id
                    : $product->id;
                $minVariantPrice = $hasVariantOptions ? (float) $variantOptions->min('sale_price') : null;
                $kitComponents = $product->product_type === \App\Models\Product::TYPE_KIT
                    ? $product->kitItems
                        ->map(function ($item) {
                            $name = $item->componentProduct?->name;
                            if (! $name) {
                                return null;
                            }
                            return [
                                'name' => $name,
                                'quantity' => (float) $item->quantity,
                            ];
                        })
                        ->filter()
                        ->values()
                    : collect();
            @endphp
            <div class="col-12 col-md-6 col-xl-3">
                <div class="product-card-modern h-100 bg-white">
                    <img
                        src="{{ $product->image_url ?: asset('images/product-placeholder.svg') }}"
                        alt="{{ $product->name }}"
                        class="product-img-modern"
                    >
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 mb-1">{{ $product->name }}</h2>
                        @if ($product->product_type === \App\Models\Product::TYPE_KIT)
                            <span class="product-kind-badge product-kind-badge--kit">Kit</span>
                        @elseif ($product->product_type === \App\Models\Product::TYPE_VARIANT)
                            <span class="product-kind-badge product-kind-badge--variant">Variante</span>
                        @endif
                        @if ($product->category)
                            <div class="text-muted small">{{ $product->category->name }}</div>
                        @endif
                        @if ($hasVariantOptions)
                            <div class="product-kind-meta">Selecciona talla o presentacion antes de agregar.</div>
                        @elseif ($product->product_type === \App\Models\Product::TYPE_KIT)
                            <div class="product-kind-meta">
                                @if ($kitComponents->isNotEmpty())
                                    Este kit incluye:
                                    <ul class="kit-components-list">
                                        @foreach ($kitComponents as $component)
                                            <li>{{ rtrim(rtrim(number_format($component['quantity'], 3, '.', ''), '0'), '.') }} x {{ $component['name'] }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    Este kit no tiene componentes configurados.
                                @endif
                            </div>
                        @endif
                        <div class="mt-3">
                            <span class="price-tag">
                                @if ($hasVariantOptions)
                                    Desde ${{ number_format($minVariantPrice, 2) }}
                                @else
                                    ${{ number_format((float) $product->sale_price, 2) }}
                                @endif
                            </span>
                        </div>

                        <form method="POST" action="{{ route('shop.cart.add') }}" class="mt-auto pt-3 d-flex flex-column gap-2 h-100">
                            @csrf
                            @if ($hasVariantOptions)
                                <div class="w-100">
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
                            <div class="d-flex gap-2 align-items-center mt-auto">
                                <input type="number" min="1" max="999" name="quantity" value="1" class="form-control form-control-sm" style="max-width: 88px;">
                                <button type="submit" class="btn btn-primary btn-sm w-100">Agregar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-secondary mb-0">No hay productos para mostrar.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $products->links() }}
    </div>
@endsection
