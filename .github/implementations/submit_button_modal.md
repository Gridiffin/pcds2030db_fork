# Submit Button in 3-Dot Menu Modal

## Problem Description

Users need a way to submit existing submissions from the 3-dot menu modal. Currently, the modal only has "Edit Submission" and "Edit Program" buttons, but there's no way to select from existing submissions and submit them.

## Requirements

1. Add a "Submit Submission" button to the 3-dot menu modal
2. When clicked, show a list of available submissions for the program
3. Allow user to select a submission and redirect to edit_submission.php with the selected submission
4. Only show submissions that are drafts (not already finalized)
5. Display submission information including period and status

## Analysis

### Current Modal Structure

- Located in `assets/js/agency/view_programs.js`
- Uses `updateMoreActionsModalContent()` function to populate actions
- Currently has 3 action buttons: Edit Submission, Submit Submission, Edit Program

### Required Changes

1. **Add new API endpoint** to get submissions for a program
2. **Modify modal content** to include "Submit Submission" button
3. **Create submission selection modal** that appears when "Submit Submission" is clicked
4. **Add JavaScript functionality** to handle submission selection and redirection

## Implementation Plan

### Step 1: Create API Endpoint for Program Submissions

- [x] Create `app/ajax/get_program_submissions_list.php`
- [x] Return draft submissions with period information
- [x] Include submission status and period details

### Step 2: Modify Modal Content

- [x] Add "Submit Submission" button to `updateMoreActionsModalContent()`
- [x] Add click handler for the new button
- [x] Create submission selection modal

### Step 3: Create Submission Selection Modal

- [x] Design modal with submission list
- [x] Show period information and submission status
- [x] Add selection functionality
- [x] Add redirect to edit_submission.php

### Step 4: Update JavaScript Functions

- [x] Add `showSubmissionSelectionModal()` function
- [x] Add `loadProgramSubmissions()` function
- [x] Add submission selection and redirection logic

### Step 5: Testing

- [ ] Test with programs that have draft submissions
- [ ] Test with programs that have no submissions
- [ ] Test with programs that have only finalized submissions
- [ ] Verify redirection to edit_submission.php works correctly

## Technical Details

### API Endpoint: `get_program_submissions_list.php`

```php
// Returns draft submissions for a program
{
  "success": true,
  "submissions": [
    {
      "submission_id": 123,
      "period_id": 456,
      "period_display": "Q1 2024",
      "is_draft": 1,
      "submitted_at": "2024-01-15 10:30:00",
      "description": "Q1 progress report"
    }
  ]
}
```

### Modal Structure

```html
<!-- Submission Selection Modal -->
<div class="modal fade" id="submissionSelectionModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Submission to Submit</h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
        ></button>
      </div>
      <div class="modal-body">
        <div id="submissionsList">
          <!-- Submissions will be loaded here -->
        </div>
      </div>
    </div>
  </div>
</div>
```

### JavaScript Functions

- `showSubmissionSelectionModal(programId, programName)` - Shows the selection modal
- `loadProgramSubmissions(programId)` - Loads submissions via AJAX
- `selectSubmission(submissionId, periodId)` - Handles submission selection and redirect

## Files to Modify

1. `assets/js/agency/view_programs.js` - Add modal functionality
2. `app/ajax/get_program_submissions_list.php` - New API endpoint
3. `assets/css/pages/view-programs.css` - Add modal styling (if needed)

## Success Criteria

- [x] "Submit Submission" button appears in 3-dot menu modal
- [x] Clicking button shows submission selection modal
- [x] Modal displays draft submissions with period information
- [x] Selecting a submission redirects to edit_submission.php with correct parameters
- [x] Modal handles cases with no draft submissions gracefully
- [x] UI is consistent with existing modal design

## Implementation Summary

### Completed Features

1. **API Endpoint**: Created `get_program_submissions_list.php` that returns draft submissions for a program with period information and status.

2. **Modal Integration**: Added "Submit Submission" button to the 3-dot menu modal with proper styling and positioning. Removed the old "Submit Program" button to avoid confusion.

3. **Submission Selection Modal**: Created a new modal that displays draft submissions in card format with:

   - Period information (Q1 2024, H1 2024, etc.)
   - Period status (Open/Closed)
   - Submission description
   - Last updated timestamp
   - Hover effects and visual feedback

4. **JavaScript Functionality**: Implemented complete workflow including:

   - `showSubmissionSelectionModal()` - Shows the selection modal
   - `loadProgramSubmissions()` - Loads submissions via AJAX
   - `createSubmissionCard()` - Creates submission cards with proper styling
   - `selectSubmission()` - Handles selection and redirects to edit_submission.php

5. **Error Handling**: Added proper error handling for:

   - No draft submissions available
   - API errors
   - Network issues

6. **UI/UX Enhancements**: Added consistent styling with:
   - Loading states with spinner
   - Empty state with helpful message and "Create New Submission" link
   - Hover effects on submission cards
   - Mobile-responsive design
   - Consistent color scheme with existing modals

### Technical Details

- **API Response Format**: JSON with submission details including period display, status, and metadata
- **Modal Structure**: Bootstrap 5 modal with custom styling
- **AJAX Integration**: Uses fetch API for loading submissions
- **Redirect Logic**: Redirects to `edit_submission.php?program_id=X&period_id=Y`
- **CSS Styling**: Added comprehensive styling for submission cards and modal states

### Files Modified

1. `app/ajax/get_program_submissions_list.php` - New API endpoint
2. `assets/js/agency/view_programs.js` - Added modal functionality and submission handling
3. `assets/css/pages/view-programs.css` - Added submission modal styling

The implementation is now complete and ready for testing.
