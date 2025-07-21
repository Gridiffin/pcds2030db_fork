# Refactoring Plan: Admin Programs Page

**Date:** 2024-07-25

**Objective:** Refactor the `app/views/admin/programs/programs.php` file to align with project best practices, improving modularity, maintainability, and separation of concerns.

---

## 📝 To-Do Checklist

### 1. **Preparation & Analysis**

- [x] **Analyze Existing JavaScript:** Identify the JS files responsible for filtering, sorting, and actions on the programs page.
- [x] **Analyze Existing CSS:** Review CSS to determine if any styles are specific to this page and can be moved to a dedicated module file.

### 2. **Directory & File Structure**

- [x] **Create Partials Directory:** `app/views/admin/programs/partials/`
- [x] **Create JavaScript Directory & File:** `assets/js/admin/programs/programs.js`
- [x] **Create CSS Directory & File:** `assets/css/admin/programs/programs.css`

### 3. **Refactor PHP View & Logic**

- [x] **Isolate Business Logic:** Move the PHP data-fetching logic from the top of `programs.php` into a more appropriate location (e.g., a controller or handler file).
- [x] **Create Partials:**
  - [x] `_draft_programs_table.php`: For the "Draft Submissions" card and table.
  - [x] `_finalized_programs_table.php`: For the "Finalized Submissions" card and table.
  - [x] `_template_programs_table.php`: For the "Program Templates" (without submissions) card and table.
  - [x] `_modals.php`: For the delete confirmation modal.
- [x] **Clean up `programs.php`:** The main view file should now be a simple layout that includes the necessary data and partials.

### 4. **Modularize JavaScript**

- [x] **Consolidate JS Logic:** Move all relevant JavaScript from existing files (`programs_delete.js`, `table_sorting.js`, and any other scattered scripts) into `assets/js/admin/programs/programs.js`.
- [x] **Update Script Includes:** Modify `programs.php` (or the layout footer) to remove the old script tags and add a single reference to the new `programs.js` file.
- [x] **Refactor JS:** Organize the consolidated JavaScript with clear functions and event listeners.

### 5. **Modularize CSS**

- [x] **Isolate Styles:** Move any CSS specific to the programs page into `assets/css/admin/programs/programs.css`.
- [x] **Import New CSS:** Add an `@import` statement for the new CSS file into a higher-level CSS file like `assets/css/base.css` or `assets/css/admin.css`.

### 6. **Final Validation**

- [x] **Test Functionality:** Thoroughly test the refactored page:
  - [x] Do all three tables load correctly?
  - [x] Does searching and filtering work for each table?
  - [x] Does table sorting work?
  - [x] Does the "More Actions" button work?
  - [x] Does the "Delete" button and confirmation modal work?
- [x] **Code Review:** Ensure the new structure adheres to the standards in `docs/project_structure_best_practices.md`.
- [x] **Update Documentation:** Mark all tasks in this plan as complete.
