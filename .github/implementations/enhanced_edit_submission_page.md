# Enhanced Edit Submission Page: Prevent Target Duplication and Ensure Accurate Updates

## Problem
- Targets were being duplicated on every save because the backend matched targets by array index, not by a unique identifier.
- Audit log and DB updates were not reliably tracking which targets were new, updated, or deleted.

## Solution
- Use a unique `target_id` for each target.
- Frontend: Always include a hidden `target_id` field for each target row (if present) and serialize it in the JSON sent to the backend.
- Backend: Use `target_id` to match, update, insert, or delete targets as appropriate.

---

## TODO List

- [x] Update frontend JS to include and serialize `target_id` for each target row.
- [x] Update backend PHP to use `target_id` for matching, updating, inserting, and deleting targets.
- [ ] Test the full edit/save flow to ensure no duplicates, correct updates, and proper audit logging.
- [ ] Clean up any test data/files after confirming correct behavior. 