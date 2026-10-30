import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const apiTarget = env.VITE_API_TARGET || 'http://127.0.0.1:8000';
    const rootDir = import.meta.dirname;

    return {
        plugins: [
            vue(),
            tailwindcss(),
        ],
        resolve: {
            alias: {
                '@': path.resolve(rootDir, 'src'),
                '@axios': path.resolve(rootDir, 'src/plugins/axios.ts'),
            },
        },
        server: {
            port: 5174,
            proxy: {
                '/api': {
                    target: apiTarget,
                    changeOrigin: true,
                },
            },
        },
    };
});
