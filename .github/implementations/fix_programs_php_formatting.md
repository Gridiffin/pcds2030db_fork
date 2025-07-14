# Fix Programs.php HTML/PHP Formatting Issue

## Problem Description

- Line 269-270 in `app/views/admin/programs/programs.php` has improper formatting and PHP warning issues
- Line 479 in the submitted programs section shows similar data output patterns
- PHP closing tag `?>` was immediately followed by HTML `<tr>` tag without proper spacing
- **CRITICAL ISSUE DISCOVERED**: User reported seeing output due to `display_errors` being enabled
- The issue was `$program['owner_agency_id']` field doesn't exist in the database query result
- The SQL query selects `agency_id` but PHP code was trying to access `owner_agency_id`
- This caused PHP "Undefined array key" warnings to appear when `display_errors` is enabled

## Root Cause Analysis

- The `get_admin_programs_list()` function selects `p.agency_id` in the SQL query
- However, the HTML template was trying to access `$program['owner_agency_id']`
- This field mismatch caused PHP warnings that were visible due to `display_errors = 1` in config
- The warnings appeared as text output in the HTML, making it look like broken attributes

## Solution Steps

### Step 1: Fix PHP/HTML formatting on line 269

- [x] Add proper indentation and spacing between PHP closing tag and HTML
- [x] Ensure consistent code formatting with the rest of the file

### Step 2: Review surrounding code for similar issues

- [x] Check for other formatting inconsistencies in the same file (both unsubmitted and submitted sections)
- [x] Ensure proper indentation throughout the file
- [x] Verified both line 271 (unsubmitted programs) and line 479 (submitted programs) sections

### Step 3: Fix the undefined array key warning

- [x] Changed `$program['owner_agency_id']` to `$program['agency_id']` to match the SQL query
- [x] Added null coalescing operator (`?? ''`) for safety
- [x] Fixed both unsubmitted and submitted programs sections

### Step 4: Test the fix

- [x] Verify the page loads correctly after the fix
- [x] Check for any PHP errors or warnings
- [x] Confirm no more undefined array key warnings appear

## Implementation Details

- Fixed the formatting issue around line 269-270 in both unsubmitted and submitted programs sections
- **MAIN FIX**: Corrected field name mismatch from `owner_agency_id` to `agency_id`
- Added null coalescing operators for both `agency_id` and `initiative_id` fields for safety
- Maintained consistent indentation with the rest of the file
- Followed established coding standards for PHP/HTML mixed code
- Eliminated PHP warnings that were appearing due to `display_errors` being enabled
- Both sections (unsubmitted programs around line 271 and submitted programs around line 479) now work without warnings
