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
            max-width: 720px;
            margin: 0 auto;
            padding: 1rem;
        }
        .card {
            background: #111827;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            font-size: 0.95rem;
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
        .btn-secondary {
            background: #334155;
            color: #f8fafc;
        }
        input {
            width: 100%;
            box-sizing: border-box;
            padding: 0.65rem 0.8rem;
            border-radius: 10px;
            border: 1px solid #475569;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 1rem;
        }
        .row {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }
        .status {
            font-size: 0.9rem;
            color: #94a3b8;
        }
        video {
            width: 100%;
            border-radius: 12px;
            border: 1px solid #334155;
            background: #020617;
            min-height: 200px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Escaner remoto</h1>
        <p class="status">Token de sesion: <code>{{ $token }}</code></p>

        <div class="card">
            <h2>Camara del celular</h2>
            <p class="status">Usa la camara para leer codigos de barras y enviarlos al POS.</p>
            <video id="camera-preview" autoplay muted playsinline></video>
            <div class="row" style="margin-top: 0.75rem;">
                <button id="start-camera" class="btn btn-primary" type="button">Iniciar camara</button>
                <button id="stop-camera" class="btn btn-danger" type="button">Detener camara</button>
            </div>
        </div>

        <div class="card">
            <h2>Ingreso manual</h2>
            <p class="status">Si no detecta la camara, pega o escribe el codigo y envialo.</p>
            <div class="row">
                <input id="manual-barcode" placeholder="Codigo de barras o SKU" autocomplete="off">
                <button id="send-manual" class="btn btn-secondary" type="button">Enviar al POS</button>
            </div>
        </div>

        <div class="card">
            <p id="feedback" class="status">Listo para escanear.</p>
        </div>
    </div>

    <script>
        (() => {
            const token = @js($token);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const video = document.getElementById('camera-preview');
            const startBtn = document.getElementById('start-camera');
            const stopBtn = document.getElementById('stop-camera');
            const manualInput = document.getElementById('manual-barcode');
            const manualBtn = document.getElementById('send-manual');
            const feedback = document.getElementById('feedback');

            let stream = null;
            let detector = null;
            let isScanning = false;
            let lastCode = '';
            let lastSentAt = 0;
            let zxingReader = null;

            function setFeedback(message) {
                feedback.textContent = message;
            }

            async function sendBarcode(rawCode) {
                const barcode = String(rawCode || '').trim();
                if (!barcode) {
                    return;
                }

                try {
                    const response = await fetch(`{{ route('pos.scanner.remote.scan', ['token' => $token]) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ barcode }),
                    });

                    if (!response.ok) {
                        setFeedback('No se pudo enviar el codigo al POS.');
                        return;
                    }

                    if (navigator.vibrate) {
                        navigator.vibrate(80);
                    }
                    setFeedback(`Enviado: ${barcode}`);
                } catch (error) {
                    setFeedback('Error de red enviando codigo al POS.');
                }
            }

            async function scanLoop() {
                if (!isScanning || !detector || !video) {
                    return;
                }

                try {
                    const barcodes = await detector.detect(video);
                    if (Array.isArray(barcodes) && barcodes.length > 0) {
                        const now = Date.now();
                        const code = String(barcodes[0].rawValue || '').trim();
                        if (code && (code !== lastCode || now - lastSentAt > 1500)) {
                            lastCode = code;
                            lastSentAt = now;
                            await sendBarcode(code);
                        }
                    }
                } catch (error) {
                    setFeedback('No fue posible detectar codigo. Prueba con mejor luz.');
                }

                if (isScanning) {
                    requestAnimationFrame(scanLoop);
                }
            }

            function canUseNativeDetector() {
                return 'BarcodeDetector' in window;
            }

            async function loadZxingReader() {
                if (window.ZXing && window.ZXing.BrowserMultiFormatReader) {
                    return new window.ZXing.BrowserMultiFormatReader();
                }

                await new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js';
                    script.async = true;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });

                if (!(window.ZXing && window.ZXing.BrowserMultiFormatReader)) {
                    throw new Error('ZXing no disponible');
                }

                return new window.ZXing.BrowserMultiFormatReader();
            }

            async function startCamera() {
                const host = String(window.location.hostname || '').toLowerCase();
                const isLocalhost = host === 'localhost' || host === '127.0.0.1';
                if (!window.isSecureContext && !isLocalhost) {
                    const message = 'Camara bloqueada: en HTTP con IP el navegador la desactiva. Usa HTTPS o localhost.';
                    setFeedback(message);
                    alert(message);
                    return;
                }
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setFeedback('Este navegador no expone getUserMedia. Usa ingreso manual.');
                    return;
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' } },
                        audio: false,
                    });
                    video.srcObject = stream;
                } catch (error) {
                    setFeedback('No se pudo acceder a la camara. Revisa permisos del navegador para este sitio.');
                    return;
                }

                if (canUseNativeDetector()) {
                    try {
                        detector = new BarcodeDetector({
                            formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e', 'qr_code'],
                        });
                        isScanning = true;
                        setFeedback('Camara iniciada (modo nativo). Apunta al codigo.');
                        requestAnimationFrame(scanLoop);
                        return;
                    } catch (error) {
                        // Continua al modo compatibilidad ZXing.
                    }
                }

                try {
                    zxingReader = await loadZxingReader();
                    setFeedback('Camara iniciada (modo compatibilidad). Apunta al codigo.');
                    zxingReader.decodeFromVideoDevice(undefined, video, async (result, err) => {
                        if (!result) {
                            return;
                        }
                        const now = Date.now();
                        const code = String(result.text || '').trim();
                        if (code && (code !== lastCode || now - lastSentAt > 1500)) {
                            lastCode = code;
                            lastSentAt = now;
                            await sendBarcode(code);
                        }
                    });
                } catch (error) {
                    setFeedback('No fue posible iniciar modo compatibilidad. Usa ingreso manual.');
                }
            }

            function stopCamera() {
                isScanning = false;
                detector = null;
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                if (zxingReader) {
                    try {
                        zxingReader.reset();
                    } catch (error) {
                        // No-op.
                    }
                    zxingReader = null;
                }
                if (video) {
                    video.srcObject = null;
                }
                setFeedback('Camara detenida.');
            }

            startBtn.addEventListener('click', startCamera);
            stopBtn.addEventListener('click', stopCamera);

            manualBtn.addEventListener('click', async () => {
                await sendBarcode(manualInput.value);
                manualInput.value = '';
                manualInput.focus();
            });

            manualInput.addEventListener('keydown', async (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    await sendBarcode(manualInput.value);
                    manualInput.value = '';
                }
            });
        })();
    </script>
</body>
</html>
