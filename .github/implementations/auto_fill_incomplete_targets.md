# Auto-Fill Incomplete Targets Feature

## Problem Description

When a user is editing a submission on a period that has no existing submission, the form should automatically populate with targets that are not yet completed from previous periods.

## Implementation Plan

### Tasks:

- [x] **Analyze current submission structure and target data**

  - [x] Examine how targets are stored and retrieved
  - [x] Understand the relationship between programs, targets, and submissions
  - [x] Identify incomplete targets logic

- [x] **Create API endpoint for fetching incomplete targets**

  - [x] Create new AJAX endpoint to get incomplete targets for a program
  - [x] Implement logic to identify targets not completed in previous periods
  - [x] Return target data in format compatible with submission form

- [x] **Update edit_submission.js**

  - [x] Add function to fetch incomplete targets when no submission exists
  - [x] Implement auto-fill logic for form fields
  - [x] Handle the case when period has no existing submission

- [x] **Update backend logic**

  - [x] Modify submission loading to include incomplete targets
  - [x] Ensure proper data structure for form population

- [x] **Test the implementation**
  - [x] Test with periods that have no submissions
  - [x] Verify incomplete targets are properly populated
  - [x] Test edge cases and error handling

## Technical Approach

1. When a period with no submission is selected, fetch incomplete targets from previous periods
2. Auto-populate the form with these targets
3. Allow users to modify the auto-filled data
4. Maintain proper validation and submission flow

## Files to Modify:

- `app/ajax/get_incomplete_targets.php` (new)
- `app/views/agency/programs/edit_submission.php`
- `assets/js/agency/edit_submission.js`
- Related target and submission handling files

## ✅ Implementation Complete

### Summary of Changes:

1. **Created new AJAX endpoint** (`app/ajax/get_incomplete_targets.php`):

   - Fetches incomplete targets from previous periods for a program
   - Filters targets with `status_indicator != 'completed'`
   - Returns data in format compatible with submission form
   - Handles duplicate prevention and proper data formatting
   - ✅ Fixed MySQL strict mode error by adding `pt.target_id` to SELECT list

2. **Enhanced edit_submission.js**:

   - Added `fetchIncompleteTargets()` function to retrieve incomplete targets
   - Modified `showNoSubmissionMessage()` to trigger target fetching
   - Enhanced `addTargetRow()` to accept optional target data for auto-filling
   - Added `autoFillIncompleteTargets()` function to populate form with fetched targets
   - Added visual indicators (badges) to show auto-filled targets

3. **Auto-fill Features**:
   - Targets with status `'not_started'`, `'in_progress'`, or `'delayed'` are auto-filled
   - Only targets from previous periods (before current period) are considered
   - Users can edit or remove auto-filled targets as needed
   - Visual feedback shows which targets were auto-filled
   - Toast notification informs users about auto-filled targets

### How It Works:

1. When a user selects a period with no existing submission, the system automatically fetches incomplete targets from previous periods
2. When the user clicks "Add New Submission", the form is pre-populated with these incomplete targets
3. Each auto-filled target shows an "Auto-filled" badge and maintains all its original data (description, status, dates, etc.)
4. Users can modify, add, or remove targets as needed before submitting

### Database Data Available for Testing:

The system has existing test data with incomplete targets:

- Program submissions with targets in `'in_progress'` and `'delayed'` status
- These targets will be automatically carried over to new periods
- Only targets with `'completed'` status are excluded from auto-fill
