# Project Restructure: Phase 1 - File Reorganization and Path Updates

## Goal
To restructure the project for improved maintainability by organizing files into a more logical structure, primarily by moving core application logic into an `app/` directory. This phase focuses on moving files and updating all internal references (PHP includes/requires) and asset links accordingly. The aim is to achieve this with minimal introduction of complex new patterns, keeping it straightforward for developers familiar with traditional PHP.

## Proposed New Directory Structure (Phase 1)

```
pcds2030_dashboard/
├── app/                     # New: Houses core application logic
│   ├── ajax/                # Moved from root (e.g., app/ajax/admin_dashboard_data.php)
│   ├── api/                 # Moved from root (e.g., app/api/report_data.php)
│   ├── config/              # Moved from root
│   │   └── config.php
│   ├── controllers/         # New: For controller files
│   │   └── DashboardController.php # Moved from includes/
│   ├── database/            # Moved from root
│   │   └── pcds2030_dashboard.sql
│   ├── handlers/            # New: For specific processing scripts
│   │   └── admin/
│   │       ├── get_user.php     # Moved from root/admin/
│   │       └── process_user.php # Moved from root/admin/
│   ├── lib/                 # Renamed from 'includes'
│   │   ├── admins/          # Moved from includes/admins/
│   │   │   ├── core.php
│   │   │   ├── index.php    # Aggregates other admin function files
│   │   │   ├── metrics.php
│   │   │   ├── outcomes.php
│   │   │   ├── periods.php
│   │   │   ├── settings.php
│   │   │   ├── statistics.php
│   │   │   └── users.php
│   │   ├── agencies/        # Moved from includes/agencies/
│   │   │   ├── core.php
│   │   │   ├── index.php    # Aggregates other agency function files
│   │   │   ├── metrics.php
│   │   │   ├── outcomes.php
│   │   │   ├── programs.php
│   │   │   └── statistics.php
│   │   ├── admin_dashboard_stats.php # Moved from includes/
│   │   ├── admin_functions.php       # Moved from includes/
│   │   ├── agency_functions.php      # Moved from includes/
│   │   ├── dashboard_header.php      # Moved from includes/ (Consider moving to views/partials later)
│   │   ├── db_connect.php            # Moved from includes/
│   │   ├── functions.php             # Moved from includes/
│   │   ├── period_selector.php       # Moved from includes/ (Consider moving to views/partials later)
│   │   ├── rating_helpers.php        # Moved from includes/
│   │   ├── session.php               # Moved from includes/
│   │   └── utilities.php             # Moved from includes/
│   ├── reports/             # Moved from root (e.g., app/reports/pptx/)
│   └── views/               # Moved from root
│       ├── admin/           # (e.g., app/views/admin/dashboard.php)
│       ├── agency/          # (e.g., app/views/agency/dashboard.php)
│       └── layouts/         # (e.g., app/views/layouts/header.php)
├── assets/                  # Stays at project root
│   ├── css/
│   ├── fonts/
│   ├── images/
│   └── js/
├── download.php             # Stays at project root
├── index.php                # Stays at project root
├── login.php                # Stays at project root
├── logout.php               # Stays at project root
├── package.json
├── README.md
└── system_context.txt
```

## Step-by-Step Plan

### 1. Preparation
- [x] **Backup Project:** Create a complete backup of the entire `pcds2030_dashboard` directory.
- [x] **Version Control:** Ensure all current changes are committed to your version control system (e.g., Git). Create a new branch for this restructuring task.

### 2. Create New Directory Structure
- [x] Create the `app/` directory at the project root.
- [x] Inside `app/`, create the following subdirectories:
    - `ajax/`
    - `api/`
    - `config/`
    - `controllers/`
    - `database/`
    - `handlers/`
        - `handlers/admin/`
    - `lib/`
        - `lib/admins/`
        - `lib/agencies/`
    - `reports/`
    - `views/`
        - `views/admin/` (if not already existing from a move)
        - `views/agency/` (if not already existing from a move)
        - `views/layouts/` (if not already existing from a move)

### 3. Move Files and Folders
- [x] Move the existing `config/` directory into `app/`. (Result: `app/config/`)
- [x] Move the existing `includes/DashboardController.php` to `app/controllers/DashboardController.php`.
- [x] Move the existing `database/` directory into `app/`. (Result: `app/database/`)
- [x] Move files from the existing root `admin/` directory (`get_user.php`, `process_user.php`) to `app/handlers/admin/`.
- [x] Move the existing `ajax/` directory into `app/`. (Result: `app/ajax/`)
- [x] Move the existing `api/` directory into `app/`. (Result: `app/api/`)
- [x] Move the contents of the existing `includes/` directory (excluding `DashboardController.php` and the `admins/`, `agencies/` subdirectories) into `app/lib/`.
- [x] Move the existing `includes/admins/` directory to `app/lib/admins/`.
- [x] Move the existing `includes/agencies/` directory to `app/lib/agencies/`.
- [x] Move the existing `reports/` directory into `app/`. (Result: `app/reports/`)
- [x] Move the existing `views/` directory into `app/`. (Result: `app/views/`)
- [x] Delete the old `admin/` (root), `includes/` directories once their contents are successfully moved.

### 4. Update Configuration (`app/config/config.php`)
- [x] Open `app/config/config.php`.
- [x] **`ROOT_PATH` Definition:**
    *   The way `ROOT_PATH` is defined needs to be robust. We will define `PROJECT_ROOT_PATH` in the entry scripts (like `index.php` at the project root).
    *   Modified `app/config/config.php` to use this or have a fallback:
        ```php
        // At the beginning of app/config/config.php, or where paths are defined:
        if (!defined('ROOT_PATH')) {
            if (defined('PROJECT_ROOT_PATH')) {
                define('ROOT_PATH', PROJECT_ROOT_PATH);
            } else {
                // Fallback if accessed directly or from a script not defining PROJECT_ROOT_PATH                // This assumes config.php is in app/config/
                define('ROOT_PATH', dirname(dirname(__DIR__)) . '/'); // Points to pcds2030_dashboard/
            }
        }
        ```
    *   Replaced existing `define('ROOT_PATH', dirname(__DIR__) . '/');` with the above definition.
- [x] **`APP_URL` Definition:**
    *   `define('APP_URL', 'http://localhost/pcds2030_dashboard');` remained unchanged as entry points and `assets/` are still at the root.
- [x] **Update other paths:**
    *   Updated `define('UPLOAD_PATH', ROOT_PATH . 'app/uploads/');` to reflect uploads directory in app.
    *   Updated `define('REPORT_PATH', ROOT_PATH . 'app/reports/');` to reflect the move of `reports/` into `app/`.

### 5. Update Entry Point PHP Files (at Project Root)
- [x] For each PHP file at the project root (`index.php`, `login.php`, `logout.php`, `download.php`):
    *   At the very beginning of each file, add:
        ```php
        <?php
        if (!defined('PROJECT_ROOT_PATH')) {
            define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
        }
        // Example: Update how config.php is included
        require_once PROJECT_ROOT_PATH . 'app/config/config.php';
        // ... any other direct initial includes from the old 'includes/' folder ...
        // e.g., require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
        ?>
        ```
    *   Update all their `require_once` / `include_once` statements for files that have moved into `app/` to use `PROJECT_ROOT_PATH . 'app/...'`.

### 6. Update PHP Include/Require Paths Globally
- [x] For all PHP files now located within the `app/` directory:
    *   Search for all `require_once`, `require`, `include_once`, `include` statements.
    *   Update their paths to reflect the new structure, using the `ROOT_PATH` constant.
    *   Examples:
        *   `require_once '../../config/config.php';` becomes `require_once ROOT_PATH . 'app/config/config.php';`
        *   `require_once '../includes/db_connect.php';` becomes `require_once ROOT_PATH . 'app/lib/db_connect.php';`
        *   `require_once 'core.php';` (if inside `app/lib/admins/index.php` referring to `app/lib/admins/core.php`) remains `require_once 'core.php';` as it's relative to the current file and both moved together.
    *   The key is that `ROOT_PATH` points to `pcds2030_dashboard/`.

### 7. Update Asset Links
- [x] **`app/views/layouts/header.php`:**
    *   Verify that all asset links (CSS, JS, fonts, images) using `<?php echo APP_URL; ?>/assets/...` are still correct. Since `APP_URL` and the `assets/` directory's root location haven't changed, these should generally be fine.
- [x] **`login.php` (at project root):**
    *   It contains hardcoded asset links like `href="assets/css/main.css"`.
    *   Update these to use `APP_URL` for consistency:
        ```php
        <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/main.css">
        <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/pages/login.css">
        <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="Logo" class="logo-image">
        ```
        (Ensure `APP_URL` is available, which it will be if `config.php` is included as per step 5).
- [ ] **CSS `url()` paths:**
    *   If any CSS files use `url()` to reference assets (e.g., fonts from within `assets/fonts/`, images from `assets/images/`), these paths are relative to the CSS file itself. Since the internal structure of `assets/` is not changing, and `assets/` itself is not moving relative to the web root, these should be fine. Double-check if issues arise.

### 8. Testing
- [x] **Basic Syntax Check:** Verified PHP syntax in key files (index.php, login.php, view_programs.php).
- [ ] **Clear Cache:** Clear any server-side caching (OpCache, APCu) if enabled, and browser cache.
- [ ] **Thoroughly Test Application:**
    *   Navigate through all pages of the application (admin and agency sections).
    *   Test all functionalities: login, logout, data submission, report generation, AJAX actions, API endpoints.
    *   Check browser developer console for any 404 errors (missing files, assets) or PHP errors.
    *   Check server error logs.

### 9. Review and Commit
- [ ] Review all changes.
- [ ] Commit the changes to your version control system.

## Future Considerations (Phase 2+)
*   Move `app/lib/dashboard_header.php` and `app/lib/period_selector.php` to a new `app/views/partials/` or `app/views/components/` directory.
*   Consider a `public/` directory for web-accessible files (`index.php`, `assets/`) for enhanced security, though this adds cPanel configuration complexity.
*   Further modularization of code within `app/lib/` or `app/handlers/`.
*   Adopting a more consistent autoloading mechanism (e.g., Composer) if not already in use for classes.
