# Attachment Upload Fix: Unknown column 'program_id' in 'field list'

## Problem
- When uploading attachments in `save_submission.php`, the code tries to insert into the `program_attachments` table using a `program_id` column.
- The current schema for `program_attachments` does **not** have a `program_id` column; it uses `submission_id` instead.
- This causes the error: `Unknown column 'program_id' in 'field list'`.

## Solution
- Update the SQL and bind parameters in `save_submission.php` to use `submission_id` instead of `program_id` when inserting into `program_attachments`.

## Implementation Steps

- [x] 1. Document the problem and solution in this file.
- [ ] 2. Update the SQL insert statement in `save_submission.php` to use `submission_id` instead of `program_id` for attachments.
- [ ] 3. Update the bind parameters to match the new SQL statement.
- [ ] 4. Test the attachment upload to confirm the error is resolved.
- [ ] 5. Mark this checklist as complete and remove this file after successful testing. 