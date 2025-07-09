# Program Timeline Logic Rewrite

## Goal
Rewrite the entire logic behind a program's own timeline (start_date, end_date) to ensure robust, error-free, and user-friendly handling of date fields.

## Problems Addressed
- MySQL errors due to invalid date formats (e.g., '2025' instead of 'YYYY-MM-DD')
- Inconsistent normalization and validation across code paths
- User confusion about accepted date formats

## Implementation Steps

- [x] **Frontend: Enforce Full Date Input**
  - Only allow `YYYY-MM-DD` format in all forms and API calls.
  - Add client-side validation and clear error messages.
  - Remove or disable any JS that allows year-only or year-month input.

- [x] **Backend: Strict Validation**
  - Accept only `YYYY-MM-DD` format for `start_date` and `end_date`.
  - If the input is not a valid date, reject it and return a clear error.
  - Remove normalization logic that tries to convert year or year-month to a full date.
  - Apply this validation in all program creation, update, draft, and wizard functions.
  - **FIXED**: Added validation to `app/api/program_submissions.php` which was bypassing validation.

- [x] **Database: Schema Consistency**
  - Confirm that the `programs` table columns for `start_date` and `end_date` are of type `DATE` and nullable.

- [x] **Error Handling and Documentation**
  - Update error messages to clearly state the required format (`YYYY-MM-DD`).
  - Update documentation and help text in the UI to match the new requirements.

- [x] **Testing**
  - Test all program creation and update flows (including drafts and wizards) to ensure only valid dates are accepted and stored.
  - **FIXED**: Discovered and resolved bind parameter mismatch in `create_simple_program` function that was causing MySQL errors.

---

**All steps completed successfully. The program timeline logic has been completely rewritten and is now robust and error-free.** 