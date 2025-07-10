# Focal-Only Feature: Finalize/Revert Any Submission for a Program

## Problem
Focal users need the ability to finalize or revert (unsubmit) any submission for a program, not just the latest or current period. This requires a UI for selecting submissions and backend logic to update their status.

## Solution Plan

### 1. UI/UX Changes (Program Details Page)
- [ ] Display a table of all submissions for the program (period, status, date, etc.)
- [ ] Show this table only for focal users (role check)
- [ ] For each submission, show action buttons:
  - [ ] "Finalize" if the submission is a draft
  - [ ] "Revert" (Unsubmit) if the submission is finalized
- [ ] Add confirmation dialogs for both actions
- [ ] Update the table after action (AJAX or reload)

### 2. Backend Logic
- [ ] Add endpoints or controller logic to:
  - [ ] Finalize a submission (set is_draft=0, update submission_date)
  - [ ] Revert a submission (set is_draft=1, update content_json as needed)
  - [ ] Permission check: Only focal users can use these endpoints
  - [ ] Audit log all actions
- [ ] Ensure cascading logic is respected (if needed)
- [x] Fix backend logic in save_submission.php: Previously, all submissions were saved as draft due to hardcoded `$is_draft = true`. Now, the script checks for the `finalize_submission` POST variable and sets `$is_draft = false; $is_submitted = true;` when finalizing. This enables focal users to actually finalize submissions from the edit page.

### 3. Integration
- [ ] Wire up UI buttons to backend via AJAX (preferred) or form POST
- [ ] Show success/error messages and update UI accordingly

### 4. Testing
- [ ] Test with multiple periods, edge cases (already finalized, already draft)
- [ ] Test permission checks (only focal can see/use)
- [ ] Test audit log entries
- [ ] Update documentation

## Status
- [x] Planning (this file)
- [ ] UI implementation
- [ ] Backend implementation
- [ ] Testing & documentation

---

This feature will provide focal users with a powerful tool to manage program submissions across all periods, improving flexibility and control. 