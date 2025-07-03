# Carry Over Incomplete Targets to Next Period

## Problem
Currently, targets are not automatically carried over to the next period if they are not completed. The user wants targets with target_status not 'completed' to be pre-filled in the new period's submission.

## Steps
- [x] 1. Identify where a new submission is being created for a program and period in update_program.php.
- [x] 2. If there is no submission for the selected period, fetch the latest submission from the previous period for the same program.
- [x] 3. Extract all targets from the previous period's content_json where target_status != 'completed'.
- [x] 4. Pre-fill the targets array for the new submission with these carried-over targets.
- [x] 5. Test the flow to ensure only incomplete targets are carried over and the user can edit/remove them as needed.
- [x] 6. Mark this implementation as complete in this file when done. 