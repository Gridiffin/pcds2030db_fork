import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        rollupOptions: {
            input: {
                // Login Module (already correct)
                'login': path.resolve(__dirname, 'assets/js/login.js'),

                // Admin Modules
                'admin-common': path.resolve(__dirname, 'assets/js/admin/admin-common.js'),
                'admin-manage-initiatives': path.resolve(__dirname, 'assets/js/admin/manage-initiatives.js'),
                'admin-reports': path.resolve(__dirname, 'assets/js/admin/reports.js'),
                'admin-programs': path.resolve(__dirname, 'assets/js/admin/programs.js'),

                // Agency Core
                'agency-dashboard': path.resolve(__dirname, 'assets/js/agency/dashboard/dashboard.js'),

                // Agency Programs Module (Individual Pages)
                'agency-view-programs': path.resolve(__dirname, 'assets/js/agency/view_programs.js'),
                'agency-create-program': path.resolve(__dirname, 'assets/js/agency/programs/create.js'),
                'agency-edit-program': path.resolve(__dirname, 'assets/js/agency/programs/edit_program.js'),
                'agency-add-submission': path.resolve(__dirname, 'assets/js/agency/programs/add_submission.js'),
                'agency-program-details': path.resolve(__dirname, 'assets/js/agency/enhanced_program_details.js'),
                'agency-edit-submission': path.resolve(__dirname, 'assets/js/agency/edit_submission.js'),
                'agency-view-submissions': path.resolve(__dirname, 'assets/js/agency/programs/view_submissions.js'),
                'agency-view-other-programs': path.resolve(__dirname, 'assets/js/agency/programs/view_other_agency_programs.js'),

                // Agency Other Modules
                'agency-initiatives': path.resolve(__dirname, 'assets/js/agency/initiatives/view.js'), // Note: using view.js as entry
                'agency-view-initiative': path.resolve(__dirname, 'assets/js/agency/initiatives/view_initiative.js'),
                'outcomes': path.resolve(__dirname, 'assets/js/agency/outcomes/outcomes.js'),
                'agency-submit-outcomes': path.resolve(__dirname, 'assets/js/agency/outcomes/submit_outcomes.js'),
                'agency-reports': path.resolve(__dirname, 'assets/js/agency/reports/reports.js'),
                'agency-notifications': path.resolve(__dirname, 'assets/js/agency/users/notifications.js'),
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