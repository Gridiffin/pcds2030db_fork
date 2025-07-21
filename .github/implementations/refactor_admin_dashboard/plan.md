# Refactoring Plan: Admin Dashboard

This document outlines the plan for refactoring the Admin Dashboard module, following the project's best practices.

## 1. Preparation & Analysis

- [ ] **Identify Files:**
  - **View:** `app/views/admin/dashboard/dashboard.php`
  - **Controller Logic (to be created):** `app/controllers/AdminDashboardController.php`
  - **Entry Point (to be created):** `app/views/admin/dashboard/index.php`
  - **Partials (to be created):**
    - `app/views/admin/dashboard/partials/_quick_actions.php`
    - `app/views/admin/dashboard/partials/_stats_overview.php`
    - `app/views/admin/dashboard/partials/_programs_overview.php`
    - `app/views/admin/dashboard/partials/_outcomes_overview.php`
    - `app/views/admin/dashboard/partials/_recent_submissions.php`
  - **PHP Logic (sources):**
    - `app/lib/admins/index.php`
    - `app/lib/admins/outcomes.php`
    - `app/lib/functions.php`
  - **JS:**
    - `assets/js/admin/dashboard_charts.js`
    - `assets/js/admin/dashboard.js`
    - `assets/js/period_selector.js`
  - **CSS:** (To be reviewed and modularized if necessary)
- [ ] **Map Data Flow:** The new flow will be `index.php` -> `AdminDashboardController.php` (fetches data) -> `dashboard.php` (renders data with partials).
- [ ] **Dynamic Features:** The period selector and "Refresh Data" button will be reviewed for potential AJAX implementation to improve user experience.

## 2. Refactoring Steps

### Phase 1: Controller and Logic Extraction

- [ ] **Create Controller:** Create `app/controllers/AdminDashboardController.php`.
- [ ] **Move Logic:** Move all data fetching and business logic from `dashboard.php` into the new controller. This includes database queries, session handling, and data preparation.
- [ ] **Data Structure:** The controller will return a structured array or object containing all the data needed by the view.

### Phase 2: View Modularization

- [ ] **Create Entry Point:** Create `app/views/admin/dashboard/index.php` which will include the controller and pass the data to the main view.
- [ ] **Create Partials:** Break down `dashboard.php` into the partial files listed above.
- [ ] **Refactor Main View:** Modify `app/views/admin/dashboard/dashboard.php` to be a clean template that includes the partials and populates them with data from the controller.

### Phase 3: Asset & Finalization

- [ ] **Review Assets:** Ensure CSS and JS are modular and correctly loaded. Create `assets/css/admin/dashboard.css` if needed for dashboard-specific styles.
- [ ] **Cleanup:** Remove any redundant code from the old `dashboard.php`.
- [ ] **Testing:** Manually verify that the refactored dashboard looks and functions identically to the original.

This plan will be updated as progress is made.
