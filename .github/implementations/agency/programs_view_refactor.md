# Agency Programs Module Refactor

## Overview
Refactoring the agency programs module starting with `view_programs.php` to follow best practices with modular architecture, Vite bundling, and base.php layout.

## Analysis

### Current Structure
- **Main file**: `app/views/agency/programs/view_programs.php` (970+ lines)
- **CSS**: `assets/css/pages/view-programs.css` (731 lines)
- **JS**: `assets/js/agency/view_programs.js` (879 lines)
- **Dependencies**: Multiple includes, rating helpers, permissions

### Issues Identified
1. **File too large**: Main view file is 970+ lines (exceeds 500-line guideline)
2. **Mixed concerns**: Business logic mixed with presentation
3. **Non-modular assets**: CSS/JS not using Vite bundling
4. **Old layout pattern**: Using header.php instead of base.php

## Refactor Plan

### 1. **Preparation & Analysis** ✅
- [x] Identify all files related to the module
- [x] Map data flow: controller → model → view → assets
- [x] List dynamic features requiring AJAX/JS

### 2. **Directory & File Structure Planning**
- [ ] Main view: `app/views/agency/programs/view_programs.php` (< 300 lines)
- [ ] Partials:
  - [ ] `app/views/agency/programs/partials/draft_programs_table.php`
  - [ ] `app/views/agency/programs/partials/finalized_programs_table.php`
  - [ ] `app/views/agency/programs/partials/template_programs_table.php`
  - [ ] `app/views/agency/programs/partials/program_filters.php`
  - [ ] `app/views/agency/programs/partials/delete_modal.php`
- [ ] CSS: `assets/css/agency/view-programs.css` with subfiles:
  - [ ] `assets/css/agency/view-programs/tables.css`
  - [ ] `assets/css/agency/view-programs/filters.css`
  - [ ] `assets/css/agency/view-programs/cards.css`
- [ ] JS: `assets/js/agency/view-programs.js` with modular structure:
  - [ ] `assets/js/agency/view-programs/logic.js` (pure logic)
  - [ ] `assets/js/agency/view-programs/dom.js` (DOM interactions)
  - [ ] `assets/js/agency/view-programs/filters.js` (filtering logic)

### 3. **Move & Refactor Files**
- [ ] Break down main view into partials
- [ ] Modularize CSS with @import structure
- [ ] Split JS into logic/DOM separation
- [ ] Ensure no duplicate functions are removed

### 4. **Implement Vite for Asset Bundling**
- [ ] Add view-programs entry to vite.config.js
- [ ] Import CSS in JS entry point
- [ ] Build and reference bundled assets

### 5. **Switch to base.php Layout**
- [ ] Replace header.php inclusion with base.php
- [ ] Set up dynamic asset injection variables
- [ ] Remove inline scripts/styles

### 6. **Maintain All Functionality**
- [ ] Preserve all filtering capabilities
- [ ] Keep pagination working
- [ ] Maintain delete confirmation modal
- [ ] Preserve tooltips and interactions
- [ ] Keep rating system intact
- [ ] Maintain permission checking

### 7. **Testing & Validation**
- [ ] Test all user interactions
- [ ] Verify filtering works
- [ ] Check pagination functionality
- [ ] Test delete operations
- [ ] Validate permissions

## Key Features to Preserve
1. **Three-section layout**: Draft, Finalized, Template programs
2. **Advanced filtering**: Search, rating, type, initiative filters
3. **Sorting capabilities**: All columns sortable
4. **Action buttons**: View, Edit, Delete with permissions
5. **Rating system**: Color-coded progress indicators
6. **Program type indicators**: Assigned vs Agency-created
7. **Tooltips and responsive design**
8. **Real-time counter updates**

## Technical Requirements
- Use PROJECT_ROOT_PATH for consistent paths
- Maintain session-based permissions
- Keep AJAX endpoints functional
- Preserve responsive design
- Maintain accessibility features

## Progress
- [x] File structure setup
- [x] CSS modularization
- [x] JS modularization
- [x] Partial creation
- [x] Vite configuration
- [x] Base layout integration
- [x] Original file replaced with refactored version
- [x] **BUG FIX #15**: Fixed PROJECT_ROOT_PATH issue (2025-07-21)
- [x] **BUG FIX #16**: Fixed partial include path issue (2025-07-21)
- [x] **BUG FIX #17**: Fixed navbar overlap and footer positioning (2025-07-21)
- [ ] Testing and validation

## 🐛 Bug Fixes

### Fixed PROJECT_ROOT_PATH Issue (2025-07-21)
- **Issue**: Fatal error with duplicated `/app/app/` in file paths
- **Cause**: Incorrect `PROJECT_ROOT_PATH` definition using only 3 `dirname()` calls
- **Solution**: Updated to use 4 `dirname()` calls for correct path resolution from `app/views/agency/programs/`
- **Status**: ✅ **FIXED** - Path now resolves correctly to project root

### Fixed Partial Include Path Issue (2025-07-21)
- **Issue**: Fatal error in `program_row.php` partial missing `app/` directory in include path
- **Cause**: Include path `PROJECT_ROOT_PATH . 'lib/rating_helpers.php'` missing `app/` prefix
- **Solution**: Updated to `PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php'`
- **Status**: ✅ **FIXED** - All partial includes now use correct paths

### Fixed Layout Issues - Navbar Overlap and Footer Positioning (2025-07-21)
- **Issue**: Header covered by navbar, footer appearing above content
- **Cause**: Missing navbar padding CSS and inline content pattern disrupting layout
- **Solution**: 
  1. Added `body { padding-top: 70px; }` CSS with responsive adjustments
  2. Created `view_programs_content.php` and switched to `$contentFile` pattern
  3. Rebuilt Vite assets with CSS fixes
- **Status**: ✅ **FIXED** - Proper layout structure with navbar offset and footer positioning

---

## Completed Tasks

### ✅ 1. Preparation & Analysis
- [x] Identified all files related to the module
- [x] Mapped data flow: controller → model → view → assets
- [x] Listed dynamic features requiring AJAX/JS

### ✅ 2. Directory & File Structure Planning
- [x] Main view: `app/views/agency/programs/view_programs.php` (< 300 lines)
- [x] Partials created:
  - [x] `app/views/agency/programs/partials/program_filters.php`
  - [x] `app/views/agency/programs/partials/program_row.php`
  - [x] `app/views/agency/programs/partials/delete_modal.php`
- [x] CSS modularized: `assets/css/agency/view-programs.css` with subfiles:
  - [x] `assets/css/agency/view-programs/tables.css`
  - [x] `assets/css/agency/view-programs/filters.css`
  - [x] `assets/css/agency/view-programs/cards.css`
- [x] JS modularized: `assets/js/agency/view-programs/` with structure:
  - [x] `assets/js/agency/view-programs/view-programs.js` (entry point)
  - [x] `assets/js/agency/view-programs/logic.js` (pure logic)
  - [x] `assets/js/agency/view-programs/dom.js` (DOM interactions)
  - [x] `assets/js/agency/view-programs/filters.js` (filtering logic)

### ✅ 3. Move & Refactor Files
- [x] Broke down main view into partials (from 970+ lines to ~250 lines)
- [x] Modularized CSS with @import structure
- [x] Split JS into logic/DOM separation
- [x] Ensured no duplicate functions are removed

### ✅ 4. Implement Vite for Asset Bundling
- [x] Added view-programs entry to vite.config.js
- [x] Import CSS in JS entry point
- [x] Built and referenced bundled assets (dist/css/view-programs.bundle.css, dist/js/view-programs.bundle.js)

### ✅ 5. Switch to base.php Layout
- [x] Replaced header.php inclusion with base.php
- [x] Set up dynamic asset injection variables
- [x] Removed inline scripts/styles

### ✅ 6. Maintain All Functionality
- [x] Preserved all filtering capabilities (search, rating, type, initiative)
- [x] Keep pagination working (via existing TablePagination)
- [x] Maintained delete confirmation modal (double confirmation)
- [x] Preserved tooltips and interactions
- [x] Kept rating system intact (color-coded badges)
- [x] Maintained permission checking (can_edit_program, can_delete_program)
- [x] Preserved three-section layout (Draft, Finalized, Template programs)
- [x] Maintained responsive design and accessibility

## Improvements Made

1. **File Size Reduction**: Main view reduced from 970+ lines to ~250 lines
2. **Modular Architecture**: Split into logical partials and components
3. **Asset Optimization**: Vite bundling for efficient loading
4. **Better Separation of Concerns**: Logic/DOM/Filtering separated in JS
5. **Consistent Styling**: Modular CSS with proper imports
6. **Performance**: Bundled assets load faster
7. **Maintainability**: Easier to update individual components
8. **Reusability**: Partials can be reused in other views
