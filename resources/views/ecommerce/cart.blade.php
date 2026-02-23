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
                    <div class="table-responsive">
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
                                                <span class="cart-product-name">{{ $item['product']->name }}</span>
                                            </div>
                                        </td>
                                        <td>${{ number_format($item['unit_price'], 2) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('shop.cart.update', $item['product']) }}" class="cart-qty-form">
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
                                            <form method="POST" action="{{ route('shop.cart.remove', $item['product']) }}">
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
                                <img src="{{ $paymentQrUrl }}" alt="QR de pago" class="cart-qr-thumb">
                            </div>
                        @endif
                        <a href="{{ route('shop.checkout') }}" class="btn btn-primary w-100 mt-3">Ir a finalizar compra</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
