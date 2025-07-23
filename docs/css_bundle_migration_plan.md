# CSS Bundle Migration Plan - Agency Pages

## Overview

After removing the legacy `main.css` from `login.php` and `base.php`, we discovered that agency pages depend on numerous CSS imports from `main.css`. This document outlines the analysis and migration plan to properly bundle these styles.

## Problem Statement

- **Before**: Agency pages loaded both `main.css` (20+ individual imports) AND bundle files
- **After removing `main.css`**: Agency pages now missing critical styles that were imported via `main.css`
- **Goal**: Migrate necessary styles to appropriate bundles while maintaining visual consistency

## Agency Page CSS Dependencies Analysis

### Critical Agency Page Classes Found

#### 1. Bento Grid System (Dashboard)
```css
/* Core bento grid classes */
.bento-grid
.bento-card
.bento-card-header
.bento-card-content
.bento-card-title
.bento-card-icon
.bento-card-actions

/* Size classes */
.bento-card.size-6x2
.bento-card.size-3x1
.bento-card.size-3x2
.bento-card.size-2x1
.bento-card.size-4x1
.bento-card.size-4x2
.bento-card.size-8x2
.bento-card.size-12x1

/* State classes */
.fade-in
.loading
.primary
.success
.warning
.info
.dark
```

#### 2. Component Classes
```css
/* Card components */
.card
.card-header
.card-body
.card-title
.submission-card
.draft-programs-card
.timeline-card

/* Table components */
.table
.table-responsive
.table-hover
.table-custom
.table-light
.sortable

/* Form components */
.btn
.form-control
.form-select
.form-group
.form-label
.form-text
.required-field

/* UI components */
.badge
.alert
.progress
.progress-bar
.list-group
.list-group-item
```

#### 3. Layout Classes
```css
/* Layout structure */
.container-fluid
.flex-fill
.content-wrapper
.page-content
.min-vh-100

/* Navigation and sections */
.outcomes-section
.programs-section
.initiatives-section
.section-header
```

#### 4. Agency-Specific Classes
```css
/* Program management */
.program-type-indicator
.program-number-preview
.validation-feedback
.view-programs-card-title

/* Timeline and metrics */
.timeline-date-group
.metric-icon

/* Notifications */
.notifications-pagination
.notifications-pagination-info
.notifications-pagination-summary
.notifications-per-page
.notifications-pagination-nav

/* Dashboard specific */
.display-4
.chart-container
.carousel-control-prev
.carousel-control-next
.carousel-card
.initiative-title
.initiative-desc
.initiative-badges
.programs-counter
```

## Required CSS Files from main.css

### Essential Components (CRITICAL)
```css
/* Core layout and grid */
@import 'components/bento-grid.css';          /* ⭐ CRITICAL - Dashboard grid system */
@import 'components/dashboard-cards.css';     /* ⭐ CRITICAL - Card styling */
@import 'pages/agency.css';                   /* ⭐ CRITICAL - Agency page styles */

/* UI Components */
@import 'components/cards.css';               /* Basic card styles */
@import 'components/tables.css';              /* Table styling */
@import 'components/buttons.css';             /* Button variations */
@import 'components/forms.css';               /* Form controls */
@import 'components/badges.css';              /* Status badges */
@import 'components/alerts.css';              /* Alert messages */

/* Layout */
@import 'layout/navigation.css';              /* Navigation styling */
@import 'layout/footer.css';                  /* Footer layout */
```

### Specialized Components
```css
/* Agency-specific features */
@import 'components/rating-indicators.css';   /* Progress ratings */
@import 'components/progress.css';            /* Progress bars */
@import 'components/notifications.css';       /* Notification system */
@import 'components/program-details.css';     /* Program detail views */
@import 'components/outcomes.css';            /* Outcomes interface */

/* Custom overrides */
@import 'custom/agency.css';                  /* Agency customizations */
```

### Page-Specific Styles
```css
/* Timeline and program features */
@import 'components/program-animations.css';
@import 'components/program-history.css';
@import 'components/metric-details.css';
@import 'components/metric-create.css';

/* Mobile and responsive */
@import 'components/mobile-targets-display.css';
@import 'components/table-word-wrap.css';
@import 'components/table-browser-fixes.css';
```

## Migration Strategy

### Phase 1: Create Shared Base CSS

**File**: `assets/css/agency/shared/base.css`

```css
/**
 * Agency Shared Base Styles
 * Core styles needed across all agency pages
 */

/* Essential components for all agency pages */
@import '../../components/bento-grid.css';
@import '../../components/dashboard-cards.css';
@import '../../components/cards.css';
@import '../../components/tables.css';
@import '../../components/buttons.css';
@import '../../components/forms.css';
@import '../../components/badges.css';
@import '../../components/alerts.css';
@import '../../components/progress.css';

/* Layout essentials */
@import '../../layout/navigation.css';
@import '../../layout/footer.css';

/* Agency-specific styles */
@import '../../pages/agency.css';
@import '../../custom/agency.css';

/* Common components */
@import '../../components/rating-indicators.css';
@import '../../components/notifications.css';
```

### Phase 2: Update Existing Page Bundles

#### Dashboard Bundle (`assets/css/agency/dashboard/dashboard.css`)
```css
/* Import shared base */
@import '../shared/base.css';

/* Dashboard-specific components */
@import './bento-grid.css';
@import './initiatives.css';
@import './programs.css';
@import './outcomes.css';
@import './charts.css';
```

#### Programs Bundle (`assets/css/agency/programs/programs.css`)
```css
/* Import shared base */
@import '../shared/base.css';

/* Program-specific components */
@import '../../components/program-details.css';
@import '../../components/enhanced-program-details.css';
@import '../../components/program-animations.css';
@import '../../components/program-history.css';
@import '../../components/table-word-wrap.css';
@import '../../components/mobile-targets-display.css';
```

#### Initiatives Bundle (`assets/css/agency/initiatives/initiatives.css`)
```css
/* Import shared base */
@import '../shared/base.css';

/* Initiative-specific styles */
@import './initiative-view.css';
@import '../../components/metric-details.css';
```

#### Outcomes Bundle (`assets/css/agency/outcomes/outcomes.css`)
```css
/* Import shared base */
@import '../shared/base.css';

/* Outcomes-specific components */
@import '../../components/outcomes.css';
@import '../../components/metric-create.css';
```

### Phase 3: Update Vite Configuration

Ensure all agency bundles are properly configured in `vite.config.js`:

```javascript
export default {
  build: {
    rollupOptions: {
      input: {
        // Existing bundles
        'agency-dashboard': 'assets/css/agency/dashboard/dashboard.css',
        
        // New bundles
        'agency-programs': 'assets/css/agency/programs/programs.css',
        'agency-initiatives': 'assets/css/agency/initiatives/initiatives.css',
        'agency-outcomes': 'assets/css/agency/outcomes/outcomes.css',
        
        // Shared base (optional, if needed standalone)
        'agency-base': 'assets/css/agency/shared/base.css'
      }
    }
  }
}
```

### Phase 4: Update PHP Files

Ensure each agency page sets the correct `$cssBundle` variable:

```php
// Dashboard
$cssBundle = 'agency-dashboard';

// Programs pages
$cssBundle = 'agency-programs';

// Initiatives pages
$cssBundle = 'agency-initiatives';

// Outcomes pages
$cssBundle = 'agency-outcomes';
```

## Implementation Checklist

### ✅ Completed
- [x] Removed legacy `main.css` from `login.php`
- [x] Removed legacy `main.css` from `base.php`
- [x] Analyzed agency page CSS dependencies
- [x] Identified required CSS components

### 🔄 In Progress
- [ ] Create `assets/css/agency/shared/base.css`
- [ ] Update existing dashboard bundle to import shared base
- [ ] Create new bundles for programs, initiatives, outcomes
- [ ] Update Vite configuration
- [ ] Test all agency pages for missing styles

### 📋 Testing Plan
1. **Test each agency page individually:**
   - Dashboard
   - Programs (view, create, edit)
   - Initiatives
   - Outcomes
   - Reports
   - Notifications

2. **Verify CSS loading in network tab:**
   - Only bundle files should load
   - No 404 errors for missing CSS
   - No visual regressions

3. **Cross-browser testing:**
   - Chrome, Firefox, Safari, Edge
   - Mobile responsive layouts

## Expected Network Tab After Migration

For any agency page, the CSS requests should be:

```
✅ Google Fonts (Poppins)
✅ Bootstrap CDN
✅ Font Awesome CDN  
✅ {page}.bundle.css (e.g., agency-dashboard.bundle.css)
❌ NO individual CSS files
❌ NO main.css
```

## Rollback Plan

If issues arise:

1. **Temporary fix**: Re-add `main.css` to `base.php`
2. **Debug**: Identify missing styles in specific pages
3. **Incremental fix**: Add missing imports to bundles
4. **Re-test**: Remove `main.css` again

## Notes

- **Bootstrap classes** (like `.container-fluid`, `.btn`, `.card`) are provided by Bootstrap CDN
- **Font Awesome icons** (like `.fas`, `.fa-*`) are provided by Font Awesome CDN
- **Custom classes** need to be properly imported in bundles
- **Bento grid system** is critical for dashboard layout and must be included
- **Agency-specific overrides** in `custom/agency.css` provide important customizations

## Success Criteria

✅ **Performance**: Reduced CSS requests from 20+ to 4-5 per page
✅ **Maintainability**: Clear bundle structure with shared base
✅ **Visual consistency**: No styling regressions across agency pages
✅ **Scalability**: Easy to add new agency pages with proper styling
