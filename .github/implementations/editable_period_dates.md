## Make Reporting Period Dates Editable

**Problem:** Currently, reporting period dates are automatically calculated based on the selected quarter/year and cannot be edited by users. This lacks flexibility for clients who may need custom date ranges.

**Goal:** Make start and end dates editable while maintaining system integrity and warning about potential overlaps.

**Tasks:**

1. [x] **Update the Frontend Form**:
   - Remove the `readonly` attribute from date input fields
   - Maintain auto-calculation but allow manual overrides
   - Add UI indication when custom dates are used

2. [x] **Update JavaScript Functions**:
   - Add tracking for when dates are manually changed
   - Add validation to detect and warn about overlapping periods
   - Track whether dates are standard or custom

3. [x] **Update Backend Validation**:
   - Create check_period_overlap.php for detecting overlapping periods
   - Update save_period.php and update_period.php to track custom dates
   - Update database queries to include the is_standard_dates flag

4. [x] **Documentation**:
   - Added code comments explaining the date flexibility
   - Added overlap detection and warning system

## How the Editable Dates Work

1. **Date Input Fields**:
   - The date fields are now fully editable (not readonly)
   - They still get auto-calculated when quarter/year changes
   - If the user manually edits dates, they're marked as custom

2. **Overlap Detection**:
   - When saving/updating a period, the system checks for date overlaps
   - If overlaps are found, a warning is shown but users can proceed
   - This provides flexibility while still alerting about potential issues

3. **Database Changes**:
   - The `is_standard_dates` flag tracks whether default or custom dates are used
   - This helps maintain data integrity while allowing flexibility
