import { createBarcodeCamera } from './barcode-camera';

const barcodeInput = document.getElementById('product-barcode');
const openButton = document.getElementById('open-product-barcode-scanner');
const closeButton = document.getElementById('close-product-barcode-scanner');
const modal = document.getElementById('product-barcode-modal');
const video = document.getElementById('product-barcode-preview');
const status = document.getElementById('product-barcode-status');
const feedback = document.getElementById('product-barcode-feedback');
const remoteButton = document.getElementById('open-product-remote-scanner');
const remoteModal = document.getElementById('product-remote-scanner-modal');
const remoteCloseButton = document.getElementById('close-product-remote-scanner');
const remoteUrlInput = document.getElementById('product-remote-scanner-url');
const remoteCopyButton = document.getElementById('copy-product-remote-scanner-url');
const remoteLaunchLink = document.getElementById('launch-product-remote-scanner');
const remoteStatus = document.getElementById('product-remote-scanner-status');

let camera = null;
let detected = false;
let remoteToken = '';
let remoteUrl = '';
let remotePollTimer = null;
let remotePolling = false;
let remoteModalOpen = false;
const remoteStorageKey = 'products:remote-barcode-scanner';

function setStatus(message) {
    status.textContent = message;
}

function stopScanner() {
    camera?.stop();
    camera = null;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function startScanner() {
    if (!barcodeInput || !modal || !video) {
        return;
    }

    if (!window.isSecureContext && !['localhost', '127.0.0.1'].includes(window.location.hostname)) {
        feedback.textContent = 'La camara requiere HTTPS o localhost.';
        return;
    }

    detected = false;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setStatus('Iniciando camara trasera...');

    try {
        camera = createBarcodeCamera({
            video,
            onStatus: setStatus,
            onDetected: barcode => {
                if (detected) {
                    return;
                }
                detected = true;
                applyBarcode(barcode, 'Codigo detectado');
                stopScanner();
            },
        });
        await camera.start();
    } catch (error) {
        setStatus(error.message || 'No fue posible iniciar la camara. Revisa los permisos.');
    }
}

function applyBarcode(barcode, source) {
    const value = String(barcode || '').trim();
    if (!value) {
        return;
    }

    barcodeInput.value = value;
    barcodeInput.setAttribute('value', value);
    barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
    barcodeInput.dispatchEvent(new Event('change', { bubbles: true }));
    feedback.textContent = `${source}: ${value}`;

    if (navigator.vibrate) {
        navigator.vibrate(80);
    }
}

function stopRemotePolling() {
    if (remotePollTimer) {
        clearTimeout(remotePollTimer);
        remotePollTimer = null;
    }
}

async function pollRemoteScanner() {
    if (!remoteToken || remotePolling) {
        return;
    }

    remotePolling = true;

    try {
        const response = await fetch(`${remoteModal.dataset.pollBaseUrl}/${remoteToken}/poll`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            remoteStatus.textContent = response.status === 404
                ? 'La sesion expiro. Cierra y genera un enlace nuevo.'
                : 'No se pudo consultar el lector. Reintentando...';
            return;
        }

        const payload = await response.json();
        const events = Array.isArray(payload.events) ? payload.events : [];
        const event = events.length > 0 ? events[events.length - 1] : null;

        if (event?.barcode) {
            applyBarcode(event.barcode, 'Codigo recibido del celular');
            remoteStatus.textContent = `Codigo reemplazado por: ${event.barcode}. Puedes escanear otro para cambiarlo nuevamente.`;
        }
    } catch {
        remoteStatus.textContent = 'Sin conexion con el lector. Reintentando...';
    } finally {
        remotePolling = false;
        if (remoteToken) {
            remotePollTimer = window.setTimeout(pollRemoteScanner, 1000);
        }
    }
}

async function openRemoteScanner() {
    stopRemotePolling();
    remoteModalOpen = true;
    remoteModal.classList.remove('hidden');
    remoteModal.classList.add('flex');

    if (remoteToken && remoteUrl) {
        remoteUrlInput.value = remoteUrl;
        remoteLaunchLink.href = remoteUrl;
        remoteStatus.textContent = 'Esperando un codigo desde el celular...';
        pollRemoteScanner();
        return;
    }

    remoteUrlInput.value = 'Generando enlace...';
    remoteLaunchLink.href = '#';
    remoteStatus.textContent = 'Preparando lector remoto...';

    try {
        const response = await fetch(remoteModal.dataset.sessionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': remoteModal.dataset.csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({}),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();
        remoteToken = payload.token || '';
        remoteUrl = payload.remote_url || '';
        localStorage.setItem(remoteStorageKey, JSON.stringify({
            token: remoteToken,
            remote_url: remoteUrl,
            expires_at: payload.expires_at || '',
        }));
        remoteUrlInput.value = remoteUrl;
        remoteLaunchLink.href = remoteUrl || '#';
        remoteStatus.textContent = 'Esperando un codigo desde el celular...';
        pollRemoteScanner();
    } catch {
        remoteStatus.textContent = 'No fue posible crear el enlace remoto.';
    }
}

function closeRemoteScanner() {
    stopRemotePolling();
    remoteModalOpen = false;
    remoteModal.classList.add('hidden');
    remoteModal.classList.remove('flex');
    if (remoteToken) {
        pollRemoteScanner();
    }
}

function resumeRemotePolling() {
    if (!remoteToken) {
        return;
    }

    stopRemotePolling();
    pollRemoteScanner();
}

function clearRemoteSession() {
    stopRemotePolling();
    remoteToken = '';
    remoteUrl = '';
    localStorage.removeItem(remoteStorageKey);
}

function restoreRemoteSession() {
    try {
        const stored = JSON.parse(localStorage.getItem(remoteStorageKey) || 'null');
        const expiresAt = Date.parse(stored?.expires_at || '');

        if (!stored?.token || !Number.isFinite(expiresAt) || expiresAt <= Date.now()) {
            clearRemoteSession();
            return;
        }

        remoteToken = stored.token;
        remoteUrl = stored.remote_url || '';
        pollRemoteScanner();
    } catch {
        clearRemoteSession();
    }
}

async function copyRemoteUrl() {
    if (!remoteUrl) {
        return;
    }

    try {
        await navigator.clipboard.writeText(remoteUrl);
        remoteStatus.textContent = 'Enlace copiado.';
    } catch {
        remoteStatus.textContent = 'No se pudo copiar el enlace.';
    }
}

openButton?.addEventListener('click', startScanner);
closeButton?.addEventListener('click', stopScanner);
modal?.addEventListener('click', event => {
    if (event.target === modal) {
        stopScanner();
    }
});
remoteButton?.addEventListener('click', openRemoteScanner);
remoteCloseButton?.addEventListener('click', closeRemoteScanner);
remoteCopyButton?.addEventListener('click', copyRemoteUrl);
remoteModal?.addEventListener('click', event => {
    if (event.target === remoteModal) {
        closeRemoteScanner();
    }
});
window.addEventListener('focus', resumeRemotePolling);
window.addEventListener('online', resumeRemotePolling);
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        resumeRemotePolling();
    }
});
window.addEventListener('pagehide', () => {
    stopScanner();
    stopRemotePolling();
});

restoreRemoteSession();
