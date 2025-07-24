# Legacy CSS Classes and Patterns Analysis

## Executive Summary

This document provides a comprehensive analysis of legacy CSS classes and patterns identified across all modules in `app/views/`. The analysis reveals extensive Bootstrap legacy usage, inline styles, hardcoded values, and custom legacy patterns that need systematic replacement with modern components.

## 1. Bootstrap Legacy Classes Inventory

### Core Bootstrap Components Found

#### Buttons
- **Legacy Classes**: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-success`, `.btn-danger`, `.btn-warning`, `.btn-info`, `.btn-light`, `.btn-dark`, `.btn-outline-*`, `.btn-group`, `.btn-group-sm`, `.btn-sm`
- **Modern Replacement**: `.btn-modern`, `.btn-primary-modern`, `.btn-outline-primary-modern`, `.btn-sm-modern`
- **Files Affected**: 47+ files across admin and agency modules
- **Priority**: HIGH

#### Cards
- **Legacy Classes**: `.card`, `.card-header`, `.card-body`, `.card-footer`, `.card-title`, `.shadow-sm`
- **Modern Replacement**: `.card-modern`, `.card-header-modern`, `.card-body-modern`, `.card-footer-modern`, `.card-title-modern`
- **Files Affected**: 35+ files
- **Priority**: HIGH

#### Forms
- **Legacy Classes**: `.form-control`, `.form-select`, `.form-check`, `.form-check-input`, `.form-check-label`, `.form-group`, `.input-group`, `.input-group-text`
- **Modern Replacement**: `.form-input-modern`, `.form-select-modern`, `.form-group-modern`, `.input-group-modern`
- **Files Affected**: 25+ files
- **Priority**: HIGH

#### Tables
- **Legacy Classes**: `.table`, `.table-hover`, `.table-bordered`, `.table-responsive`, `.table-sm`, `.table-forest`, `.table-light`
- **Modern Replacement**: `.table-modern`, `.table-responsive-modern`
- **Files Affected**: 20+ files
- **Priority**: MEDIUM

#### Navigation & Layout
- **Legacy Classes**: `.navbar`, `.nav`, `.nav-tabs`, `.nav-pills`, `.breadcrumb`, `.breadcrumb-item`, `.container`, `.container-fluid`, `.row`, `.col-*`
- **Modern Replacement**: Grid system needs evaluation
- **Files Affected**: Navigation files, headers
- **Priority**: MEDIUM

#### Alerts & Badges
- **Legacy Classes**: `.alert`, `.alert-danger`, `.alert-success`, `.badge`, `.bg-success`, `.bg-danger`, `.bg-warning`, `.bg-info`
- **Modern Replacement**: `.alert-modern`, `.badge-modern`
- **Files Affected**: 15+ files
- **Priority**: MEDIUM

#### Pagination
- **Legacy Classes**: `.pagination`, `.page-item`, `.page-link`
- **Modern Replacement**: `.pagination-modern`
- **Files Affected**: User tables, reports
- **Priority**: LOW

## 2. Inline Styles Inventory

### Critical Inline Styles Found

#### Layout & Positioning
```php
// /app/views/layouts/main_toast.php:1
style="position: fixed; top: 1rem; right: 1rem; z-index: 9999; display:none;"

// /app/views/admin/outcomes/view_outcome.php:403
style="width: 100%; height: 800px; margin: 20px 0;"

// /app/views/admin/users/_user_table.php:28
style="width: 100px;"
```
**Replacement Strategy**: Create utility classes `.position-fixed`, `.z-index-toast`, `.chart-container`

#### Display States
```php
// Multiple files showing/hiding elements
style="display: none;"
style="display: block;"
```
**Replacement Strategy**: Use `.hidden` and `.visible` utility classes

#### Size & Spacing
```php
// Fixed dimensions
style="width: 40px; height: 40px;"
style="max-height: 300px; overflow-y: auto;"
style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6;"
```
**Replacement Strategy**: Create size utility classes and scrollable container components

## 3. Hardcoded Colors and Values

### Color Values Found
- `#6c757d` (Bootstrap gray)
- `#666`, `#555` (text colors)
- `#ccc` (borders)
- `#007bff` (Bootstrap primary)
- `#28a745` (Bootstrap success)
- `rgba(0,0,0,0.05)` (background overlays)

**Replacement Strategy**: Replace with design token variables from `design-tokens.css`:
- `var(--color-gray-600)` instead of `#6c757d`
- `var(--color-primary)` instead of `#007bff`
- `var(--color-success)` instead of `#28a745`

### Spacing Values Found
- Fixed pixel values: `10px`, `20px`, `1rem`
- Bootstrap spacing: `mb-3`, `mt-2`, `py-4`

**Replacement Strategy**: Use design token spacing:
- `var(--space-3)` instead of `12px`
- `var(--space-6)` instead of `24px`

## 4. Table Styling Patterns

### Legacy Table Classes
```css
.table-forest           // Custom forest theme table
.table-custom          // Custom styling
.table-bordered        // Bootstrap borders
.table-hover          // Bootstrap hover effects
.data-table           // Custom class for data tables
```

### Table Structure Patterns
- Fixed column widths with inline styles
- Custom header styling
- Pagination components
- Responsive wrappers

**Replacement Strategy**: Create `.table-modern` component system with:
- `.table-modern-forest` theme variant
- `.table-modern-responsive` wrapper
- `.table-modern-pagination` component

## 5. JavaScript Dependencies on CSS Classes

### Bootstrap JavaScript Dependencies
```javascript
// Dropdown initialization
document.querySelectorAll('[data-bs-toggle="dropdown"]')

// Modal triggers
$('[data-bs-toggle="modal"]')

// Tab navigation
$('[data-bs-toggle="tab"]')
```

### Custom JavaScript Selectors
```javascript
// User table pagination
'.user-table-page-link'

// Button actions
'.delete-user-btn'
'.toggle-active-btn'

// Form elements
'.toggle-password'
'.notification-action-link'
```

**Impact Assessment**: JavaScript dependencies are minimal and mostly use data attributes. CSS class changes won't break functionality.

## 6. Page-Specific CSS Loading Patterns

### $additionalStyles Usage
Found in 9 files using `$additionalStyles` array pattern:
- `/admin/outcomes/view_outcome.php`
- `/admin/outcomes/edit_outcome.php`
- `/admin/reports/generate_reports.php`
- `/admin/programs/edit_program_backup.php`
- `/admin/periods/reporting_periods.php`
- `/admin/audit/audit_log.php`

**Current Pattern**:
```php
$additionalStyles = [
    'css/admin/specific-page.css',
    'css/components/charts.css'
];
```

**Recommendation**: Transition to modern component imports in base.css instead of page-specific loading.

## 7. Custom Legacy Classes

### Admin-Specific Classes
- `.admin-card` - Custom admin card styling
- `.admin-layout` - Layout modifier
- `.admin-content` - Content wrapper
- `.admin-header-wrapper` - Header container

### Agency-Specific Classes
- `.agency-layout` - Layout modifier
- `.agency-content` - Content wrapper

### Form-Specific Classes
- `.form-group` - Bootstrap form grouping (legacy)
- `.user-checkboxes` - Custom checkbox container

## 8. Migration Priority Matrix

### Phase 1 - Critical (Immediate)
1. **Button Components** - 47+ files affected
2. **Card Components** - 35+ files affected  
3. **Form Components** - 25+ files affected

### Phase 2 - Important (Next Sprint)
1. **Table Components** - 20+ files affected
2. **Layout Grid System** - Navigation and headers
3. **Alert/Badge Components** - 15+ files affected

### Phase 3 - Enhancement (Future)
1. **Pagination Components** - User tables, reports
2. **Page-specific CSS Cleanup** - 9 files with additionalStyles
3. **Utility Class Standardization** - Spacing, colors, display

## 9. Replacement Strategy Recommendations

### Immediate Actions Needed

1. **Create Migration Utilities**
   - CSS class mapping tool
   - Automated search/replace scripts
   - Component usage tracking

2. **Establish Modern Component Library**
   - Complete existing modern components
   - Create missing modern equivalents
   - Document component API

3. **Implement Gradual Migration**
   - Start with highest-impact files
   - Maintain backward compatibility
   - Test each component migration

### Modern Component Gaps Identified

**Missing Modern Components**:
- Table components (complete system)
- Pagination components
- Alert/notification components
- Badge components
- Modal components
- Dropdown/select components

### Legacy Cleanup Steps

1. **Phase 1**: Replace Bootstrap button/card/form classes with modern equivalents
2. **Phase 2**: Convert inline styles to utility classes
3. **Phase 3**: Replace hardcoded values with design tokens
4. **Phase 4**: Migrate table patterns to modern components
5. **Phase 5**: Clean up page-specific CSS loading

## 10. Risk Assessment

### High Risk Areas
- **User Management Tables** - Complex pagination and sorting
- **Form Validation** - JavaScript form handling
- **Chart Components** - Custom styling and interactions
- **Navigation Components** - Bootstrap JavaScript dependencies

### Medium Risk Areas
- **Dashboard Cards** - Mixed modern/legacy patterns
- **Report Generation** - Complex table layouts
- **Modal Dialogs** - Bootstrap modal system

### Low Risk Areas
- **Static Content Pages** - Minimal interactions
- **Footer Components** - Simple styling
- **Basic Text Content** - Typography only

## Conclusion

The codebase contains extensive legacy Bootstrap usage with 100+ files requiring modernization. The highest impact changes involve button, card, and form components affecting 47+ files each. A systematic approach focusing on high-impact components first will provide the best return on investment while maintaining system stability during the transition.

The modern component system is well-established with cards, buttons, and forms already implemented. The primary task is systematic replacement of legacy classes with their modern equivalents, followed by cleanup of inline styles and hardcoded values.