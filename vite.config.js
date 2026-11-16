import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import ViteRestart from 'vite-plugin-restart';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig(({ command }) => ({
  base: command === 'serve' ? '' : '/dist/',
  publicDir: false,
  plugins: [
    vue(),
    ViteRestart({ reload: ['./templates/**/*'] }),
  ],
  resolve: {
    alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) },
  },
  build: {
    manifest: true,
    outDir: './web/dist/',
    emptyOutDir: true,
    rollupOptions: { input: { app: 'src/main.js' } },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    origin: 'https://hypotheca.ddev.site:5173',
    cors: { origin: /https?:\/\/([A-Za-z0-9\-.]+)?(\.ddev\.site)(?::\d+)?$/ },
  },
}));
