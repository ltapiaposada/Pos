<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Escaner remoto POS</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        .wrap {
            max-width: 520px;
            margin: 0 auto;
            padding: 0.65rem;
        }
        h1 {
            margin: 0 0 0.35rem;
            font-size: 1.35rem;
        }
        h2 {
            margin: 0 0 0.35rem;
            font-size: 1.05rem;
        }
        .card {
            background: #111827;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 0.7rem;
            margin-bottom: 0.55rem;
        }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary {
            background: #22c55e;
            color: #052e16;
        }
        .btn-danger {
            background: #f87171;
            color: #450a0a;
        }
        .row {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }
        .status {
            margin: 0.3rem 0;
            font-size: 0.78rem;
            color: #94a3b8;
        }
        video {
            width: 100%;
            height: min(42vh, 260px);
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #334155;
            background: #020617;
        }
        .camera-frame {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
        }
        .camera-guide {
            position: absolute;
            inset: 28% 8%;
            border: 3px solid #22c55e;
            border-radius: 12px;
            box-shadow: 0 0 0 999px rgba(2, 6, 23, 0.28);
            pointer-events: none;
        }
    </style>
</head>
<body data-scan-url="{{ route('pos.scanner.remote.scan', ['token' => $token]) }}">
    <div class="wrap">
        <h1>Escaner remoto</h1>

        <div class="card">
            <h2>Camara del celular</h2>
            <div class="camera-frame">
                <video id="camera-preview" autoplay muted playsinline></video>
                <div class="camera-guide" aria-hidden="true"></div>
            </div>
            <div class="row" style="margin-top: 0.75rem;">
                <button id="start-camera" class="btn btn-primary" type="button">Escanear otro codigo</button>
                <button id="stop-camera" class="btn btn-danger" type="button">Detener camara</button>
            </div>
            <p class="status">Cada lectura se envia una sola vez. Para corregirla, pulsa Escanear otro codigo y lee el nuevo.</p>
            <p id="feedback" class="status">Listo para escanear.</p>
            <p id="scanner-diagnostics" class="status">Lector ZXing local listo.</p>
        </div>
    </div>

    @vite('resources/js/remote-scanner.js')
</body>
</html>
