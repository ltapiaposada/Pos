import {
    BarcodeFormat,
    BrowserBarcodeReader,
    DecodeHintType,
    NotFoundException,
} from '@zxing/library';

const body = document.body;
const scanUrl = body.dataset.scanUrl || '';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const video = document.getElementById('camera-preview');
const startBtn = document.getElementById('start-camera');
const stopBtn = document.getElementById('stop-camera');
const feedback = document.getElementById('feedback');
const diagnostics = document.getElementById('scanner-diagnostics');

let reader = null;
let lastCode = '';
let lastSentAt = 0;
let sending = false;
let nativeDetector = null;
let nativeScanTimer = null;

function setFeedback(message) {
    feedback.textContent = message;
}

function setDiagnostics(message) {
    diagnostics.textContent = message;
}

function errorDescription(error) {
    if (!error) {
        return 'Error desconocido';
    }

    return [error.name, error.message].filter(Boolean).join(': ') || String(error);
}

async function sendBarcode(rawCode) {
    const barcode = String(rawCode || '').trim();
    if (!barcode || sending) {
        return;
    }

    sending = true;

    try {
        const response = await fetch(scanUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ barcode }),
        });

        if (!response.ok) {
            const payload = await response.json().catch(() => ({}));
            throw new Error(payload.message || `HTTP ${response.status}`);
        }

        if (navigator.vibrate) {
            navigator.vibrate(80);
        }

        stopCamera(false);
        setFeedback(`Codigo enviado: ${barcode}`);
        setDiagnostics('Envio confirmado. Pulsa "Escanear otro codigo" para reemplazarlo.');
    } catch (error) {
        setFeedback('No se pudo enviar el codigo al POS.');
        setDiagnostics(`Error de envio: ${errorDescription(error)}`);
    } finally {
        sending = false;
    }
}

async function processResult(result) {
    const value = typeof result?.getText === 'function'
        ? result.getText()
        : (result?.rawValue || result?.text);
    const code = String(value || '').trim();
    const now = Date.now();

    if (!code || (code === lastCode && now - lastSentAt <= 5000)) {
        return;
    }

    lastCode = code;
    lastSentAt = now;
    setFeedback(`Codigo detectado: ${code}`);
    await sendBarcode(code);
}

async function startNativeDetector() {
    if (!('BarcodeDetector' in window)) {
        return false;
    }

    const desiredFormats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'itf'];
    const supportedFormats = typeof BarcodeDetector.getSupportedFormats === 'function'
        ? await BarcodeDetector.getSupportedFormats()
        : desiredFormats;
    const formats = desiredFormats.filter(format => supportedFormats.includes(format));

    if (formats.length === 0) {
        return false;
    }

    const stream = await navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 1280 },
            height: { ideal: 720 },
        },
        audio: false,
    });

    video.srcObject = stream;
    await video.play();
    nativeDetector = new BarcodeDetector({ formats });

    const detect = async () => {
        if (!nativeDetector || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
            return;
        }

        try {
            const results = await nativeDetector.detect(video);
            if (results.length > 0) {
                await processResult(results[0]);
            }
        } catch (error) {
            setDiagnostics(`Lector nativo: ${errorDescription(error)}`);
        }
    };

    nativeScanTimer = window.setInterval(detect, 180);
    await optimizeCamera();
    setDiagnostics(`${diagnostics.textContent} Lector rapido activo.`);

    return true;
}

async function optimizeCamera() {
    const track = video?.srcObject?.getVideoTracks?.()[0];
    if (!track) {
        return;
    }

    const capabilities = typeof track.getCapabilities === 'function' ? track.getCapabilities() : {};
    const advanced = [];

    if (Array.isArray(capabilities.focusMode) && capabilities.focusMode.includes('continuous')) {
        advanced.push({ focusMode: 'continuous' });
    }

    if (advanced.length > 0) {
        await track.applyConstraints({ advanced }).catch(() => {});
    }

    const settings = typeof track.getSettings === 'function' ? track.getSettings() : {};
    setDiagnostics(
        `Camara activa: ${settings.width || '?'}x${settings.height || '?'}`
        + `${settings.focusMode ? `, enfoque ${settings.focusMode}` : ''}. Buscando codigo...`,
    );
}

async function startCamera() {
    const host = String(window.location.hostname || '').toLowerCase();
    const isLocalhost = host === 'localhost' || host === '127.0.0.1';

    if (!window.isSecureContext && !isLocalhost) {
        setFeedback('La camara requiere una direccion HTTPS.');
        setDiagnostics(`Contexto no seguro: ${window.location.protocol}`);
        return;
    }

    if (!navigator.mediaDevices?.getUserMedia) {
        setFeedback('Este navegador no permite usar la camara.');
        setDiagnostics('API getUserMedia no disponible.');
        return;
    }

    stopCamera(false);
    lastCode = '';
    lastSentAt = 0;
    startBtn.disabled = true;
    setFeedback('Solicitando permiso para la camara...');
    setDiagnostics('Inicializando lector ZXing local.');

    try {
        try {
            if (await startNativeDetector()) {
                setFeedback('Camara activa. Coloca la barra completa dentro del recuadro.');
                return;
            }
        } catch (nativeError) {
            if (video?.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
            nativeDetector = null;
            setDiagnostics(`Usando lector compatible: ${errorDescription(nativeError)}`);
        }

        const hints = new Map();
        hints.set(DecodeHintType.POSSIBLE_FORMATS, [
            BarcodeFormat.EAN_13,
            BarcodeFormat.EAN_8,
            BarcodeFormat.UPC_A,
            BarcodeFormat.UPC_E,
            BarcodeFormat.CODE_128,
            BarcodeFormat.CODE_39,
            BarcodeFormat.CODE_93,
            BarcodeFormat.ITF,
            BarcodeFormat.CODABAR,
        ]);

        reader = new BrowserBarcodeReader(250, hints);
        setDiagnostics('Usando la camara trasera predeterminada.');
        const videoConstraints = {
            facingMode: { ideal: 'environment' },
            width: { ideal: 1280 },
            height: { ideal: 720 },
        };

        await reader.decodeFromConstraints(
            { video: videoConstraints, audio: false },
            video,
            (result, error) => {
                if (result) {
                    processResult(result).catch(sendError => {
                        setDiagnostics(`Lectura: ${errorDescription(sendError)}`);
                    });
                    return;
                }

                if (error && !(error instanceof NotFoundException)) {
                    setDiagnostics(`Lector: ${errorDescription(error)}`);
                }
            },
        );

        await optimizeCamera();
        setFeedback('Camara activa. Acerca el codigo y mantenlo dentro del recuadro.');
    } catch (error) {
        reader = null;
        setFeedback('No fue posible iniciar el lector.');
        setDiagnostics(errorDescription(error));
    } finally {
        startBtn.disabled = false;
    }
}

function stopCamera(showMessage = true) {
    if (nativeScanTimer) {
        clearInterval(nativeScanTimer);
        nativeScanTimer = null;
    }
    nativeDetector = null;

    if (reader) {
        try {
            reader.reset();
        } catch {
            // The stream may already be closed by the browser.
        }
        reader = null;
    }

    if (video?.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }

    if (showMessage) {
        setFeedback('Camara detenida.');
        setDiagnostics('Lector detenido.');
    }
}

startBtn.addEventListener('click', startCamera);
stopBtn.addEventListener('click', () => stopCamera());

window.addEventListener('pagehide', () => stopCamera(false));
