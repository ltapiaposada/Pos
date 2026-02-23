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
        .checkout-shell {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }
        .checkout-summary {
            border: 1px solid #dbeafe;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 10px 24px rgba(14, 116, 144, 0.09);
        }
        .payment-qr-box {
            border: 1px solid #bfdbfe;
            border-radius: .9rem;
            background: #f8fbff;
            padding: .8rem;
        }
        .payment-qr-image {
            width: 180px;
            max-width: 100%;
            border-radius: .75rem;
            border: 1px solid #cbd5e1;
            background: #fff;
        }
        body.dark-mode .shop-mini-hero {
            background: linear-gradient(135deg, #0b1220 0%, #1e3a8a 55%, #0369a1 100%);
            box-shadow: 0 16px 36px rgba(2, 6, 23, .55);
        }
        body.dark-mode .checkout-shell {
            border-color: rgba(71, 85, 105, .55);
            background: rgba(15, 23, 42, .84);
            box-shadow: 0 12px 28px rgba(2, 6, 23, .45);
            color: #e2e8f0;
        }
        body.dark-mode .checkout-summary {
            border-color: rgba(59, 130, 246, .35);
            background: linear-gradient(180deg, rgba(15, 23, 42, .9) 0%, rgba(15, 23, 42, .74) 100%);
            box-shadow: 0 12px 28px rgba(2, 6, 23, .5);
            color: #e2e8f0;
        }
        body.dark-mode .checkout-shell .form-control,
        body.dark-mode .checkout-shell .form-select {
            background: rgba(15, 23, 42, .75);
            border-color: #475569;
            color: #e2e8f0;
        }
        body.dark-mode .checkout-shell .form-control::placeholder {
            color: #94a3b8;
        }
        body.dark-mode .checkout-shell .form-label {
            color: #cbd5e1;
        }
        body.dark-mode .checkout-summary .list-group-item {
            background: transparent;
            border-color: rgba(71, 85, 105, .55);
            color: #e2e8f0;
        }
        body.dark-mode .payment-qr-box {
            border-color: rgba(59, 130, 246, .45);
            background: rgba(30, 58, 138, .22);
        }
        body.dark-mode .payment-qr-image {
            border-color: rgba(100, 116, 139, .55);
            background: #0f172a;
        }
    </style>

    <section class="shop-mini-hero">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 text-white">Finalizar compra</h1>
                <p class="mb-0 text-white-50">Completa tus datos y confirma tu pedido.</p>
            </div>
            <a href="{{ route('shop.cart') }}" class="btn btn-light btn-sm">Volver al carrito</a>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="checkout-shell">
                <div class="card-body">
                    <form method="POST" action="{{ route('shop.place-order') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Telefono</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Direccion de entrega</label>
                            <input type="text" id="address" name="address" class="form-control" required value="{{ old('address', $customer->address) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Metodo de pago</label>
                            <select id="payment_method" name="payment_method" class="form-select" required>
                                <option value="card" @selected(old('payment_method') === 'card')>Tarjeta</option>
                                <option value="transfer" @selected(old('payment_method') === 'transfer')>Transferencia</option>
                                <option value="contraentrega" @selected(old('payment_method') === 'contraentrega')>Contraentrega</option>
                                @if ($paymentQrUrl)
                                    <option value="qr" @selected(old('payment_method') === 'qr')>QR</option>
                                @endif
                                <option value="other" @selected(old('payment_method') === 'other')>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="coupon_code" class="form-label">Cupon</label>
                            <input type="text" id="coupon_code" name="coupon_code" class="form-control" value="{{ old('coupon_code') }}" placeholder="Ej: BIENVENIDO10">
                        </div>
                        <div class="col-md-6" id="payment-reference-wrapper">
                            <label for="payment_reference" class="form-label">Referencia de pago</label>
                            <input
                                type="text"
                                id="payment_reference"
                                name="payment_reference"
                                class="form-control"
                                value="{{ old('payment_reference') }}"
                                placeholder="Ej: TRX-123456"
                            >
                        </div>
                        <div class="col-12">
                            <label for="customer_note" class="form-label">Nota del pedido</label>
                            <input type="text" id="customer_note" name="customer_note" class="form-control" value="{{ old('customer_note') }}" placeholder="Indicaciones de entrega (opcional)">
                        </div>
                        @if ($paymentQrUrl)
                            <div class="col-12" id="payment-qr-wrapper">
                                <div class="payment-qr-box">
                                    <div class="fw-semibold mb-2">Pago por QR</div>
                                    <p class="text-muted small mb-2">Si eliges QR, escanea esta imagen para realizar el pago.</p>
                                    <img src="{{ $paymentQrUrl }}" alt="QR de pago" class="payment-qr-image">
                                </div>
                            </div>
                        @endif
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Confirmar pedido</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="checkout-summary">
                <div class="card-body">
                    <h2 class="h6">Resumen</h2>
                    <ul class="list-group list-group-flush mb-3">
                        @foreach ($cartItems as $item)
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>{{ $item['product']->name }} x {{ $item['quantity'] }}</span>
                                <span>${{ number_format($item['total'], 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong>${{ number_format($summary['subtotal'], 2) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Impuestos</span><strong>${{ number_format($summary['tax'], 2) }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Total</span><strong>${{ number_format($summary['total'], 2) }}</strong></div>
                    <div class="small text-muted mt-2">El envio y descuento por cupon se calculan al confirmar.</div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const methodSelect = document.getElementById('payment_method');
            const qrWrapper = document.getElementById('payment-qr-wrapper');
            const paymentReferenceWrapper = document.getElementById('payment-reference-wrapper');
            const paymentReferenceInput = document.getElementById('payment_reference');
            if (!methodSelect || !paymentReferenceWrapper || !paymentReferenceInput) {
                return;
            }

            function togglePaymentBlocks() {
                const method = methodSelect.value;
                const showReference = method === 'transfer' || method === 'qr';

                if (qrWrapper) {
                    qrWrapper.classList.toggle('d-none', method !== 'qr');
                }

                paymentReferenceWrapper.classList.toggle('d-none', !showReference);
                paymentReferenceInput.required = showReference;

                if (!showReference) {
                    paymentReferenceInput.value = '';
                }
            }

            methodSelect.addEventListener('change', togglePaymentBlocks);
            togglePaymentBlocks();
        })();
    </script>
@endsection
