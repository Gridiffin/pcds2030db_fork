# Outcomes Module Refactor - Implementation Plan

**Date:** 2025-07-20  
**Task:** Refactor agency-side outcomes module according to best practices

## Context Analysis

### Current Structure
- Location: `app/views/agency/outcomes/`
- Files: `view_outcome.php`, `edit_outcome.php`, `submit_outcomes.php`, `delete_outcome.php`
- Assets: Various CSS/JS files scattered across different directories
- Layout: Uses old header/footer pattern instead of base.php

### Issues Identified
1. **Monolithic Files**: `view_outcome.php` is 612 lines - needs modularization
2. **Old Layout Pattern**: Uses `header.php` instead of modern `base.php` layout
3. **Hardcoded Asset Paths**: Direct asset includes instead of Vite bundling
4. **Mixed Code Structure**: HTML, PHP logic, and inline styles/scripts mixed
5. **Missing Bundle Configuration**: Not configured in `vite.config.js`

### Key Patterns from Bug Tracker
- **Path Resolution**: Use PROJECT_ROOT_PATH with proper app/ prefix
- **Include Order**: config.php → db_connect.php → session.php → functions.php
- **Bundle Naming**: Use bundle names without extensions (e.g., 'outcomes' not 'outcomes.bundle.css')
- **Function Conflicts**: Check existing function names before creating new ones
- **Navbar Overlap**: Add proper body padding for fixed navbar

## Implementation Plan

### Phase 1: Analysis & Setup ✅
- [x] Read bug tracker for common patterns and pitfalls
- [x] Analyze current outcomes module structure
- [x] Check existing assets and dependencies
- [x] Review how dashboard/reports modules were refactored

### Phase 2: File Structure Planning
- [ ] Plan new modular directory structure
- [ ] Identify reusable partials and components
- [ ] Plan asset organization (CSS/JS)
- [ ] Plan Vite entry points

### Phase 3: Asset Refactoring
- [ ] Create modular CSS structure in `assets/css/agency/outcomes/`
- [ ] Create modular JS structure in `assets/js/agency/outcomes/`
- [ ] Configure Vite bundle for outcomes module
- [ ] Build and test asset loading

### Phase 4: PHP File Refactoring
- [ ] Convert main view files to use base.php layout
- [ ] Break down monolithic files into partials
- [ ] Update include paths to use PROJECT_ROOT_PATH
- [ ] Ensure proper include order

### Phase 5: Testing & Validation
- [ ] Test all pages load correctly
- [ ] Verify all assets load via Vite bundles
- [ ] Test functionality (CRUD operations)
- [ ] Check navbar overlap and styling
- [ ] Validate responsive design

### Phase 6: Documentation
- [ ] Update this implementation document with progress
- [ ] Document any bugs found and resolved
- [ ] Update project structure documentation

## Directory Structure Plan

```
assets/
├── css/agency/outcomes/
│   ├── outcomes.css (main import file)
│   ├── base.css (layout and common styles)
│   ├── view.css (view-specific styles)
│   ├── edit.css (edit form styles)
│   ├── submit.css (submit page styles)
│   └── tables.css (dynamic table styles)
├── js/agency/outcomes/
│   ├── outcomes.js (main entry point)
│   ├── view.js (view functionality)
│   ├── edit.js (edit functionality)
│   ├── submit.js (submit functionality)
│   └── chart-manager.js (chart functionality)

app/views/agency/outcomes/
├── outcomes.php (main list view - if needed)
├── view_outcome.php (main view file)
├── edit_outcome.php (main edit file)
├── submit_outcomes.php (main submit file)
├── delete_outcome.php (main delete file)
└── partials/
    ├── view_content.php
    ├── edit_content.php
    ├── submit_content.php
    ├── outcome_table.php
    ├── outcome_charts.php
    └── outcome_actions.php
```

## Status Tracking

- **Started:** 2025-07-20
- **Current Phase:** 1 - Analysis & Setup ✅
- **Next Phase:** 2 - File Structure Planning
- **Estimated Completion:** TBD

## Notes
- Reference dashboard module refactor for patterns
- Follow reports module bundle configuration
- Prevent common path resolution and function conflict issues
- Ensure mobile responsiveness and navbar spacing
