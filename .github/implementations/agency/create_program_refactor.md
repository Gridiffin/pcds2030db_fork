# Create Program Module Refactor Plan

## Current State Analysis

The create program module currently has several issues that need to be addressed:

1. Mixed concerns in main PHP file (449 lines):
   - Database queries mixed with view logic
   - Inline JavaScript in PHP file
   - No separation between content and layout
   - Direct database operations in view

2. Asset organization:
   - JavaScript not properly modularized (logic mixed with DOM)
   - No dedicated CSS file/module
   - Inline styles in HTML
   - Not using Vite for asset bundling

3. File structure issues:
   - Not following modern base.php layout pattern
   - Missing proper partials organization
   - Missing unit tests
   - No separation of AJAX endpoints

## Refactoring Plan

### 1. File Structure

```
app/
  views/
    agency/
      programs/
        create_program.php (main entry)
        partials/
          create_program_content.php (main content)
          program_form.php (form partial)
          timeline_section.php (timeline card)
          permissions_section.php (permissions card)
          info_section.php (info card)
  ajax/
    agency/
      check_program_number.php (moved from root ajax)
  lib/
    agencies/
      program_validation.php (new validation functions)

assets/
  css/
    agency/
      programs/
        create.css (main styles)
        form.css (form specific styles)
        timeline.css (timeline specific)
        permissions.css (permissions specific)
  js/
    agency/
      programs/
        create.js (main entry)
        createLogic.js (business logic)
        formValidation.js (validation)
        userPermissions.js (permissions handling)

tests/
  agency/
    programs/
      createLogic.test.js
      formValidation.test.js
```

### 2. Database & Logic Separation

1. Move all database operations to lib/agencies/programs.php
2. Create validation functions in new program_validation.php
3. Centralize AJAX endpoints in app/ajax/agency/
4. Use proper error handling and validation

### 3. Asset Bundling with Vite

1. Create dedicated CSS modules
2. Modularize JavaScript into logical components
3. Update vite.config.js to include program creation
4. Use proper asset loading in base layout

### 4. Modern Layout Integration

1. Convert to use base.php layout
2. Create proper content partials
3. Remove inline styles/scripts
4. Use proper asset injection

### 5. JavaScript Modernization

1. Convert to ES6 modules
2. Separate logic from DOM manipulation
3. Add proper error handling
4. Implement proper form validation

### 6. Testing Implementation

1. Add unit tests for validation logic
2. Add unit tests for form handling
3. Add integration tests for AJAX
4. Document testing procedures

## Implementation Steps

1. [x] Create new directory structure
2. [x] Move and refactor database operations
3. [x] Create modular CSS files
4. [x] Modularize JavaScript
5. [x] Create content partials
6. [x] Update main view to use base layout
7. [x] Implement Vite bundling
8. [x] Add unit tests
9. [ ] Test all functionality
10. [x] Document changes

## Potential Issues to Watch

1. Path resolution for includes (common bug from past refactors)
2. Navbar overlap with content (recurring issue)
3. Asset bundling path issues
4. Form validation state preservation
5. AJAX endpoint availability during refactor

## Success Criteria

1. All files under 300 lines
2. No inline JavaScript/CSS
3. Proper separation of concerns
4. All tests passing
5. No regression in functionality
6. Proper error handling
7. Maintainable and documented code

## Notes

- Follow established patterns from recently refactored modules
- Maintain all existing functionality
- Ensure proper error handling
- Keep backward compatibility
- Document all changes 