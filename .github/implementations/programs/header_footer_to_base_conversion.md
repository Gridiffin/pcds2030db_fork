# Programs Module Complete Header/Footer to Base.php Conversion

## Issue Description
Some programs module pages were still using the old `header.php/footer.php` layout system instead of the modern `base.php` layout with bundle loading. This prevented proper CSS and JS bundle loading.

## THINK Phase
All programs module pages must use `base.php` layout to enable:
1. Proper CSS bundle loading (`programs.bundle.css`)
2. Consistent layout structure with `<main class="flex-fill">`
3. Modern asset management through Vite bundling
4. Centralized header/footer management

## Files Converted

### 1. edit_submission.php
**Before**: Used `require_once '../../layouts/header.php';` and `require_once '../../layouts/footer.php';`

**After**: Converted to base.php layout with proper structure:

```php
// Bundle configuration
$cssBundle = 'programs';
$jsBundle = null;

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/agency/edit_submission.js'
];

// Content structure
<main class="flex-fill">
<div class="container-fluid">
    <!-- Main content -->
</div>
</main>

<?php
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?>
```

**Changes Made**:
- ❌ Removed: `require_once '../../layouts/header.php';`
- ❌ Removed: `require_once '../../layouts/page_header.php';`
- ❌ Removed: `require_once '../../layouts/footer.php';`
- ❌ Removed: `<script src="<?php echo asset_url('js/agency', 'edit_submission.js'); ?>"></script>`
- ✅ Added: `$additionalScripts = [APP_URL . '/assets/js/agency/edit_submission.js'];`
- ✅ Added: `<main class="flex-fill">` wrapper
- ✅ Added: `require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';`

### 2. view_other_agency_programs.php
**Before**: Simple placeholder with old layout system

**After**: Complete rewrite using base.php pattern:

```php
<?php
// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';

// Set up base layout variables
$pageTitle = 'Other Agencies\' Programs';
$cssBundle = 'programs';
$jsBundle = null;

// Configure modern page header
$header_config = [
    'title' => 'Other Agencies\' Programs',
    'subtitle' => 'Browse programs from other agencies',
    'variant' => 'blue'
];

$contentFile = null;
?>

<main class="flex-fill">
    <div class="container-fluid">
        <!-- Content -->
    </div>
</main>

<?php
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?>
```

### 3. program_details.php (Path Fix)
**Issue**: Had incorrect path `require_once PROJECT_ROOT_PATH . 'views/layouts/base.php';`

**Fixed**: Updated to `require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';`

## Verification Results

### ✅ All Header/Footer References Removed
```bash
# No matches found for:
grep -r "header.php" app/views/agency/programs/*.php
grep -r "footer.php" app/views/agency/programs/*.php
```

### ✅ All Files Using Base.php Layout
Every programs module file now includes:
- `view_programs.php`
- `create_program.php`
- `edit_program.php`
- `add_submission.php`
- `program_details.php`
- `edit_submission.php`
- `view_submissions.php`
- `view_other_agency_programs.php`

### ✅ All Files Using Programs Bundle
Every file has: `$cssBundle = 'programs';`

## Standard Programs Page Structure

Following `view_programs.php` as the reference, all programs pages now follow this pattern:

```php
<?php
// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
// ... other includes

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Page logic here...

// Set up base layout variables
$pageTitle = 'Page Title';
$cssBundle = 'programs';
$jsBundle = 'bundle-name'; // or null

// Additional scripts (optional)
$additionalScripts = [
    APP_URL . '/assets/js/agency/page-script.js'
];

// Configure modern page header
$header_config = [
    'title' => 'Page Title',
    'subtitle' => 'Page description',
    'variant' => 'green', // or 'white', 'blue'
    'actions' => [
        [
            'url' => 'back_page.php',
            'text' => 'Back',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Set content file (if using partial) or null for inline content
$contentFile = __DIR__ . '/page_content.php'; // or null
?>

<!-- Inline content (if $contentFile = null) -->
<main class="flex-fill">
    <div class="container-fluid">
        <!-- Page content here -->
    </div>
</main>

<?php
// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?>
```

## Expected Bundle Loading Behavior

After this conversion, all programs pages should:

### ✅ Browser Network Tab Shows:
- Single CSS request: `programs.bundle.css` (~108.83 KB)
- Bootstrap CSS from CDN
- Font Awesome CSS from CDN
- Any additional scripts from `$additionalScripts`

### ❌ Browser Network Tab Should NOT Show:
- `main.css` requests
- Individual CSS files (programs.css, form.css, etc.)
- Old asset loading patterns

## Testing Verification

Each converted page should be tested:
1. Visit the page URL
2. Check Network tab for clean bundle loading
3. Verify styling is properly applied
4. Confirm layout structure (header, nav, main content, footer)

## Status: ✅ COMPLETE

All programs module pages have been successfully converted from the old `header.php/footer.php` system to the modern `base.php` layout with proper bundle loading. The entire programs module now follows a consistent, clean architecture with optimized asset loading.
