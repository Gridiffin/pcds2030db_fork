# Quarter Selection Feature

## Overview
The Quarter Selection feature allows users to explicitly choose a specific reporting quarter (period) when creating a new program. This replaces the previous automatic quarter assignment logic that would automatically assign new programs to the most recent open quarter.

## User Interface
- A dropdown menu has been added to Step 1 of the program creation wizard.
- The dropdown displays all available reporting quarters, with open quarters highlighted.
- Open quarters are visually distinguished using color and styling.
- A default open quarter is auto-selected when available.

## Technical Implementation

### Database
- Uses the existing `reporting_periods` table for quarter data.
- Links to `program_submissions` table via the `period_id` column.

### Backend Components
- `get_reporting_periods_for_dropdown($include_inactive = false)` - Returns formatted reporting periods for the dropdown.
- Added period_id validation in:
  - `create_wizard_program_draft($data)`
  - `update_wizard_program_draft($program_id, $data)`

### Validation
- Client-side validation ensures a quarter is selected before form submission.
- Server-side validation confirms the period exists in the database.

### Form Integration
- The selected period appears in the form review summary before submission.
- The period selection is preserved during auto-save and page reloads.

## How to Use
1. Navigate to the "Create New Program" form.
2. In Step 1, locate the "Reporting Quarter" dropdown.
3. Select the appropriate reporting quarter for the program submission.
4. Complete the rest of the form and submit.

## Administration
Administrators can manage reporting periods through the admin interface:
- Create new quarters
- Set quarters as open or closed
- View which programs are associated with each quarter

## Benefits
- Gives users more control over their program submissions.
- Provides flexibility in assigning programs to specific reporting periods.
- Improves clarity about which reporting period a program is being submitted for.
