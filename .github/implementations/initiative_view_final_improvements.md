# Initiative View Final Improvements

## Issues to Address

### 1. Remove Hardcoded "10 years"
- [x] Fix hardcoded "10 years completed" text
- [x] Make timeline calculation dynamic based on actual initiative dates
- [x] Ensure it works for any initiative, not just initiative 31

### 2. Center Progress Percentage
- [x] Center the progress percentage in the timeline card (user removed progress bar)
- [x] Update CSS to properly center content without progress bar

### 3. Add Recent Activity Feed
- [x] Create new "Recent Activity Feed" section
- [x] Display latest program submissions related to the initiative
- [x] Show program names, agencies, and submission dates
- [x] Style consistently with existing design

### 4. Remove Quick Actions Section
- [x] Remove the entire Quick Actions section from sidebar

## Implementation Notes
- User has already removed the progress bar from timeline card
- Need to update CSS to center the percentage properly
- Recent Activity Feed should show program submissions/outcomes
- All timeline calculations should be dynamic

## Files Modified
- `app/views/agency/initiatives/view_initiative.php` ✅
- `assets/css/pages/initiative-view.css` (needs update for centering)

## Completed Tasks
- ✅ Fixed hardcoded timeline text to be dynamic
- ✅ Added Recent Activity Feed with database queries
- ✅ Removed Quick Actions section
- ⏳ Need to center progress percentage properly
