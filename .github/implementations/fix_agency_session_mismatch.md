# Fix Agency Session Mismatch Issue

## Problem Description
- Users with role "agency" experience infinite loading when clicking buttons in programs list
- **Root cause 1**: `get_agency_id()` function returns `user_id` instead of `agency_id`
- **Root cause 2**: Circular dependency between `can_edit_program()` and `can_edit_program_with_user_restrictions()`
- This causes infinite recursion and memory exhaustion errors

## Database Analysis
- Users table: `user_id` ≠ `agency_id` for agency users
- Example: user_id=3 has agency_id=1, user_id=4 has agency_id=1, user_id=6 has agency_id=2
- Login correctly sets `$_SESSION['agency_id']` from user table
- But `get_agency_id()` function returns `$_SESSION['user_id']` instead

## Recursion Analysis
- `can_edit_program()` calls `can_edit_program_with_user_restrictions()`
- `can_edit_program_with_user_restrictions()` calls `can_edit_program()`
- This creates infinite loop causing memory exhaustion

## Solution Steps

### 1. ✅ Fix get_agency_id() function in core.php
- Change return value from `$_SESSION['user_id']` to `$_SESSION['agency_id']`

### 2. ✅ Fix circular dependency causing infinite recursion
- Created `can_edit_program_agency_level()` function for agency-level checks only
- Modified `can_edit_program_with_user_restrictions()` to use agency-level function
- This breaks the circular dependency between the two functions

### 3. ✅ Fix incorrect session variable usage across agency views
- Fixed initiatives/view_initiatives.php
- Fixed initiatives/view_initiative.php  
- Fixed initiatives/initiatives.php
- Fixed dashboard/dashboard.php

### 3. ⏳ Test with agency role users
- Test simple programs page
- Test create new program functionality  
- Test view details functionality

### 4. ⏳ Verify database queries work correctly
- Ensure programs are retrieved for correct agency_id
- Check program_agency_assignments table queries

### 5. ⏳ Clean up debug files
- Remove test files after verification
