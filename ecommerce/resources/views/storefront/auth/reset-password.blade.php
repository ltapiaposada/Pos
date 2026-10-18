@extends('storefront.layouts.app')

@push('styles')
.auth-page{max-width:520px;margin:0 auto;padding:56px 24px 80px}.auth-card{border:1px solid var(--line);border-radius:var(--radius-m);background:var(--paper);padding:30px}.auth-card h1{margin:7px 0;font-size:32px}.auth-card p{color:var(--soft);line-height:1.55}
@endpush

@section('content')
<section class="auth-page"><div class="auth-card"><div class="eyebrow" style="color:var(--forest)">Nueva contraseña</div><h1>Actualiza tu acceso</h1><p>Elige una contraseña nueva para seguir consultando tus pedidos.</p><form method="post" action="{{ route('shop.password.update') }}" class="form-grid">@csrf<input type="hidden" name="token" value="{{ $token }}"><div class="field field--full"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" class="input" value="{{ old('email',$email) }}" required></div><div class="field"><label for="password">Nueva contraseña</label><input id="password" name="password" type="password" class="input" required></div><div class="field"><label for="password_confirmation">Confirmar contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" class="input" required></div><div class="field field--full"><button class="action" style="width:100%">Guardar contraseña</button></div></form></div></section>
@endsection
