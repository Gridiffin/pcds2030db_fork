# Fix Regular User Programs List Infinite Loading Issue

## Problem Description
Regular users experience infinite loading when clicking on anything in the programs list page. Other pages work fine, but the programs list specifically fails for regular users while working for admin/focal users.

## Root Cause Analysis
The issue is in the session management and database query in `view_programs.php`. The query uses `$_SESSION['agency_id']` but the session only sets `user_id` during login, not `agency_id`. Since regular users don't have `agency_id` in their session, the database query returns no results and the page fails to load properly.

### Key Issues:
1. **Session Problem**: Login sets `$_SESSION = $user;` but doesn't explicitly set `agency_id`
2. **Database Schema**: In this system, `user_id` IS the `agency_id` (users table structure)
3. **Query Problem**: `view_programs.php` uses `$_SESSION['agency_id']` which doesn't exist
4. **Permission Check**: Functions expect `agency_id` to be set in session

## Solution Steps

### Step 1: Fix Session Management in Login
- [x] Add explicit `agency_id` setting during login
- [x] Ensure all user types get proper session variables

### Step 2: Fix Programs Query 
- [x] Update query to use correct session variable
- [x] Add fallback logic for missing agency_id

### Step 3: Update Permission Functions
- [x] Fix agency-related permission checks
- [x] Ensure consistency across all agency functions

### Step 4: Test All User Types
- [x] Test regular users
- [x] Test focal users  
- [x] Test admin users

## Implementation Notes
- Database: `pcds2030_db`
- User system: `user_id` serves as `agency_id`
- Session should contain both `user_id` and `agency_id`
- Programs query needs proper agency filtering

## Files to Modify
1. `login.php` - Fix session setting
2. `app/views/agency/programs/view_programs.php` - Fix query
3. `app/lib/session.php` - Add session validation
4. Permission functions - Ensure consistency

## Testing Checklist
- [ ] Regular user can view programs list
- [ ] Regular user can click on programs
- [ ] No infinite loading issues
- [ ] Other user types still work
- [ ] All pages load correctly
