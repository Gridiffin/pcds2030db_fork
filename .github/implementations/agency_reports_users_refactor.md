# Agency Reports and Users Module Refactoring

**Date:** 2025-07-20
**Modules:** Agency Reports (view_reports.php, public_reports.php) and Users (all_notifications.php)

## Current State Analysis

### Reports Module Issues
- ❌ Uses old ROOT_PATH constant instead of PROJECT_ROOT_PATH
- ❌ Uses old header include pattern instead of base layout
- ❌ Missing modular CSS/JS structure
- ❌ PHP logic mixed with view (no proper data handling)
- ❌ No proper reports functionality (reports array undefined)
- ❌ Missing error handling and validation
- ❌ No pagination for large report lists
- ❌ Hardcoded paths and no dynamic asset loading

### All Notifications Module Issues
- ❌ Uses old header include pattern instead of base layout
- ❌ Inline CSS styles instead of modular CSS files
- ❌ Missing JS functionality for AJAX operations
- ❌ No modular structure (800+ lines in single file)
- ❌ Direct database queries in view file
- ❌ No proper error handling or loading states
- ❌ Missing notification management features

## Planned Refactor Structure

### Reports Module
```
app/views/agency/reports/
├── view_reports.php           # Main view using base layout
├── public_reports.php         # Public reports view using base layout
└── partials/
    ├── reports_filter.php     # Filter form partial
    ├── reports_list.php       # Reports listing partial
    └── reports_info.php       # Info section partial

assets/css/agency/reports/
├── reports.css               # Main CSS (imports subfiles)
├── view_reports.css          # View reports specific styles
├── public_reports.css        # Public reports specific styles
└── partials/
    ├── filter.css            # Filter component styles
    ├── list.css              # List component styles
    └── info.css              # Info section styles

assets/js/agency/reports/
├── reports.js                # Main JS entry (imports submodules)
├── logic.js                  # Pure logic functions
├── view_reports.js           # View reports page logic
├── public_reports.js         # Public reports page logic
└── ajax.js                   # AJAX operations
```

### Users Module (All Notifications)
```
app/views/agency/users/
├── all_notifications.php     # Main view using base layout
└── partials/
    ├── notification_list.php  # Notification listing partial
    ├── notification_item.php  # Individual notification partial
    ├── notification_header.php # Header with stats/actions
    └── pagination.php         # Pagination partial

assets/css/agency/users/
├── notifications.css         # Main CSS (imports subfiles)
└── partials/
    ├── list.css              # List component styles
    ├── item.css              # Individual item styles
    ├── header.css            # Header component styles
    └── pagination.css        # Pagination styles

assets/js/agency/users/
├── notifications.js          # Main JS entry (imports submodules)
├── logic.js                  # Pure logic functions (formatters, etc.)
├── ajax.js                   # AJAX operations
└── interactions.js           # DOM interactions and events
```

## Implementation Plan

### Phase 1: Reports Module Refactoring
- [ ] Create implementation doc and gather context
- [ ] Move PHP logic to appropriate lib files
- [ ] Create modular view structure with partials
- [ ] Implement base layout usage
- [ ] Create modular CSS structure
- [ ] Create modular JS structure with Vite bundling
- [ ] Add proper error handling and validation
- [ ] Add AJAX functionality for dynamic loading
- [ ] Test and validate functionality

### Phase 2: All Notifications Refactoring
- [ ] Move PHP logic to lib files (notification management)
- [ ] Create modular view structure with partials
- [ ] Implement base layout usage
- [ ] Create modular CSS structure (remove inline styles)
- [ ] Create modular JS structure for AJAX operations
- [ ] Add notification management features (mark as read, delete)
- [ ] Add real-time updates and better UX
- [ ] Test and validate functionality

### Phase 3: Integration and Testing
- [ ] Update Vite configuration for new bundles
- [ ] Build and test assets
- [ ] Integration testing with existing navigation
- [ ] Performance optimization
- [ ] Documentation updates

## Technical Requirements

### Base Layout Usage
Both modules will use the base layout pattern:
```php
<?php
$pageTitle = 'Reports';
$cssBundle = 'agency/reports.bundle.css';
$jsBundle = 'agency/reports.bundle.js';
$contentFile = __DIR__ . '/partials/reports_content.php';

require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?>
```

### Vite Configuration Updates
Add new entry points to vite.config.js:
```js
input: {
  'agency-reports': 'assets/js/agency/reports/reports.js',
  'agency-notifications': 'assets/js/agency/users/notifications.js'
}
```

### Data Handling
Move all database operations to lib files:
- `app/lib/agencies/reports.php` - Reports management
- `app/lib/agencies/notifications.php` - Notifications management

## Success Criteria
- [ ] All files under 300 lines (500 max, 800 ceiling)
- [ ] Proper separation of concerns
- [ ] Modular CSS/JS with Vite bundling
- [ ] Base layout usage throughout
- [ ] No inline styles or scripts
- [ ] Proper error handling and validation
- [ ] AJAX functionality for better UX
- [ ] Mobile-responsive design
- [ ] Accessibility compliance
- [ ] Performance optimization

## Notes
- Follow established patterns from login and dashboard refactors
- Maintain existing functionality while improving architecture
- Ensure compatibility with existing navigation and permissions
- Add proper audit logging for notification actions
