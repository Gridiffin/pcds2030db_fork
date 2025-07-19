import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        login: path.resolve(__dirname, 'assets/js/shared/login.js'),
        initiatives: path.resolve(__dirname, 'assets/js/agency/initiatives.js'),
        dashboard: path.resolve(__dirname, 'assets/js/agency/dashboard/dashboard.js'),
      },
      output: {
        entryFileNames: 'js/[name].bundle.js',
        assetFileNames: 'css/[name].bundle.css',
      },
    },
    outDir: 'dist',
    emptyOutDir: true,
  },
}); 