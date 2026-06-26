import {
    BarcodeFormat,
    BrowserBarcodeReader,
    DecodeHintType,
    NotFoundException,
} from '@zxing/library';

export function createBarcodeCamera({ video, onDetected, onStatus }) {
    let reader = null;
    let stopped = true;

    const setStatus = message => {
        if (typeof onStatus === 'function') {
            onStatus(message);
        }
    };

    async function start() {
        if (!video) {
            throw new Error('No se encontro el visor de la camara.');
        }

        const host = String(window.location.hostname || '').toLowerCase();
        const localHost = host === 'localhost' || host === '127.0.0.1';
        if (!window.isSecureContext && !localHost) {
            throw new Error('La camara requiere HTTPS o localhost.');
        }
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Este navegador no permite usar la camara.');
        }

        stop();
        stopped = false;
        setStatus('Iniciando camara trasera...');

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
        await reader.decodeFromConstraints(
            {
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
                audio: false,
            },
            video,
            (result, error) => {
                if (stopped) {
                    return;
                }
                if (result) {
                    const value = String(result.getText?.() || result.text || '').trim();
                    if (value && typeof onDetected === 'function') {
                        onDetected(value);
                    }
                    return;
                }
                if (error && !(error instanceof NotFoundException)) {
                    setStatus('Buscando codigo. Manten la barra quieta y con buena luz.');
                }
            },
        );

        setStatus('Camara activa. Centra el codigo de barras.');
    }

    function stop() {
        stopped = true;
        if (reader) {
            try {
                reader.reset();
            } catch {
                // The browser may already have stopped the stream.
            }
            reader = null;
        }
        if (video?.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
    }

    return { start, stop };
}
