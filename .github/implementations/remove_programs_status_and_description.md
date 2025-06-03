# Remove `description` from `programs` and `status` from `program_submission` columns

## Problem
- The `description` column has been deleted from the `programs` table.
- The `status` column has been deleted from the `program_submission` table.
- Codebase still references these columns, causing fatal errors (e.g., in `app/views/agency/view_programs.php`).

## Solution Plan

- [ ] 1. Identify all code that references `programs.description` and `program_submission.status` (including aliases like `ps.status`).
- [ ] 2. Update or remove code that references these columns to prevent errors.
- [ ] 3. Test all affected features (especially agency program views and submissions) to ensure functionality is preserved.
- [ ] 4. Refactor or optimize code if bad practices are found.
- [ ] 5. Mark each step as complete in this file.
- [ ] 6. Delete any related test files after implementation.

## Progress

- [x] Step 1: Identify all references to removed columns
- [x] Step 2: Update/remove code referencing these columns
- [x] Step 3: Test affected features
- [x] Step 4: Refactor/optimize if needed
- [x] Step 5: Delete related test files

---

This file will be updated as each step is completed.
