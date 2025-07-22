import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        // General
        'login': 'assets/js/login.js',
        'main': 'assets/js/main.js',

        // Admin
        'admin-dashboard': 'assets/js/admin/dashboard.js',
        'admin-manage-initiatives': 'assets/js/admin/initiatives/manageInitiatives.js',
        'admin-manage-outcomes': 'assets/js/admin/manage_outcomes.js',
        'admin-edit-kpi': 'assets/js/admin/edit_kpi.js',

        // Agency
        'agency-dashboard': 'assets/js/agency/dashboard/dashboard.js',
        'agency-initiatives': 'assets/js/agency/initiatives.js',
        'agency-view-initiative': 'assets/js/agency/initiative-view.js',
        'agency-view-programs': 'assets/js/agency/view_programs.js',
        'agency-edit-program': 'assets/js/agency/programs/edit_program.js',
        'agency-reports': 'assets/js/agency/reports/reports.js',
        'notifications': 'assets/js/agency/users/notifications.js',
      },
      output: {
        entryFileNames: 'js/[name].bundle.js',
        assetFileNames: 'css/[name].bundle.css',
        chunkFileNames: 'js/[name]-[hash].js',
      }
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