# Agency CSS Migration Guide: From Legacy main.css to Vite Bundles

## Overview
This document provides step-by-step instructions to migrate agency pages from the legacy `main.css` system to clean Vite bundle loading. The dashboard page has been successfully migrated and serves as the reference implementation.

## Problem Statement
- **Before**: Agency pages loaded 20+ individual CSS files via `main.css` @imports
- **After**: Each page loads only one bundled CSS file via Vite build system
- **Goal**: Clean network tab with no individual CSS requests, maintain visual consistency

## Prerequisites
- Vite build system configured (✅ Complete)
- Agency shared base CSS created (✅ Complete) 
- Bundle infrastructure working (✅ Complete)

## Migration Steps for Each Agency Page

### Pre-Migration Analysis (Do This First!)
**Before starting migration**, analyze the target page to identify specific styling needs:

1. **Inspect Current CSS Dependencies:**
   ```bash
   # Check what CSS files the page currently loads
   grep -r "asset_url.*css\|\.css" app/views/agency/[page]/
   ```

2. **Identify Unique Components:**
   - Open the page in browser
   - Note: Tables, forms, charts, modals, timelines, etc.
   - Check responsive behavior on mobile
   - Document any JavaScript-dependent styling

3. **Check Existing Page-Specific CSS:**
   ```bash
   # Look for page-specific styles
   ls assets/css/agency/[page]/
   ls assets/css/pages/*[page]*
   ```

4. **Review HTML Structure:**
   - Look for custom wrapper divs
   - Check for data attributes used by JavaScript
   - Note any inline styles that need migration

### Step 1: Remove Legacy CSS Loading
**File**: Target page PHP file (e.g., `view_programs.php`, `initiatives.php`)

**Action**: Remove this line:
```php
<!-- ❌ REMOVE -->
<link rel="stylesheet" href="<?php echo asset_url('css', 'main.css'); ?>">
```

**Verify**: Page should use `base.php` layout with `$cssBundle` variable set

### Step 2: Fix HTML Structure for Footer Positioning
**File**: Page content file (e.g., `view_programs_content.php`)

**Current Issue**: Footer appears in middle of page due to layout conflicts

**Fix**: Remove `content-wrapper` div and use direct `main.flex-fill` structure:

```php
<!-- ❌ WRONG Structure -->
<div class="content-wrapper">
    <main class="flex-fill">
        <!-- content -->
    </main>
</div>

<!-- ✅ CORRECT Structure -->
<main class="flex-fill">
    <div class="container-fluid">
        <!-- content -->
    </div>
</main>
```

**Why**: The `base.php` layout uses `<body class="d-flex flex-column min-vh-100">` and footer uses `margin-top: auto` for sticky positioning. Extra wrapper divs break this pattern.

### Step 3: Verify Missing CSS Imports in Shared Base
**File**: `assets/css/agency/shared/base.css`

**Required Imports** (✅ Already added to shared base):
```css
/* Foundation styles */
@import '../../base/variables.css';
@import '../../base/reset.css'; 
@import '../../base/typography.css';
@import '../../base/utilities.css';

/* Layout system */
@import '../../layout/dashboard.css';    /* Body/main layout */
@import '../../layout/header.css';       /* Page headers */

/* Components */
@import '../../components/footer.css';   /* Footer styling */
@import '../../components/bento-grid.css';
/* ... other components ... */
```

### Step 4: Add Page-Specific CSS Bundle
**File**: `assets/css/agency/[page]/[page].css`

**Structure**:
```css
/* Import shared base first */
@import '../shared/base.css';

/* Import page-specific styles */
@import './base.css';  /* If page has specific components */
```

**File**: `vite.config.js` 
**Action**: Add entry point:
```js
build: {
  rollupOptions: {
    input: {
      'agency-[page]': './assets/css/agency/[page]/[page].css'
    }
  }
}
```

### Step 5: Set Bundle Variable in Page
**File**: Target page PHP file

**Required Variables**:
```php
$cssBundle = 'agency-[page]';  // Matches vite.config.js entry
$jsBundle = 'agency-[page]';   // If page has JS
```

### Step 6: Test and Verify
**Build**: Run `npm run build`
**Check**: Bundle file created in `dist/css/agency-[page].bundle.css`
**Network Tab**: Should show only:
- Google Fonts (external)
- Bootstrap CDN (external)  
- Font Awesome CDN (external)
- Single bundle file (internal)

## Common Issues and Solutions

### Issue: Footer in Middle of Page
**Cause**: Extra `content-wrapper` div interfering with flexbox layout
**Solution**: Use direct `main.flex-fill` structure (see Step 2)

### Issue: Missing Section Styling
**Cause**: Page-specific CSS not imported to shared base
**Solution**: Add page's `./base.css` import to page bundle

### Issue: Missing Foundation Styles
**Cause**: Variables, reset, typography not loaded
**Solution**: Verify shared base imports (see Step 3)

### Issue: Large Bundle Size Jump
**Cause**: Expected - consolidating 20+ files into one bundle
**Reference**: Dashboard bundle is 78.37 kB (previously 65+ individual files)

## Page-Specific Considerations

### Programs Pages (`view_programs.php`, `create_program.php`, `edit_program.php`)

**Unique Elements:**
- Data tables with pagination
- Program creation/edit forms  
- Modal dialogs for confirmations
- Status badges and indicators
- Filter dropdowns
- Action buttons (Create, Edit, Delete)

**Required CSS Imports:**
```css
/* In agency-programs bundle */
@import '../shared/base.css';
@import '../../components/tables.css';         /* Data tables */
@import '../../components/modals.css';         /* Confirmation dialogs */
@import '../../components/pagination.css';     /* Table pagination */
@import '../../components/dropdowns.css';      /* Filter controls */
@import '../../components/status-indicators.css'; /* Program status */
@import './base.css';                          /* Programs-specific styles */
```

**HTML Structure Considerations:**
- Tables may need responsive wrapper: `<div class="table-responsive">`
- Forms need proper Bootstrap grid classes
- Modals require proper backdrop/dialog structure

**Testing Focus:**
- [ ] Table sorting and pagination works
- [ ] Form validation styling appears correctly
- [ ] Modal dialogs center properly
- [ ] Status badges display with correct colors
- [ ] Mobile responsive table scrolling

### Initiatives Pages (`initiatives.php`, `view_initiative.php`)

**Unique Elements:**
- Timeline components
- Progress indicators
- Initiative cards with images
- Status workflow indicators
- Milestone markers
- Detail view layouts

**Required CSS Imports:**
```css
/* In agency-initiatives bundle */
@import '../shared/base.css';
@import '../../components/timeline.css';       /* Initiative timelines */
@import '../../components/progress-bars.css'; /* Progress indicators */
@import '../../components/milestones.css';    /* Milestone markers */
@import '../../components/image-cards.css';   /* Initiative cards */
@import '../../components/workflow.css';      /* Status workflows */
@import './base.css';                         /* Initiatives-specific */
```

**HTML Structure Considerations:**
- Timeline elements need proper vertical alignment
- Progress bars require percentage calculations
- Card layouts may use CSS Grid or Flexbox
- Image elements need responsive handling

**Testing Focus:**
- [ ] Timeline displays vertically aligned
- [ ] Progress bars animate correctly
- [ ] Initiative cards maintain aspect ratios
- [ ] Milestone indicators are positioned correctly
- [ ] Detail views have proper spacing

### Outcomes Pages (`submit_outcomes.php`, `create_outcome_flexible.php`, `view_outcome.php`)

**Unique Elements:**
- Chart.js integration (graphs, charts)
- Data input forms with dynamic fields
- Metric display cards
- Export/import functionality
- Data visualization containers
- KPI indicators

**Required CSS Imports:**
```css
/* In agency-outcomes bundle */
@import '../shared/base.css';
@import '../../components/charts.css';        /* Chart.js containers */
@import '../../components/metrics.css';       /* KPI displays */
@import '../../components/data-forms.css';    /* Dynamic forms */
@import '../../components/export-tools.css';  /* Export buttons */
@import '../../components/visualizations.css'; /* Data viz */
@import './base.css';                         /* Outcomes-specific */
```

**HTML Structure Considerations:**
- Chart containers need fixed dimensions
- Dynamic forms may add/remove fields via JS
- Metric cards need consistent sizing
- Export tools require proper button grouping

**Testing Focus:**
- [ ] Charts render with correct dimensions
- [ ] Dynamic form fields add/remove properly
- [ ] Metric cards align in grid layout
- [ ] Export functionality styling intact
- [ ] Data tables display correctly
- [ ] Mobile chart responsiveness works

### Reports Pages (`reports.php`, `view_report.php`)

**Unique Elements:**
- Print-optimized layouts
- Report generation forms
- Date range pickers
- Export format options
- Report preview containers
- Data summary tables

**Required CSS Imports:**
```css
/* In agency-reports bundle */
@import '../shared/base.css';
@import '../../components/print-styles.css';   /* Print layouts */
@import '../../components/date-pickers.css';   /* Date controls */
@import '../../components/report-forms.css';   /* Generation forms */
@import '../../components/preview.css';        /* Report previews */
@import '../../components/export-options.css'; /* Format selection */
@import './base.css';                          /* Reports-specific */
```

**HTML Structure Considerations:**
- Print styles need `@media print` rules
- Date pickers may use third-party widgets
- Preview containers need proper sizing
- Export options require radio/checkbox styling

**Testing Focus:**
- [ ] Print preview displays correctly
- [ ] Date range pickers function properly
- [ ] Report generation forms validate
- [ ] Export format options style correctly
- [ ] Preview containers size appropriately
- [ ] Print styles work when printing

### Notifications Pages (`notifications.php`)

**Unique Elements:**
- Notification list items
- Read/unread indicators
- Action buttons (Mark as read, Delete)
- Notification categories/types
- Time stamps formatting
- Bulk action controls

**Required CSS Imports:**
```css
/* In agency-notifications bundle */
@import '../shared/base.css';
@import '../../components/notification-items.css'; /* List items */
@import '../../components/indicators.css';          /* Read/unread */
@import '../../components/bulk-actions.css';       /* Bulk controls */
@import '../../components/timestamps.css';         /* Time formatting */
@import './base.css';                              /* Notifications-specific */
```

**HTML Structure Considerations:**
- List items need consistent spacing
- Indicators require proper positioning
- Bulk actions need checkbox alignment
- Time stamps may need relative positioning

**Testing Focus:**
- [ ] Notification items display consistently
- [ ] Read/unread indicators show correctly
- [ ] Bulk action checkboxes align properly
- [ ] Time stamps format appropriately
- [ ] Action buttons respond correctly
- [ ] Empty state displays properly

## Critical Page-Specific Files to Check

### Before Migration - Identify Dependencies
For each page, check these files for specific styling needs:

1. **Page's current CSS file**: `assets/css/agency/[page]/base.css`
2. **Page's JS dependencies**: Look for CSS classes manipulated by JavaScript
3. **Template partials**: Check if page includes shared components
4. **Form elements**: Identify custom form styling requirements

### Common Page-Specific Issues

**Tables with Custom Styling:**
- Programs: Rating indicators, status columns
- Reports: Data summary formatting
- Solution: Ensure `tables.css` and custom table CSS imported

**Forms with Dynamic Elements:**
- Outcomes: Dynamic metric inputs
- Programs: Conditional form fields
- Solution: Include `forms.css` and JavaScript-dependent styles

**Charts and Visualizations:**
- Outcomes: Chart.js integration
- Dashboard: Already handled in shared base
- Solution: Add `charts.css` with proper container sizing

**Mobile-Specific Layouts:**
- All pages: Responsive navigation, tables
- Solution: Ensure responsive CSS imports and test on mobile

## Validation Checklist

For each migrated page:

- [ ] Network tab shows only 4 CSS files (3 external CDNs + 1 bundle)
- [ ] Footer appears at bottom of viewport
- [ ] Page header styling intact (titles, breadcrumbs)
- [ ] All interactive elements styled (buttons, forms, tables)
- [ ] No browser console CSS errors
- [ ] Bundle builds successfully with `npm run build`
- [ ] Visual comparison matches pre-migration appearance

## Reference Implementation
- **Completed**: `app/views/agency/dashboard/dashboard.php`
- **Bundle**: `agency-dashboard.bundle.css` (78.37 kB)
- **Structure**: Uses `base.php` layout with direct `main.flex-fill`
- **Network**: Clean 4-file loading pattern

## Files Modified in Dashboard Migration
1. `assets/css/agency/shared/base.css` - Added foundation imports
2. `assets/css/agency/dashboard/dashboard.css` - Added base import  
3. `app/views/agency/dashboard/dashboard_content.php` - Fixed HTML structure
4. `assets/css/layout/dashboard.css` - Fixed flexbox layout
5. `vite.config.js` - Bundle configuration (already done)

## Next Steps
Apply this guide systematically to remaining agency pages:
1. `view_programs.php` and related program pages
2. `initiatives.php` 
3. `submit_outcomes.php` and outcome pages
4. `reports.php`
5. `notifications.php`

Each page should achieve the same clean bundle loading as the dashboard.
