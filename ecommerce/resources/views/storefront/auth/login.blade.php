@extends('storefront.layouts.app')

@push('styles')
.auth-page{max-width:520px;margin:0 auto;padding:56px 24px 80px}.auth-card{border:1px solid var(--line);border-radius:var(--radius-m);background:var(--paper);padding:30px}.auth-card h1{margin:7px 0;font-size:32px}.auth-card p{color:var(--soft);line-height:1.55}.auth-footer{margin-top:18px;text-align:center;color:var(--soft);font-size:13px}.auth-footer a{color:var(--forest);font-weight:700}
@endpush

@section('content')
<section class="auth-page"><div class="auth-card"><div class="eyebrow" style="color:var(--forest)">Tu cuenta</div><h1>Inicia sesión</h1><p>Accede para comprar y consultar todos tus pedidos.</p><form method="post" action="{{ route('shop.login.store') }}" class="form-grid">@csrf<div class="field field--full"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autofocus></div><div class="field field--full"><label for="password">Contraseña</label><input id="password" name="password" type="password" class="input" required></div><label style="font-size:13px"><input type="checkbox" name="remember" value="1"> Mantener mi sesión</label><div class="field field--full" style="display:flex;justify-content:space-between;align-items:center;gap:12px"><a href="{{ route('shop.password.request') }}" style="color:var(--forest);font-size:13px;font-weight:700">¿Olvidaste tu contraseña?</a><button class="action">Ingresar</button></div></form><div class="auth-footer">¿Aún no tienes cuenta? <a href="{{ route('shop.register') }}">Regístrate</a></div></div></section>
@endsection
