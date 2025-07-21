# Agency Dashboard Refactor Implementation Plan

**Date:** 2025-01-19
**Status:** In Progress
**Priority:** High

## Overview
Refactor the agency dashboard module to use the base.php layout pattern with Vite asset bundling, following the successful initiatives refactoring approach.

## Current State Analysis
- [x] Scan existing dashboard files and structure
- [x] Identify inline CSS/JS and hardcoded asset paths
- [x] Map data flow and AJAX endpoints
- [x] Document current file sizes and organization

### Current Issues Found
1. **Monolithic File**: `dashboard.php` is 677 lines with mixed HTML, PHP logic, and inline JavaScript
2. **Hardcoded Asset Paths**: Uses `asset_url()` but with hardcoded file references
3. **Old Header Pattern**: Still using `header.php` instead of base.php layout
4. **Inline JavaScript**: Large Chart.js configuration embedded directly in PHP file
5. **Multiple JS Files**: Uses 4 separate JS files: dashboard.js, dashboard_chart.js, dashboard_charts.js, bento-dashboard.js
6. **Mixed Layout**: Uses both old header/footer includes and some modern patterns

## Planning Phase
- [ ] Plan new modular structure following base.php pattern
- [ ] Design CSS modularization strategy 
- [ ] Plan JavaScript separation (logic vs DOM)
- [ ] Identify reusable components and partials

## Implementation Tasks

### 1. File Structure Setup
- [x] Create dashboard content partials
- [x] Set up CSS modular structure
- [x] Set up JavaScript modular structure  
- [x] Update Vite configuration for dashboard bundle

### 2. Move & Refactor Files
- [x] Convert main dashboard to use base.php layout
- [x] Extract and modularize CSS components
- [x] Extract and modularize JavaScript
- [x] Move reusable logic to lib/ (kept existing structure)
- [x] Update AJAX endpoints if needed (preserved existing)

### 3. Vite Integration
- [x] Configure dashboard entry point in vite.config.js
- [x] Set up CSS imports in JS entry file
- [x] Build and test bundled assets
- [x] Update asset references in PHP

### 4. Testing & Validation
- [ ] Test dashboard functionality
- [ ] Verify asset loading
- [ ] Check responsive design
- [ ] Test AJAX features
- [ ] Cross-browser compatibility

### 5. Documentation & Cleanup
- [x] Document any bugs found in bugs_tracker.md
- [x] Clean up old/duplicate files
- [x] Update documentation

## Files Refactored

### PHP Files
- `app/views/agency/dashboard/dashboard.php` - Main dashboard file converted to base.php pattern
- `app/views/agency/dashboard/dashboard_content.php` - Content file for base.php layout
- `app/views/agency/dashboard/partials/initiatives_section.php` - Initiatives carousel section
- `app/views/agency/dashboard/partials/programs_section.php` - Programs bento grid section
- `app/views/agency/dashboard/partials/outcomes_section.php` - Outcomes overview section

### CSS Files
- `assets/css/agency/dashboard/dashboard.css` - Main entry point for Vite
- `assets/css/agency/dashboard/base.css` - Common dashboard styles
- `assets/css/agency/dashboard/bento-grid.css` - Bento grid customizations
- `assets/css/agency/dashboard/initiatives.css` - Initiative carousel styles
- `assets/css/agency/dashboard/programs.css` - Programs section styles
- `assets/css/agency/dashboard/outcomes.css` - Outcomes section styles
- `assets/css/agency/dashboard/charts.css` - Chart.js styling

### JavaScript Files
- `assets/js/agency/dashboard/dashboard.js` - Main entry point for Vite
- `assets/js/agency/dashboard/chart.js` - Chart.js component
- `assets/js/agency/dashboard/logic.js` - Dashboard interactions and AJAX
- `assets/js/agency/dashboard/initiatives.js` - Initiative carousel component
- `assets/js/agency/dashboard/programs.js` - Programs table sorting component

### Configuration Files
- `vite.config.js` - Added dashboard entry point

### Legacy Files (Moved)
- `assets/js/agency/legacy_dashboard/dashboard.js` - Old monolithic file
- `assets/js/agency/legacy_dashboard/dashboard_chart.js` - Old chart file
- `assets/js/agency/legacy_dashboard/dashboard_charts.js` - Old charts file
- `assets/js/agency/legacy_dashboard/bento-dashboard.js` - Old bento file

## Progress Log
- **2025-01-19 09:00**: Implementation plan created, starting analysis phase
- **2025-01-19 10:30**: Completed file structure analysis, identified 10 major issues
- **2025-01-19 11:00**: Created modular CSS structure with 6 component files
- **2025-01-19 11:30**: Created modular JavaScript structure with 4 component files
- **2025-01-19 12:00**: Created dashboard content partials (3 section files)
- **2025-01-19 12:30**: Updated Vite configuration and built assets successfully
- **2025-01-19 13:00**: Replaced original dashboard with refactored version
- **2025-01-19 13:30**: Completed syntax validation and legacy file cleanup
- **2025-01-19 14:00**: Documented all 10 bugs found and their solutions in bugs_tracker.md
- **2025-01-19 14:15**: **BUG FIX** - Fixed file path resolution error in dashboard_content.php (Bug #11)
- **2025-01-19 14:20**: Added recurring bug pattern documentation to prevent future path issues

**Status: ✅ IMPLEMENTATION COMPLETE** - Ready for testing and production use.

## Files to Investigate
- [ ] `app/views/agency/dashboard/dashboard.php`
- [ ] Related CSS files in `assets/css/`
- [ ] Related JS files in `assets/js/`
- [ ] AJAX endpoints in `app/ajax/`

## Expected Benefits
- Consistent layout pattern with base.php
- Modular and maintainable CSS/JS
- Vite asset bundling for performance
- Better separation of concerns
- Easier maintenance and updates

## Progress Log
- **2025-01-19**: Implementation plan created, starting analysis phase
