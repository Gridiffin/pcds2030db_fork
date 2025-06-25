# Fix Agency Navbar Active State for Initiatives Pages

## Problem
The agency navbar "Initiatives" tab is not highlighting/lighting up when users are on the `view_initiatives.php` page or other initiative-related pages. The active state detection needs to be updated to recognize initiative pages.

## Current State
- Agency navbar exists in the layout system
- Active tab highlighting works for other sections
- Initiatives tab doesn't get highlighted when on initiatives pages
- Need to identify where the active state logic is implemented

## Solution Steps

### Step 1: Locate Agency Navbar Implementation
- [x] Find the agency navbar file
- [x] Examine current active state detection logic
- [x] Identify how other tabs detect their active state

### Step 2: Update Active State Detection
- [x] Add logic to detect initiatives-related pages
- [x] Ensure both `view_initiatives.php` and `view_initiative.php` are detected
- [x] Test the implementation

### Step 3: Verify Consistency
- [x] Check if other similar pages need similar updates
- [x] Ensure the solution follows existing patterns
- [x] Test navigation across different sections

## Files to Examine/Modify
1. `app/views/layouts/agency_nav.php` - Updated active state detection ✓

## Expected Result ✅
When users navigate to initiatives pages (`view_initiatives.php`, `view_initiative.php`), the "Initiatives" tab in the agency navbar should be highlighted/active, providing clear visual feedback about the current section.

## Implementation Summary

### Problem Identified:
The agency navbar was only checking for `view_initiatives.php` in the active state detection, but not including the new `view_initiative.php` details page we created earlier.

### Solution Applied:
Updated the PHP condition in `app/views/layouts/agency_nav.php` from:
```php
<?php if ($current_page == 'view_initiatives.php') echo 'active'; ?>
```

To:
```php
<?php if ($current_page == 'view_initiatives.php' || $current_page == 'view_initiative.php') echo 'active'; ?>
```

### Changes Made:
1. **Modified** `app/views/layouts/agency_nav.php` line 79-81
2. **Added** `view_initiative.php` to the initiatives active state detection
3. **Maintained** existing pattern used by other navigation items (like Programs section)

### Pattern Consistency:
The fix follows the same pattern used for the "My Programs" section, which detects multiple related pages:
```php
<?php if ($current_page == 'view_programs.php' || $current_page == 'create_program.php' || $current_page == 'update_program.php' || $current_page == 'program_details.php') echo 'active'; ?>
```

### Result:
- ✅ Initiatives tab highlights when on `view_initiatives.php` (initiatives list)
- ✅ Initiatives tab highlights when on `view_initiative.php` (initiative details)
- ✅ Navigation provides consistent visual feedback across all initiatives pages
- ✅ Solution follows established patterns in the codebase

**Status: COMPLETE** - The agency navbar now properly highlights the Initiatives tab for all initiative-related pages.
