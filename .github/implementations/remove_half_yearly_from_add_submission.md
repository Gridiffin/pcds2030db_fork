# Remove Half-Yearly Selection from Add Submission Page

## Problem

The user requested to remove the half-yearly selection option from the add_submission page dropdown, while keeping it available in other parts of the system.

## Solution Steps

### 1. ✅ Analyze Current Implementation

- The add_submission.php file uses `get_reporting_periods_for_dropdown(true)` to populate the dropdown
- This function returns all periods including half-yearly periods (period_type = 'half')
- The dropdown is rendered in the partial file `add_submission_content.php`

### 2. ✅ Modify Period Query

- Replaced the call to `get_reporting_periods_for_dropdown(true)` with a custom query
- Added WHERE clause to exclude periods with `period_type = 'half'`
- Maintained the same ordering and field structure as the original function
- Applied the same derived fields and display name formatting

### 3. ✅ Implementation Details

- Modified `app/views/agency/programs/add_submission.php` line 47-60
- Custom query: `SELECT period_id, year, period_type, period_number, status FROM reporting_periods WHERE period_type != 'half' ORDER BY year DESC, period_type ASC, period_number DESC`
- Maintained backward compatibility by applying `add_derived_period_fields()` and `get_period_display_name()`

### 4. ✅ Verification

- Half-yearly periods are now excluded from the add_submission dropdown
- Other pages using `get_reporting_periods_for_dropdown()` remain unaffected
- Quarterly periods (Q1-Q4) are still available for selection
- The change is isolated to only the add_submission page

## Files Modified

- `app/views/agency/programs/add_submission.php` - Modified the period query to exclude half-yearly periods

## Testing

- [x] Verify that half-yearly periods no longer appear in the add_submission dropdown
- [x] Confirm that quarterly periods (Q1-Q4) are still available
- [x] Test that other pages using period dropdowns are unaffected
- [x] Verify that existing functionality works correctly

## Notes

- This change only affects the add_submission page
- All other period selectors in the system remain unchanged
- The implementation maintains the same data structure and formatting as the original function
