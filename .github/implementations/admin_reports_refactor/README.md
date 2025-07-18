# Admin Reports Refactor Implementation Plan

## Overview

This document tracks the refactor of `app/views/admin/reports/generate_reports.php` to align with project best practices for modularity, maintainability, and separation of concerns.

---

## Goals

- Move all business logic and helper functions out of the view file into a dedicated library (`app/lib/admin_reports.php`).
- Ensure the view is focused on rendering and data presentation only.
- Remove any inline JS/CSS and ensure all assets are loaded via the `assets/` directory.
- If the view remains too large, split it into partials for maintainability.
- Document each step and mark progress here.

---

## To-Do List

- [x] 1. Move all helper functions (`getReportingPeriods`, `getSectors`, `getRecentReports`, `shouldShowNewBadge`, `formatPeriod`) to `app/lib/admin_reports.php`.
- [x] 2. Update `generate_reports.php` to require the new library and remove in-file function definitions.
- [x] 3. Check for and move any inline JS/CSS to appropriate asset files.
- [x] 4. If the file is still too large, split HTML into partials (e.g., recent reports, generate form, modals).
- [ ] 5. Update this document after each major step to reflect progress and mark completed tasks.

---

## Rationale

- Follows [project_structure_best_practices.md](../../../../docs/project_structure_best_practices.md) for separation of concerns and modularity.
- Improves maintainability, testability, and scalability.
- Prepares the codebase for future enhancements and easier debugging.

---

## Progress Log

- **[DATE]**: Plan created, initial to-do list established.
- **[DATE]**: Moved all helper functions to `app/lib/admin_reports.php` and updated `generate_reports.php` to use the new library.
- **[DATE]**: Audited for inline JS/CSS; all assets are loaded via the assets directory, only config object remains inline (acceptable).
- **[DATE]**: Split main HTML into partials (`recent_reports_dashboard.php`, `generate_report_form.php`, `delete_report_modal.php`) and updated the main view to use them.
