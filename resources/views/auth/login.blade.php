<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-base-content">Iniciar</h1>
        <p class="mt-1 text-sm text-base-content/60">Ingresa tus credenciales para acceder.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Contraseña" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="checkbox checkbox-primary" name="remember">
                <span class="ms-2 text-sm text-base-content/70">Recordarme</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a class="underline text-sm text-base-content/70 hover:text-base-content rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/40" href="{{ route('register') }}">
                Crear cuenta
            </a>
            @if (Route::has('password.request'))
                <a class="underline text-sm text-base-content/70 hover:text-base-content rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/40" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif

            <x-primary-button class="ms-3">Iniciar</x-primary-button>
        </div>
    </form>
</x-guest-layout>
