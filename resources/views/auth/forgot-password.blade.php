<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-base-content">Recuperar acceso</h1>
        <p class="mt-1 text-sm text-base-content/60">Te enviaremos un enlace para restablecer tu contrasena.</p>
    </div>

    <div class="mb-4 text-sm text-base-content/70">
        Ingresa tu correo y te enviaremos un enlace para crear una nueva contrasena.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electronico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end">
            <x-primary-button>
                Enviar enlace de recuperacion
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
