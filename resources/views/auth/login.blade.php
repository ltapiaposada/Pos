<x-auth-shell title="Inicia sesion" subtitle="Ingresa tus credenciales para continuar.">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="auth-field">
            <label for="email">Correo electronico</label>
            <div class="auth-input-wrap"><input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" required autofocus autocomplete="username"></div>
            @error('email') <div class="auth-error">{{ $message }}</div> @enderror
        </div>
        <div class="auth-field">
            <label for="password">Contrasena</label>
            <div class="auth-input-wrap"><input id="password" class="auth-password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password"><button type="button" class="auth-toggle" data-auth-toggle="password" aria-label="Mostrar contrasena">Mostrar</button></div>
            @error('password') <div class="auth-error">{{ $message }}</div> @enderror
        </div>
        <div class="auth-options">
            <label class="auth-remember" for="remember"><input id="remember" type="checkbox" name="remember" @checked(old('remember'))>Recordarme</label>
            @if (Route::has('password.request'))<a href="{{ route('password.request') }}" class="auth-link">Olvidaste tu contrasena?</a>@endif
        </div>
        <button type="submit" class="auth-submit">Iniciar sesion</button>
    </form>
    <div class="auth-divider"><span>o</span></div>
    <div class="auth-footer-link">No tienes cuenta? <a href="{{ route('register') }}" class="auth-link">Registrate gratis</a></div>
</x-auth-shell>
