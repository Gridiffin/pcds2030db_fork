import { defineConfig } from 'vite';
import manifest from 'vite-plugin-manifest';
import copy from 'rollup-plugin-copy';

export default defineConfig({
  plugins: [
    manifest(),
    copy({
      targets: [
        { src: 'assets/images/*', dest: 'public/images' },
        { src: 'assets/fonts/*', dest: 'public/fonts' }
      ],
      hook: 'writeBundle'
    })
  ],
  build: {
    outDir: 'public/dist',
    manifest: true,
    rollupOptions: {
      input: {
        main: 'frontend/main.js',
      },
    },
  },
  server: {
    proxy: {
      '/': {
        target: 'http://localhost:8080', // Your local PHP server
        changeOrigin: true,
      },
    },
  },
}); 