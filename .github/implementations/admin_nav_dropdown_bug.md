## Admin Navigation Dropdown Bug on manage_outcomes.php

**Last Updated:** May 26, 2025

### Problem Description:
The main administration navigation dropdown menus ("Programs", "Users", "Settings"), which are part of `admin_nav.php`, become unclickable and non-functional specifically when the user is on the `manage_outcomes.php` page. These dropdowns work correctly on other admin pages, such as `edit_outcome.php`.

### Investigation Steps & Findings:

1.  **`admin_nav.php` Active Class Logic:**
    *   The logic for determining the `active` class on navigation items in `admin_nav.php` was updated using `$_SERVER['REQUEST_URI']` and `strpos()`.
    *   This was ruled out as the cause of the dropdown malfunction.

2.  **Initial JavaScript Error in `manage_outcomes.php` (Inline Script near bottom):**
    *   An error "metricEditorContainer not found" was initially present due to `document.getElementById` calls for elements that might not exist.
    *   **Action:** Added null checks for `getElementById` calls for `refreshPage`, `createMetricBtn`, `sector_id`, and `period_id` in the inline script.
    *   **Result:** This specific JS error was resolved, but the navigation dropdowns remained non-functional.

3.  **`Chart.js` Script in `manage_outcomes.php`:**
    *   The page `manage_outcomes.php` includes `<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>`.
    *   A console error "Source map error: request failed with status 404 Resource URL: https://cdn.jsdelivr.net/npm/chart.js Source Map URL: chart.umd.js.map" was observed. This is generally a harmless developer warning and unlikely to be the cause.
    *   **Action (Attempt 1):** Temporarily commented out the `Chart.js` script include.
    *   **Result (Attempt 1):** Did not resolve the dropdown issue. The script was restored.
    *   **Action (Attempt 2 - Current State):** The `Chart.js` script include is currently commented out in `manage_outcomes.php`.
    *   **Result (Attempt 2):** Did not resolve the dropdown issue.

4.  **Bootstrap JavaScript Initialization:**
    *   `app/views/layouts/footer.php` correctly includes `bootstrap.bundle.min.js` (v5.2.3) from a CDN.
    *   `assets/js/utilities/dropdown_init.js` is loaded via `footer.php` and contains code to initialize Bootstrap dropdowns:
        ```javascript
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown-toggle');
            dropdowns.forEach(dropdown => {
                new bootstrap.Dropdown(dropdown);
            });
            // ... special handling for notificationsDropdown ...
        });
        ```
    *   This initialization appears correct.

5.  **`outcome-editor.js` Error (Separate Issue):**
    *   A console error "metricEditorContainer not found" was observed on `edit_outcome.php` (a page where dropdowns *were* working), originating from `assets/js/outcome-editor.js`.
    *   **Action:** Added a guard in `outcome-editor.js` to only run `initializeOutcomeEditor()` if `document.getElementById('metricEditorContainer')` exists.
    *   **Result:** This resolved the error in `outcome-editor.js` but was confirmed to be unrelated to the main navigation dropdown bug on `manage_outcomes.php`.

6.  **Inline JavaScript Block in `manage_outcomes.php` (Bottom of file):**
    *   The entire `DOMContentLoaded` inline script block at the bottom of `manage_outcomes.php` (responsible for refresh button, create button, and filter auto-submission) was suspected.
    *   **Action (Current State):** This entire `<script> ... </script>` block is currently commented out.
    *   **Result:** Did not resolve the dropdown issue.

### Current Status (As of last test):

*   The navigation dropdowns on `manage_outcomes.php` are **still not working**.
*   In `d:/laragon/www/pcds2030_dashboard/app/views/admin/outcomes/manage_outcomes.php`:
    *   The `Chart.js` script tag (`<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>`) is **commented out**.
    *   The entire inline JavaScript block at the end of the file (`<script> document.addEventListener('DOMContentLoaded', ...); </script>`) is **commented out**.
*   No JavaScript errors are reported in the console on `manage_outcomes.php` with these scripts commented out (the Chart.js source map error is also gone).

### Hypotheses for Next Steps:

*   **HTML Structural Interference:** An element in the main content of `manage_outcomes.php` might be inadvertently overlaying the navigation bar or interfering with click events (e.g., a z-index issue, an element with large transparent dimensions).
*   **CSS Conflict:** A CSS rule specific to `manage_outcomes.php` (or a class applied uniquely on that page) might be affecting the visibility or interactivity of the dropdowns (e.g., `pointer-events: none`, `visibility: hidden` on dropdown menus).
*   **JavaScript Conflict from other included files:** A script included via `header.php` or `footer.php` (other than `dropdown_init.js` itself) might be causing issues only in the context of `manage_outcomes.php`.
*   **Mismatched HTML structure for dropdowns:** Although `admin_nav.php` is shared, ensure no conditional PHP within it could alter the dropdown structure specifically when `manage_outcomes.php` is the active page.

### Files Involved/Checked:

*   `d:/laragon/www/pcds2030_dashboard/app/views/layouts/admin_nav.php`
*   `d:/laragon/www/pcds2030_dashboard/app/views/admin/outcomes/manage_outcomes.php`
*   `d:/laragon/www/pcds2030_dashboard/app/views/admin/outcomes/edit_outcome.php` (working reference)
*   `d:/laragon/www/pcds2030_dashboard/app/views/layouts/footer.php`
*   `d:/laragon/www/pcds2030_dashboard/app/views/layouts/header.php`
*   `d:/laragon/www/pcds2030_dashboard/assets/js/main.js`
*   `d:/laragon/www/pcds2030_dashboard/assets/js/utilities/dropdown_init.js`
*   `d:/laragon/www/pcds2030_dashboard/assets/js/outcome-editor.js`
