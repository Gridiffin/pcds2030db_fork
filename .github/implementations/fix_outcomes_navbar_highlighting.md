# Fix Outcomes Navbar Highlighting

## Problem
The view and edit outcomes pages (`view_outcome.php`) are not properly identified as part of the outcomes section, causing the agency navbar to not highlight the "outcomes" tab when users are on these pages.

## Solution Steps

### 1. Analyze Current Navbar Implementation
- [x] Examine agency navbar structure and highlighting mechanism
- [x] Identify how current page/section detection works
- [x] Check what variables or mechanisms control tab highlighting

### 2. Update View Outcome Page
- [x] Updated agency navigation to include all outcomes-related pages
- [x] Added comprehensive list of outcome pages for proper highlighting
- [x] Tested navigation highlighting logic

### 3. Verify Consistency
- [x] Check other outcomes pages for consistent implementation
- [x] Ensure all outcomes-related pages highlight the outcomes tab
- [x] Test navigation flow and highlighting behavior

## Files Modified
- `app/views/layouts/agency_nav.php` - Updated highlighting logic to include all outcome pages

## Implementation Details
Updated the outcomes tab highlighting condition from a simple OR check to use `in_array()` function with a comprehensive list of all outcome-related page filenames:

- `submit_outcomes.php` (main outcomes page)
- `create_outcome_flexible.php` (flexible outcome creation)
- `create_outcome.php` (classic outcome creation)
- `view_outcome.php` (unified view/edit outcome page)
- `view_outcome_flexible.php` (flexible outcome viewing)
- `edit_outcomes.php` (outcome editing)
- `create_outcomes_detail.php` (outcome detail creation)
- `submit_draft_outcome.php` (draft outcome submission)
- `update_metric_detail.php` (metric detail updates)

## Expected Outcome
✅ **COMPLETED**: When users navigate to any outcome-related page (including view or edit outcome pages), the "outcomes" tab in the agency navbar is properly highlighted, providing clear visual indication of the current section.
