import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

function resolveBuildBase(assetUrl) {
    if (!assetUrl) {
        return '/build/';
    }

    try {
        const { pathname } = new URL(assetUrl);
        const normalizedPath = pathname.endsWith('/') ? pathname : `${pathname}/`;

        return `${normalizedPath}build/`;
    } catch {
        return '/build/';
    }
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        base: resolveBuildBase(env.ASSET_URL),
        server: {
            host: '127.0.0.1',
            port: 5173,
            strictPort: true,
            hmr: {
                host: '127.0.0.1',
                protocol: 'ws',
                port: 5173,
            },
        },
        plugins: [
            laravel({
                input: ['resources/css/vendor.css', 'resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
    };
});
