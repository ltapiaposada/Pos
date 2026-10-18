<x-auth-shell title="Restablece tu contrasena" subtitle="Define una contrasena segura para tu cuenta.">
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="auth-field"><label for="email">Correo electronico</label><div class="auth-input-wrap"><input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" readonly></div>@error('email') <div class="auth-error">{{ $message }}</div> @enderror</div>
        <div class="auth-field"><label for="password">Nueva contrasena</label><div class="auth-input-wrap"><input id="password" class="auth-password" type="password" name="password" placeholder="••••••••" required autocomplete="new-password"><button type="button" class="auth-toggle" data-auth-toggle="password" aria-label="Mostrar contrasena">Mostrar</button></div>@error('password') <div class="auth-error">{{ $message }}</div> @enderror</div>
        <div class="auth-field"><label for="password_confirmation">Confirmar contrasena</label><div class="auth-input-wrap"><input id="password_confirmation" class="auth-password" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password"><button type="button" class="auth-toggle" data-auth-toggle="password_confirmation" aria-label="Mostrar contrasena">Mostrar</button></div>@error('password_confirmation') <div class="auth-error">{{ $message }}</div> @enderror</div>
        <button type="submit" class="auth-submit">Restablecer contrasena</button>
    </form>
</x-auth-shell>
