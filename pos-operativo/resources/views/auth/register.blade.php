<x-auth-shell title="Crea tu cuenta" subtitle="Registrate para comprar y consultar tus pedidos.">
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="auth-field"><label for="name">Nombre</label><div class="auth-input-wrap"><input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Tu nombre" required autofocus autocomplete="name"></div>@error('name') <div class="auth-error">{{ $message }}</div> @enderror</div>
        <div class="auth-field"><label for="email">Correo electronico</label><div class="auth-input-wrap"><input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" required autocomplete="username"></div>@error('email') <div class="auth-error">{{ $message }}</div> @enderror</div>
        <div class="auth-field"><label for="password">Contrasena</label><div class="auth-input-wrap"><input id="password" class="auth-password" type="password" name="password" placeholder="••••••••" required autocomplete="new-password"><button type="button" class="auth-toggle" data-auth-toggle="password" aria-label="Mostrar contrasena">Mostrar</button></div>@error('password') <div class="auth-error">{{ $message }}</div> @enderror</div>
        <div class="auth-field"><label for="password_confirmation">Confirmar contrasena</label><div class="auth-input-wrap"><input id="password_confirmation" class="auth-password" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password"><button type="button" class="auth-toggle" data-auth-toggle="password_confirmation" aria-label="Mostrar contrasena">Mostrar</button></div>@error('password_confirmation') <div class="auth-error">{{ $message }}</div> @enderror</div>
        <button type="submit" class="auth-submit">Crear cuenta</button>
    </form>
    <div class="auth-divider"><span>o</span></div>
    <div class="auth-footer-link">Ya tienes cuenta? <a href="{{ route('login') }}" class="auth-link">Inicia sesion</a></div>
</x-auth-shell>
