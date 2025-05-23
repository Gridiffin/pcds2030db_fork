# Project Restructuring - Phase 2

## URL Structure Update Plan

After completing the initial phase of restructuring (moving files to the `app/` directory), the next phase should focus on ensuring all internal links use proper absolute URLs to maintain consistency and prevent broken links when navigating between different sections of the application.

### Steps Completed in Phase 1:

1. Moved core application logic to `app/` directory
2. Updated include paths in entry point files
3. Updated configuration to properly define `ROOT_PATH` and `PROJECT_ROOT_PATH`
4. Updated redirections in entry point files to use the new structure
5. Fixed asset links in entry files to use `APP_URL` consistently
6. Added `view_url()` helper function in `config.php` to generate consistent view URLs

### To Be Implemented in Phase 2:

1. **Update Internal View Links**
   - Replace all relative links in view files with the `view_url()` function
   - Example: `<a href="assign_programs.php">` should become `<a href="<?php echo view_url('admin', 'assign_programs.php'); ?>">`

2. **Update AJAX and API Endpoints**
   - Create a similar `api_url()` function for API endpoints
   - Update all AJAX calls to use absolute URLs

3. **Asset References**
   - Ensure all CSS, JS, and image references use `APP_URL`
   - Create a dedicated `asset_url()` function for consistency

4. **Form Actions**
   - Update all form actions to use absolute URLs
   - Example: `<form action="process_form.php">` to `<form action="<?php echo APP_URL; ?>/app/handlers/process_form.php">`

5. **Testing Strategy**
   - Test navigation from all entry points
   - Test form submissions
   - Test AJAX calls and API endpoints
   - Test with different browser cache settings

### Implementation Guidelines:

1. Use the helper functions consistently across all files
2. Consider implementing a simple routing system for future scalability
3. Document URL patterns for future developers
4. Add automated tests to verify link integrity

This phased approach ensures that we maintain a working application throughout the restructuring process while systematically improving the codebase organization and maintainability.
