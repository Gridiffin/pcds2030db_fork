# Feature: Show Latest Program Submission by Reporting Period in Program Details

## Problem
Currently, the Program Details page only shows the latest submission overall, regardless of reporting period. If a user submits for Q3, then later for Q4, only the Q4 submission is shown—even when viewing Q3. The page should show the latest submission for each reporting period.

## Solution Plan

- [x] **Analyze Current Backend Logic**
  - Review how `get_program_details` and related functions fetch and structure submissions.
  - Identify where the latest submission is selected (currently not grouped by period).

- [x] **Update Backend Logic**
  - Refactor `get_program_details` to group submissions by `period_id` and select the latest submission for each period.
  - Return a new array (e.g., `latest_submissions_by_period`) mapping each period to its latest submission.

- [x] **Update AJAX/API Endpoints**
  - Ensure endpoints like `get_program_submission.php` can fetch the latest submission for a given period.
  - Update or add endpoints as needed to support frontend requirements.

- [x] **Update Frontend (Program Details Page)**
  - Update the view to display all periods and their latest submissions, not just the latest overall.
  - Group/display submissions by period (e.g., in a table or accordion).

- [ ] **Test the Implementation**
  - Test with multiple submissions across different periods.
  - Ensure the correct submission is shown for each period.
  - Check for regressions in other program-related features.

- [ ] **Documentation & Cleanup**
  - Update documentation to reflect the new logic.
  - Remove or update any test files as needed.

---

## Notes
- Ensure all code changes follow the project's coding standards and are well-documented.
- Use parameterized queries for all database operations.
- Reference all related files (backend, frontend, styles) and update as needed.
- Mark each task as complete as you progress. 