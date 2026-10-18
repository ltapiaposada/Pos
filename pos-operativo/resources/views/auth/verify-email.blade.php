<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-base-content">Verificar correo</h1>
        <p class="mt-1 text-sm text-base-content/60">Confirma tu direccion para activar la cuenta.</p>
    </div>

    <div class="mb-4 text-sm text-base-content/70">
        Gracias por registrarte. Antes de continuar, confirma tu correo electronico mediante el enlace que te enviamos. Si no lo recibiste, podemos enviarte otro.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-success">
            Enviamos un nuevo enlace de verificacion al correo que registraste.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Reenviar correo de verificacion
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-base-content/70 hover:text-base-content rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/40">
                Cerrar sesion
            </button>
        </form>
    </div>
</x-guest-layout>
