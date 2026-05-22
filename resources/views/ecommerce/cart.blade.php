@extends('ecommerce.layouts.app')

@section('content')
    @php
        $paymentQrUrl = ! empty($business['payment_qr_url']) ? $business['payment_qr_url'] : null;
    @endphp
    <style>
        .shop-mini-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #0ea5e9 100%);
            border-radius: 1rem;
            color: #fff;
            padding: 1.4rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .18);
        }
        .cart-shell {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            overflow: hidden;
        }
        .cart-summary {
            border: 1px solid #dbeafe;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 10px 24px rgba(14, 116, 144, 0.09);
        }
        .cart-qr-note {
            border: 1px dashed #93c5fd;
            border-radius: .8rem;
            background: #f8fbff;
            padding: .7rem;
        }
        .cart-qr-thumb {
            width: 96px;
            max-width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: .6rem;
            background: #fff;
            cursor: zoom-in;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .cart-qr-thumb:hover {
            transform: scale(1.04);
            box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
        }
        .qr-lightbox {
            position: fixed;
            inset: 0;
            z-index: 1080;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: rgba(15, 23, 42, .82);
            backdrop-filter: blur(4px);
        }
        .qr-lightbox.is-open {
            display: flex;
        }
        .qr-lightbox__dialog {
            position: relative;
            width: min(92vw, 540px);
            border-radius: 1.25rem;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
            padding: 1rem;
        }
        .qr-lightbox__close {
            position: absolute;
            top: .75rem;
            right: .75rem;
            border: 0;
            border-radius: 999px;
            width: 2.25rem;
            height: 2.25rem;
            background: rgba(15, 23, 42, .08);
            color: #0f172a;
            font-size: 1.25rem;
            line-height: 1;
        }
        .qr-lightbox__image {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 1rem;
            border: 1px solid #cbd5e1;
            background: #fff;
        }
        .cart-qty-form {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: nowrap;
        }
        .cart-product-cell {
            min-width: 260px;
        }
        .cart-product-wrap {
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr);
            align-items: center;
            gap: .5rem;
        }
        .cart-product-image {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 8px;
            display: block;
        }
        .cart-product-name {
            display: block;
            white-space: normal;
            overflow-wrap: anywhere;
            line-height: 1.2;
        }
        .cart-product-modifiers {
            margin-top: .45rem;
            display: grid;
            gap: .2rem;
        }
        .cart-product-modifier {
            font-size: .78rem;
            color: #64748b;
        }
        .cart-qty-input {
            width: 112px;
            min-width: 112px;
        }
        @media (max-width: 576px) {
            .cart-product-cell {
                min-width: 220px;
            }
            .cart-qty-input {
                width: 128px;
                min-width: 128px;
            }
        }
        body.dark-mode .shop-mini-hero {
            background: linear-gradient(135deg, #0b1220 0%, #1e3a8a 55%, #0369a1 100%);
            box-shadow: 0 16px 36px rgba(2, 6, 23, .55);
        }
        body.dark-mode .cart-shell {
            border-color: rgba(71, 85, 105, .55);
            background: rgba(15, 23, 42, .82);
            box-shadow: 0 12px 28px rgba(2, 6, 23, .45);
        }
        body.dark-mode .cart-shell .table {
            color: #e2e8f0;
        }
        body.dark-mode .cart-shell .table-light > tr > th,
        body.dark-mode .cart-shell .table-light > th {
            background-color: rgba(30, 41, 59, .92);
            color: #cbd5e1;
            border-color: rgba(71, 85, 105, .65);
        }
        body.dark-mode .cart-shell .table > :not(caption) > * > * {
            border-color: rgba(71, 85, 105, .55);
            background-color: transparent;
            color: inherit;
        }
        body.dark-mode .cart-summary {
            border-color: rgba(59, 130, 246, .35);
            background: linear-gradient(180deg, rgba(15, 23, 42, .9) 0%, rgba(15, 23, 42, .74) 100%);
            box-shadow: 0 12px 28px rgba(2, 6, 23, .5);
            color: #e2e8f0;
        }
        body.dark-mode .cart-qr-note {
            border-color: rgba(59, 130, 246, .45);
            background: rgba(30, 58, 138, .24);
        }
        body.dark-mode .cart-qr-thumb {
            border-color: rgba(100, 116, 139, .55);
            background: #0f172a;
        }
        body.dark-mode .qr-lightbox__dialog {
            background: #0f172a;
            box-shadow: 0 24px 60px rgba(2, 6, 23, .55);
        }
        body.dark-mode .qr-lightbox__close {
            background: rgba(148, 163, 184, .12);
            color: #e2e8f0;
        }
        body.dark-mode .qr-lightbox__image {
            border-color: rgba(100, 116, 139, .55);
            background: #0f172a;
        }
        body.dark-mode .cart-shell .form-control,
        body.dark-mode .cart-shell .btn-outline-secondary {
            background: rgba(15, 23, 42, .75);
            color: #e2e8f0;
            border-color: #475569;
        }
        body.dark-mode .cart-shell .btn-outline-secondary:hover,
        body.dark-mode .cart-shell .btn-outline-secondary:focus {
            background: rgba(30, 41, 59, .95);
            color: #f8fafc;
            border-color: #64748b;
        }
        body.dark-mode .alert-secondary {
            background: rgba(15, 23, 42, .78);
            color: #e2e8f0;
            border-color: rgba(71, 85, 105, .5);
        }
        body.dark-mode .cart-product-modifier {
            color: #94a3b8;
        }
    </style>

    <section class="shop-mini-hero">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 text-white">Tu carrito</h1>
                <p class="mb-0 text-white-50">Revisa cantidades y totales antes de pasar al pago.</p>
            </div>
            <a href="{{ route('shop.index') }}" class="btn btn-light btn-sm">Seguir comprando</a>
        </div>
    </section>

    @if ($cartItems->isEmpty())
        <div class="alert alert-secondary">Tu carrito esta vacio.</div>
        <a href="{{ route('shop.index') }}" class="btn btn-primary">Ver catalogo</a>
    @else
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="cart-shell">
                    <div class="d-md-none p-3">
                        @foreach ($cartItems as $item)
                            <article class="border rounded-3 p-3 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $item['product']->image_url ?: asset('images/product-placeholder.svg') }}" alt="{{ $item['product']->name }}" class="cart-product-image">
                                    <div>
                                        <div class="fw-semibold">{{ $item['display_name'] }}</div>
                                        @if (! empty($item['selection_summary']))
                                            <div class="cart-product-modifiers">
                                                @foreach ($item['selection_summary'] as $selectionLine)
                                                    <div class="cart-product-modifier">{{ $selectionLine['group'] }}: {{ implode(', ', $selectionLine['labels']) }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="small mt-2">Precio: ${{ number_format($item['unit_price'], 2) }}</div>
                                <div class="small">Impuesto: ${{ number_format($item['tax'], 2) }}</div>
                                <div class="small">Total: ${{ number_format($item['total'], 2) }}</div>
                                <form method="POST" action="{{ route('shop.cart.update', $item['line_key']) }}" class="mt-2 d-grid gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" min="1" max="999" value="{{ $item['quantity'] }}" class="form-control form-control-sm">
                                    <button class="btn btn-outline-secondary btn-sm">Actualizar cantidad</button>
                                </form>
                                <form method="POST" action="{{ route('shop.cart.remove', $item['line_key']) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm w-100">Quitar</button>
                                </form>
                            </article>
                        @endforeach
                    </div>

                    <div class="table-responsive d-none d-md-block">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Impuesto</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cartItems as $item)
                                    <tr>
                                        <td class="cart-product-cell">
                                            <div class="cart-product-wrap">
                                                <img src="{{ $item['product']->image_url ?: asset('images/product-placeholder.svg') }}" alt="{{ $item['product']->name }}" class="cart-product-image">
                                                <span class="cart-product-name">
                                                    {{ $item['display_name'] }}
                                                    @if (! empty($item['selection_summary']))
                                                        <span class="cart-product-modifiers">
                                                            @foreach ($item['selection_summary'] as $selectionLine)
                                                                <span class="cart-product-modifier">{{ $selectionLine['group'] }}: {{ implode(', ', $selectionLine['labels']) }}</span>
                                                            @endforeach
                                                        </span>
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        <td>${{ number_format($item['unit_price'], 2) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('shop.cart.update', $item['line_key']) }}" class="cart-qty-form">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" min="1" max="999" value="{{ $item['quantity'] }}" class="form-control form-control-sm cart-qty-input">
                                                <button class="btn btn-outline-secondary btn-sm">Actualizar</button>
                                            </form>
                                        </td>
                                        <td>${{ number_format($item['subtotal'], 2) }}</td>
                                        <td>${{ number_format($item['tax'], 2) }}</td>
                                        <td>${{ number_format($item['total'], 2) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('shop.cart.remove', $item['line_key']) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm">Quitar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="cart-summary">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-3">Resumen de compra</h2>
                        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>${{ number_format($summary['subtotal'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span>Impuestos</span><strong>${{ number_format($summary['tax'], 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between"><span>Total</span><strong>${{ number_format($summary['total'], 2) }}</strong></div>
                        @if ($paymentQrUrl)
                            <div class="cart-qr-note mt-3">
                                <div class="small fw-semibold mb-1">Pago por QR disponible</div>
                                <div class="small text-muted mb-2">Lo veras al finalizar compra.</div>
                                <div class="small text-muted mb-2">Toca el QR para ampliarlo.</div>
                                <img src="{{ $paymentQrUrl }}" alt="QR de pago" class="cart-qr-thumb js-qr-trigger">
                            </div>
                        @endif
                        <a href="{{ route('shop.checkout') }}" class="btn btn-primary w-100 mt-3">Ir a finalizar compra</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if ($paymentQrUrl)
        <div class="qr-lightbox" id="qr-lightbox" aria-hidden="true">
            <div class="qr-lightbox__dialog" role="dialog" aria-modal="true" aria-label="QR de pago ampliado">
                <button type="button" class="qr-lightbox__close" id="qr-lightbox-close" aria-label="Cerrar">&times;</button>
                <img src="{{ $paymentQrUrl }}" alt="QR de pago ampliado" class="qr-lightbox__image">
            </div>
        </div>
        <script>
            (function () {
                const trigger = document.querySelector('.js-qr-trigger');
                const lightbox = document.getElementById('qr-lightbox');
                const closeBtn = document.getElementById('qr-lightbox-close');

                if (!trigger || !lightbox || !closeBtn) {
                    return;
                }

                function closeLightbox() {
                    lightbox.classList.remove('is-open');
                    lightbox.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }

                function openLightbox() {
                    lightbox.classList.add('is-open');
                    lightbox.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }

                trigger.addEventListener('click', openLightbox);
                closeBtn.addEventListener('click', closeLightbox);
                lightbox.addEventListener('click', function (event) {
                    if (event.target === lightbox) {
                        closeLightbox();
                    }
                });
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
                        closeLightbox();
                    }
                });
            })();
        </script>
    @endif
@endsection
