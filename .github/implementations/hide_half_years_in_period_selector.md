# Hide Half Years in Period Selector for Program Editing

## Problem

When users select a period while editing a program in `update_program.php`, the dropdown shows both quarterly periods (Q1-Q4) and half-yearly periods (Half Year 1, Half Year 2). The user wants to hide the half-yearly periods in this context to reduce confusion and focus on quarterly reporting.

## Solution Steps

### 1. ✅ Analyze Current Implementation

- The period selector is implemented in `app/lib/period_selector_edit.php`
- It currently shows all periods from the database query: `SELECT * FROM reporting_periods ORDER BY year DESC, quarter DESC`
- Half-yearly periods are identified by `quarter` values 5 and 6
- The `get_period_display_name()` function handles the display formatting

### 2. ✅ Modify Period Selector Query

- Updated the database query in `period_selector_edit.php` to exclude half-yearly periods
- Changed from `SELECT * FROM reporting_periods` to `SELECT * FROM reporting_periods WHERE quarter NOT IN (5, 6)`
- This only affects the editing context, other period selectors remain unchanged

### 3. ✅ Add Documentation Comments

- Added clear documentation to the period selector component
- Explained the rationale for excluding half-yearly periods
- Maintained code clarity for future developers

### 4. ✅ Verify Other Selectors Unaffected

- Confirmed `period_selector.php` still shows all periods
- Confirmed `period_selector_dashboard.php` still shows all periods
- Change is isolated to the editing context only

### 5. ✅ Update Documentation

- Updated component documentation with clear rationale
- Added comments explaining the filtering behavior
- Implementation is complete and ready for use

## Implementation Details

### Files to Modify

1. `app/lib/period_selector_edit.php` - Main period selector component for editing
2. Possibly add CSS to hide specific options if needed

### Database Query Changes

- ✅ Current: `SELECT * FROM reporting_periods ORDER BY year DESC, quarter DESC`
- ✅ New: `SELECT * FROM reporting_periods WHERE quarter NOT IN (5, 6) ORDER BY year DESC, quarter DESC`

### Benefits

- Cleaner interface for program editing
- Reduces confusion about period types
- Maintains quarterly focus for program submissions
- Easy to reverse if needed

## Testing Checklist

- ✅ Half-yearly periods are hidden in program editing (query filter implemented)
- ✅ Quarterly periods display correctly (confirmed via code review)
- ✅ Period selection functionality preserved (no changes to selection logic)
- ✅ Other period selectors remain unaffected (verified other files unchanged)
- ✅ Code documentation updated (added explanatory comments)
- ✅ Implementation follows existing patterns (consistent with codebase style)

## Ready for User Testing

The implementation is complete and ready for the user to test in their development environment.

## Rollback Plan

If issues arise, simply revert the query change in `period_selector_edit.php` to show all periods again.
