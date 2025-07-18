# Admin System Settings Refactor Plan

## Overview

This document tracks the refactor of `app/views/admin/settings/system_settings.php` to follow project best practices: modularization, separation of concerns, and maintainability.

---

## Goals

- Move business logic to a controller (`app/controllers/AdminSettingsController.php`).
- Keep the view focused on rendering, using partials for alerts and repeated UI.
- Move inline JS to `assets/js/admin/system_settings.js`.
- Ensure all CSS is referenced via assets, not inline.
- Prepare for scalable addition of more settings.
- Add CSRF protection and robust error handling.
- Update this plan after each step.

---

## Checklist

- [x] 1. Create `AdminSettingsController.php` for settings logic
- [x] 2. Refactor `system_settings.php` to use controller and partials
- [x] 3. Move inline JS to `assets/js/admin/system_settings.js`
- [ ] 4. Use/extend `error_alert.php` for alert rendering
- [ ] 5. Ensure all CSS is referenced via assets
- [ ] 6. Add CSRF protection to the form
- [ ] 7. Test the refactored page
- [ ] 8. Update this plan after each step

---

## Progress Log

- **18/07/2025**: Plan created, initial checklist written.
- **18/07/2025**: Step 1 complete — Created `AdminSettingsController.php` for settings logic.
- **18/07/2025**: Step 2 complete — Refactored `system_settings.php` to use controller and partials.
- **18/07/2025**: Step 3 complete — Moved inline JS to `assets/js/admin/system_settings.js`.
