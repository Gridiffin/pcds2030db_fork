import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        rollupOptions: {
            input: {
                'login': path.resolve(__dirname, 'assets/js/login.js'),
                'admin-dashboard': path.resolve(__dirname, 'assets/js/admin/dashboard.js'),
                'agency-dashboard': path.resolve(__dirname, 'assets/js/agency/dashboard/dashboard.js'),
                'agency-initiatives': path.resolve(__dirname, 'assets/js/agency/initiatives/view.js'),
                'agency-programs-view': 'assets/js/agency/view_programs.js',
                'agency-programs-create': 'assets/js/agency/programs/create.js',
                'agency-programs-add-submission': 'assets/js/agency/programs/add_submission.js',
                'agency-programs-edit': 'assets/js/agency/programs/edit_program.js',
                'agency-reports': 'assets/js/agency/reports/view_reports.js',
                'agency-outcomes': 'assets/js/agency/outcomes/outcomes.js',
                'agency-notifications': 'assets/js/agency/users/notifications.js',
                // Add more entry points as needed
            },
            output: {
                entryFileNames: 'js/[name].bundle.js',
                assetFileNames: 'css/[name].bundle.css',
            }
        },
        outDir: 'dist',
        emptyOutDir: true,
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