<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-base-content">Confirmar contrasena</h1>
        <p class="mt-1 text-sm text-base-content/60">Validacion requerida para continuar.</p>
    </div>

    <div class="mb-4 text-sm text-base-content/70">
        Esta es un area segura de la aplicacion. Confirma tu contrasena antes de continuar.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Contrasena" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-primary-button>
                Confirmar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
