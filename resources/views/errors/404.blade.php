<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página no disponible</title>
    @vite(['resources/css/vendor.css', 'resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, .18), transparent 28%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, .18), transparent 24%),
                linear-gradient(135deg, #eff6ff 0%, #f8fafc 45%, #dbeafe 100%);
            color: #0f172a;
        }

        .error-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .error-card {
            width: min(920px, 100%);
            border-radius: 32px;
            background: rgba(255, 255, 255, .9);
            border: 1px solid rgba(148, 163, 184, .28);
            box-shadow: 0 28px 70px rgba(15, 23, 42, .14);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .error-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
        }

        .error-copy {
            padding: 3rem;
        }

        .error-hero {
            position: relative;
            overflow: hidden;
            min-height: 100%;
            padding: 3rem 2.5rem;
            background: linear-gradient(160deg, #0f172a 0%, #1d4ed8 55%, #0ea5e9 100%);
            color: #fff;
        }

        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .8rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, .3);
            border: 1px solid rgba(255, 255, 255, .2);
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .error-code {
            font-size: clamp(4rem, 9vw, 6rem);
            line-height: .9;
            font-weight: 800;
            letter-spacing: -.05em;
            margin: 1.5rem 0 .75rem;
        }

        .error-title {
            font-size: clamp(2rem, 4vw, 2.8rem);
            line-height: 1.05;
            font-weight: 800;
            margin: 0 0 1rem;
        }

        .error-text {
            font-size: 1rem;
            line-height: 1.7;
            color: #475569;
            max-width: 34rem;
        }

        .error-host {
            margin: 1.25rem 0 0;
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .85rem;
            margin-top: 1.75rem;
        }

        .error-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-width: 180px;
            border-radius: 999px;
            padding: .9rem 1.2rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
        }

        .error-btn:hover {
            transform: translateY(-1px);
        }

        .error-btn--primary {
            background: #0f172a;
            color: #fff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .18);
        }

        .error-btn--secondary {
            background: #fff;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }

        .hero-orb {
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, .13);
            filter: blur(2px);
        }

        .hero-orb--one {
            width: 210px;
            height: 210px;
            right: -70px;
            top: -40px;
        }

        .hero-orb--two {
            width: 150px;
            height: 150px;
            left: -35px;
            bottom: 38px;
        }

        .hero-panel {
            position: relative;
            z-index: 1;
            padding: 1.2rem;
            border-radius: 24px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            backdrop-filter: blur(6px);
        }

        .hero-panel__label {
            font-size: .76rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(255, 255, 255, .72);
            margin-bottom: .45rem;
        }

        .hero-panel__value {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .hero-list {
            margin: 1.4rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: .75rem;
        }

        .hero-list li {
            display: flex;
            align-items: center;
            gap: .6rem;
            color: rgba(255, 255, 255, .9);
        }

        .hero-list__dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #bfdbfe;
            flex: 0 0 auto;
        }

        @media (max-width: 900px) {
            .error-grid {
                grid-template-columns: 1fr;
            }

            .error-copy,
            .error-hero {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <main class="error-shell">
        <section class="error-card">
            <div class="error-grid">
                <div class="error-copy">
                    <span class="error-badge">Sitio no disponible</span>
                    <div class="error-code">404</div>
                    <h1 class="error-title">Esta tienda no está configurada para este dominio.</h1>
                    <p class="error-text">
                        No encontramos una empresa pública asociada al host que estás usando. Si estás probando en local,
                        asegúrate de que el dominio exista en la empresa y apunte a tu servidor.
                    </p>

                    <div class="error-host">
                        <strong>Dominio actual:</strong> {{ request()->getHost() }}
                        @if (! empty($exception?->getMessage()))
                            <div class="mt-2 small">{{ $exception->getMessage() }}</div>
                        @endif
                    </div>

                    <div class="error-actions">
                        <a href="{{ route('login') }}" class="error-btn error-btn--primary">Ir al panel</a>
                        <a href="javascript:history.back()" class="error-btn error-btn--secondary">Volver</a>
                    </div>
                </div>

                <aside class="error-hero">
                    <span class="hero-orb hero-orb--one"></span>
                    <span class="hero-orb hero-orb--two"></span>

                    <div class="hero-panel">
                        <div class="hero-panel__label">Validación de dominio</div>
                        <div class="hero-panel__value">Coincidencia exacta por host</div>
                    </div>

                    <ul class="hero-list">
                        <li><span class="hero-list__dot"></span>El catálogo público solo carga si <code>companies.domain</code> coincide.</li>
                        <li><span class="hero-list__dot"></span>No se usa empresa por defecto cuando el dominio no existe.</li>
                        <li><span class="hero-list__dot"></span>El carrito y los pedidos quedan aislados por empresa.</li>
                    </ul>
                </aside>
            </div>
        </section>
    </main>
</body>
</html>
