# Agency Cleanup To-Do List

This document consolidates the cleanup plan and results into actionable steps for the agency side cleanup process. It ensures consistency and provides a clear roadmap for execution.

---

## Step 1: Identify Cleanup Targets ✅
1. **Debug Code**: ✅
   - Remove unnecessary `console.log`, `var_dump`, `print_r`, `alert`, `die()`, `exit()`.
   - Replace alerts with proper notification systems (e.g., toast messages).

2. **Deprecated Files**: ✅
   - Remove files marked as deprecated (e.g., `view_all_sectors.php`, `ajax/sectors_data.php`).
   - Ensure no dependencies are broken.

3. **TODO/FIXME Comments**: ✅
   - Review and address actionable items.
   - Remove outdated or irrelevant comments.

4. **Unused Code**: ✅
   - Detect and remove unused functions, variables, or imports.
   - Simplify redundant logic where applicable.

---

## Step 2: Document Findings ✅
1. Use `agency_cleanup_results.md` to track:
   - Debug code instances
   - Deprecated files
   - TODO/FIXME comments
   - Unused code

2. Categorize findings by module or type (e.g., JavaScript, PHP, assets).
3. Mark each item with a status (e.g., Pending, In Progress, Completed).

---

## Step 3: Execute Cleanup ✅
1. **Remove Debug Code**: ✅
   - Clean `assets/js/agency/view_programs.js`.
   - Clean `assets/js/agency/users/` modules.
   - Clean `assets/js/agency/view-programs/` modules.
   - Clean `assets/js/agency/reports/` modules.
   - Clean `assets/js/agency/outcomes/` modules.
   - Clean `assets/js/agency/programs/` modules.
   - Clean `app/views/agency/initiatives/partials/status_grid.php`.
   - Clean `app/views/agency/initiatives/partials/initiatives_table.php`.
   - Clean `app/views/agency/dashboard/dashboard_content.php`.

2. **Remove Deprecated Files**: ✅
   - Delete `app/views/agency/sectors/view_all_sectors.php`.
   - Delete `app/views/agency/sectors/ajax/sectors_data.php`.
   - Remove entire `app/views/agency/sectors/` directory if empty.

3. **Address TODO/FIXME Comments**: ✅
   - Implement actionable items.
   - Remove unnecessary comments.
   - Update documentation where needed.

---

## Step 4: Validate Changes (Next Step)
1. **Testing**:
   - Run unit tests and integration tests.
   - Verify no functionality is broken.

2. **Manual Verification**:
   - Test critical features (e.g., dashboard, CRUD operations, reports).
   - Check for JavaScript errors in the browser console.

---

## Step 5: Update Documentation ✅
1. Update relevant documentation (e.g., `system_context.md`, `refactoring_summary.md`).
2. Reflect removed or updated components in `agency_cleanup_results.md`.
3. Archive deprecated files if necessary.

---

## Step 6: Track Progress ✅
1. Use `agency_cleanup_results.md` to monitor cleanup completion.
2. Regularly update the results document with statuses.
3. Share progress with the team for transparency.

---

*Last Updated: July 23, 2025*
