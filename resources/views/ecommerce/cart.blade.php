@extends('ecommerce.layouts.app')

@section('content')
    @php $paymentQrUrl = ! empty($business['payment_qr_url']) ? $business['payment_qr_url'] : null; @endphp
    <style>
        .cart-layout { display:grid; grid-template-columns:minmax(0, 1.55fr) minmax(280px, .75fr); gap:24px; align-items:start; }.cart-summary { position:sticky; top:96px; }.cart-selection { margin-top:5px; color:var(--shop-ink-soft); font-size:12px; }.cart-qr { width:94px; border:1px solid var(--shop-line); border-radius:6px; cursor:zoom-in; }.cart-modal { position:fixed; inset:0; z-index:1080; display:none; place-items:center; padding:20px; background:rgba(18,51,41,.72); }.cart-modal.is-open { display:grid; }.cart-modal__dialog { position:relative; width:min(480px,100%); border-radius:var(--shop-radius-m); background:#fff; padding:18px; }.cart-modal__dialog img { width:100%; border-radius:var(--shop-radius-s); }.cart-modal__close { position:absolute; top:10px; right:10px; width:34px; height:34px; border:1px solid var(--shop-line); border-radius:50%; background:#fff; font-size:22px; cursor:pointer; } @media(max-width:900px){.cart-layout{grid-template-columns:1fr}.cart-summary{position:static}}
    </style>
    <section class="shop-page">
        <header class="shop-page-header">
            <div><div class="shop-page-header__eyebrow">Tu compra</div><h1>Tu carrito</h1><p>Revisa los productos y las cantidades antes de continuar.</p></div>
            <a href="{{ route('shop.index') }}" class="shop-action shop-action--soft">Seguir comprando</a>
        </header>
        @if ($cartItems->isEmpty())
            <section class="shop-surface"><h2>Tu carrito esta vacio</h2><p class="shop-note">Cuando agregues un producto aparecera aqui.</p><a href="{{ route('shop.index') }}" class="shop-action mt-3">Ver catalogo</a></section>
        @else
            <div class="cart-layout">
                <section class="shop-surface"><h2>Productos seleccionados</h2>
                    @foreach ($cartItems as $item)
                        <article class="shop-line-item">
                            <div><div class="shop-line-item__name">{{ $item['display_name'] }}</div>
                                @if (! empty($item['selection_summary']))<div class="cart-selection">@foreach ($item['selection_summary'] as $selectionLine)<div>{{ $selectionLine['group'] }}: {{ implode(', ', $selectionLine['labels']) }}</div>@endforeach</div>@endif
                                <div class="shop-line-item__meta">${{ number_format($item['unit_price'], 2) }} · Impuesto ${{ number_format($item['tax'], 2) }}</div>
                                <div class="shop-item-actions"><form method="POST" action="{{ route('shop.cart.update', $item['line_key']) }}" class="d-flex gap-2">@csrf @method('PATCH')<input type="number" name="quantity" min="1" max="999" value="{{ $item['quantity'] }}" class="shop-quantity" aria-label="Cantidad"><button class="shop-action shop-action--soft">Actualizar</button></form><form method="POST" action="{{ route('shop.cart.remove', $item['line_key']) }}">@csrf @method('DELETE')<button class="shop-action shop-action--danger">Quitar</button></form></div>
                            </div><div class="shop-line-item__price">${{ number_format($item['total'], 2) }}</div>
                        </article>
                    @endforeach
                </section>
                <aside class="shop-summary cart-summary"><h2>Resumen de compra</h2><div class="shop-totals"><div class="shop-total-row"><span>Subtotal</span><strong>${{ number_format($summary['subtotal'], 2) }}</strong></div><div class="shop-total-row"><span>Impuestos</span><strong>${{ number_format($summary['tax'], 2) }}</strong></div><div class="shop-total-row shop-total-row--final"><span>Total</span><strong>${{ number_format($summary['total'], 2) }}</strong></div></div>
                    @if ($paymentQrUrl)<div class="shop-notice">Pago por QR disponible al finalizar.<br><img src="{{ $paymentQrUrl }}" alt="QR de pago" class="cart-qr js-qr-trigger mt-2"></div>@endif
                    <a href="{{ route('shop.checkout') }}" class="shop-action w-100 mt-4">Ir a finalizar compra</a>
                </aside>
            </div>
        @endif
    </section>
    @if ($paymentQrUrl)<div class="cart-modal" id="qr-lightbox" aria-hidden="true"><div class="cart-modal__dialog" role="dialog" aria-modal="true"><button type="button" class="cart-modal__close" id="qr-lightbox-close" aria-label="Cerrar">&times;</button><img src="{{ $paymentQrUrl }}" alt="QR de pago ampliado"></div></div>
    <script>(function(){const t=document.querySelector('.js-qr-trigger'),m=document.getElementById('qr-lightbox'),c=document.getElementById('qr-lightbox-close');if(!t||!m||!c)return;const close=()=>{m.classList.remove('is-open');m.setAttribute('aria-hidden','true')};t.addEventListener('click',()=>{m.classList.add('is-open');m.setAttribute('aria-hidden','false')});c.addEventListener('click',close);m.addEventListener('click',e=>{if(e.target===m)close()});document.addEventListener('keydown',e=>{if(e.key==='Escape')close()})})();</script>@endif
@endsection
