<x-auth-shell title="Recupera tu acceso" subtitle="Te enviaremos un enlace para restablecer tu contrasena.">
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="auth-field"><label for="email">Correo electronico</label><div class="auth-input-wrap"><input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" required autofocus autocomplete="username"></div>@error('email') <div class="auth-error">{{ $message }}</div> @enderror</div>
        <button type="submit" class="auth-submit">Enviar enlace de recuperacion</button>
    </form>
    <div class="auth-divider"><span>o</span></div>
    <div class="auth-footer-link">Recordaste tu contrasena? <a href="{{ route('login') }}" class="auth-link">Inicia sesion</a></div>
</x-auth-shell>
