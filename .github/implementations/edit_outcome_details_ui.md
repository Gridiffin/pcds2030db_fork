# Edit Outcome Details (detail_json) UI/Logic Fix

## Problem
- The edit buttons for outcome details are not working as expected.
- The `detail_json` column in `outcomes_details` contains an array of `items`, each with fields like `value`, `description`, and sometimes `label`.
- The edit UI must allow editing all fields in each `items` entry, but does not need to allow editing `layout_type`.

## Solution Plan
- [x] Analyze the structure of `detail_json` and confirm required fields.
- [ ] Update the edit modal/component in `submit_outcomes.php` to:
  - [ ] Load all `items` from `detail_json` for editing.
  - [ ] Render input fields for `value`, `description`, and `label` (if present) for each item.
  - [ ] Allow adding/removing items dynamically.
  - [ ] Save changes back to the database, ensuring all fields are captured.
- [ ] Test the edit functionality for various `detail_json` structures.
- [ ] Clean up any related JS and PHP code for maintainability.

## Notes
- Do not allow editing of `layout_type`.
- Ensure all changes follow project coding standards and are modular.
- Mark each task as complete as you progress.
