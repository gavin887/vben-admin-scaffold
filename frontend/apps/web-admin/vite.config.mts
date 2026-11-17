import { defineConfig } from '@vben/vite-config';

export default defineConfig(async () => ({
  application: { archiver: false },
  vite: {
    build: { emptyOutDir: true, outDir: 'dist' },
    server: {
      proxy: {
        '/admin': {
          changeOrigin: true,
          target: 'http://127.0.0.1:8000',
          rewrite: (path) => path.replace(/^\/admin/, '/index.php/admin'),
        },
        '/uploads': { target: 'http://127.0.0.1:8000', changeOrigin: true },
      },
    },
  },
}));
