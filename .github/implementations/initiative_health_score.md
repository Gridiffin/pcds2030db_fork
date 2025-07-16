# Initiative Health Score Calculation Modification

## Problem Statement
We need to review and potentially modify the logic for calculating and displaying the initiative health score in the 'view initiative' feature.

## Plan & TODOs

- [ ] Identify all files and code paths involved in the initiative health score calculation and display (backend, frontend, AJAX/API, CSS if any)
- [ ] Summarize the current health score calculation logic and data flow
- [ ] Gather user requirements for the desired change
- [ ] Update the calculation logic as per new requirements
- [ ] Update the frontend to reflect any changes in display or logic
- [ ] Ensure all related files (JS, PHP, CSS) are updated and consistent
- [ ] Test the updated feature for correctness and performance
- [ ] Mark each task as complete in this file as we progress 
- [x] Switch health score calculation from using program 'rating' to using program 'status' in the programs table

---

### Health Score Calculation (Updated)

The initiative health score is now calculated based on the 'status' field in the programs table:

| Status      | Health Score |
|-------------|--------------|
| completed   | 100          |
| active      | 75           |
| on_hold     | 50           |
| delayed     | 25           |
| cancelled   | 10           |

Legacy status values are normalized to these categories. The health score is the average of all program statuses under the initiative.

The popover and UI have been updated to reflect this logic. 