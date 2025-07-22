# Refactor Agency Add Submission Page

**Date:** 2025-07-22

**Module:** `agency/programs/add_submission`

## 1. Preparation & Analysis

- [X] **Identify Files:**
  - **View:** `app/views/agency/programs/add_submission.php`
  - **JS:** `assets/js/agency/add_submission.js` (to be moved)
  - **CSS:** None (will be created for consistency)
  - **Layout:** Uses old `header.php`/`footer.php` pattern.
- [X] **Map Data Flow:**
  - PHP file handles all logic: DB queries, form processing, session management.
  - Data is passed to the view and to an inline JS script.
- [X] **Identify Dynamic Features:**
  - Client-side form interactions (add targets, add attachments).
  - Server-side form submission and validation.

## 2. Refactoring Plan

### 2.1. Directory & File Structure

- [ ] **Main View:** `app/views/agency/programs/add_submission.php`
  - Will be refactored to handle only PHP logic and `base.php` layout integration.
- [ ] **Content Partial:** `app/views/agency/programs/partials/add_submission_content.php` (New)
  - Will contain all HTML markup.
- [ ] **CSS:** `assets/css/agency/programs/add_submission.css` (New)
  - Will contain module-specific styles and the recurring navbar offset fix.
- [ ] **JS:** `assets/js/agency/programs/add_submission.js` (New Location)
  - Move from `assets/js/agency/` to `assets/js/agency/programs/`.
  - Convert to an ES module.
  - Import its corresponding CSS.

### 2.2. PHP Logic & Data Flow

- [ ] **`add_submission.php` (Main View):**
  - Correct `PROJECT_ROOT_PATH` definition to use four `dirname()` calls.
  - Keep all existing PHP logic (data fetching, form processing).
  - Remove all HTML and old layout `require` statements.
  - Set `$pageTitle`, `$bodyClass`, `$cssBundle`, `$jsBundle`, and `$contentFile`.
  - Include `base.php` at the end.
- [ ] **`add_submission_content.php` (Partial):**
  - Move all HTML from the original file here.
  - Pass PHP data (e.g., `$program_id`) to JavaScript via `data-*` attributes on the main form element instead of `window` variables.

### 2.3. Asset Management (Vite)

- [ ] **`vite.config.js`:**
  - Add a new entry point: `'agency-programs-add-submission': 'assets/js/agency/programs/add_submission.js'`
- [ ] **`add_submission.js`:**
  - Add `import '../../../css/agency/programs/add_submission.css';` at the top.
  - Read `programId` and `programNumber` from `data-*` attributes on the form.
- [ ] **`add_submission.css`:**
  - Add the standard navbar padding fix to prevent content overlap.
  - ```css
    body.add-submission-page {
        padding-top: 70px;
    }
    @media (max-width: 768px) {
        body.add-submission-page {
            padding-top: 85px;
        }
    }
    ```

### 2.4. Layout & UI

- [ ] Ensure the page uses the `base.php` layout correctly.
- [ ] Verify the page header, content, and footer render in the correct order.
- [ ] Test for and fix any navbar overlap issues.

## 3. Execution Checklist

- [ ] Create `refactor_add_submission.md` implementation plan.
- [ ] Create `app/views/agency/programs/partials/add_submission_content.php`.
- [ ] Move HTML from `add_submission.php` to the new content partial.
- [ ] Refactor `add_submission.php` to use the `base.php` layout system.
- [ ] Create `assets/css/agency/programs/add_submission.css` with navbar fix.
- [ ] Move `assets/js/agency/add_submission.js` to `assets/js/agency/programs/`.
- [ ] Update the JS file to be an ES module and import CSS.
- [ ] Update `vite.config.js` with the new bundle entry.
- [ ] Run `npm run build` to generate assets.
- [ ] Test the page functionality thoroughly.
- [ ] Update `docs/bugs_tracker.md` with a summary of the refactor.

## 4. Bug Prevention

- **Path Resolution:** Correct `PROJECT_ROOT_PATH` definition to avoid `failed to open stream` errors (recurring issue).
- **Navbar Overlap:** Proactively add CSS padding to prevent the fixed navbar from covering content (recurring issue).
- **Asset Loading:** Use the Vite/`base.php` system exclusively to prevent 404s and ensure correct bundling.
- **Layout Integrity:** Use the `$contentFile` pattern to ensure the footer is positioned correctly. 