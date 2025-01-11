import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig(( { command }) => {
    const config = {
            plugins: [
            laravel({
                input: 'resources/js/app.js',
                ssr: 'resources/js/ssr.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ]
    };

    // Entwicklungsspezifische Konfiguration
    if (command !== 'build') {
        config.server = {
            hmr: {
                host: 'localhost',
                protocol: 'ws'
            },
            watch: {
                usePolling: true
            },
            host: true,
            strictPort: true,
            port: 5173,
        };
    }

    // Produktionsspezifische Optimierungen
    if (command === 'build') {
        config.build = {
            // Chunks in kleinere Dateien aufteilen
            chunkSizeWarningLimit: 1000,
            rollupOptions: {
                output: {
                    manualChunks: {
                        vendor: ['vue', '@inertiajs/vue3'],
                    },
                },
            },
            // Source Maps in Produktion deaktivieren
            sourcemap: false,
        };
    }

    return config;
});
