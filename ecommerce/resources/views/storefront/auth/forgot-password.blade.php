@extends('storefront.layouts.app')

@push('styles')
.auth-page{max-width:520px;margin:0 auto;padding:56px 24px 80px}.auth-card{border:1px solid var(--line);border-radius:var(--radius-m);background:var(--paper);padding:30px}.auth-card h1{margin:7px 0;font-size:32px}.auth-card p{color:var(--soft);line-height:1.55}
@endpush

@section('content')
<section class="auth-page"><div class="auth-card"><div class="eyebrow" style="color:var(--forest)">Acceso a tu cuenta</div><h1>Restablece tu contraseña</h1><p>Ingresa el correo con el que te registraste. Te enviaremos un enlace seguro para crear una nueva contraseña.</p><form method="post" action="{{ route('shop.password.email') }}" class="form-grid">@csrf<div class="field field--full"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" class="input" value="{{ old('email',request('email')) }}" required autofocus></div><div class="field field--full"><button class="action" style="width:100%">Enviar enlace</button></div></form><p class="note"><a href="{{ route('shop.login') }}" style="color:var(--forest);font-weight:700">Volver a iniciar sesión</a></p></div></section>
@endsection
