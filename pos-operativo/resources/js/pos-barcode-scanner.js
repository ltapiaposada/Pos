import { createBarcodeCamera } from './barcode-camera';

let activeCamera = null;

window.PosBarcodeCamera = {
    async start(video, onDetected, onStatus) {
        this.stop();
        activeCamera = createBarcodeCamera({ video, onDetected, onStatus });
        await activeCamera.start();
    },
    stop() {
        activeCamera?.stop();
        activeCamera = null;
    },
};
