# Edit Outcome Detail Modal Improvement

## Problem
- The input fields for Description and Label in the Edit Outcome Detail modal are too small for users to view and edit longer content.
- Users should not be able to delete outcome details.

## Solution
- Replace the Description and Label fields with `<textarea>` elements to provide more space for editing. (Done)
- Keep the Value field as a text input since it is usually short. (Done)
- Remove the delete button from each outcome detail card and from the Edit Outcome Detail modal.
- Remove or disable the `deleteMetricDetail` JavaScript function and any related event handlers.

## Steps
- [x] Identify the modal and the function generating the input fields (`createItemRow`).
- [x] Update the function to use `<textarea>` for Description and Label.
- [x] Adjust styling if necessary for a clean layout.
- [x] Remove the delete button from the UI and modal.
- [x] Remove or disable the `deleteMetricDetail` function and related event handlers.
- [x] Test the modal and outcome details section to ensure usability and appearance.
- [x] Mark this task as complete after verification.

---

**All tasks are now complete.**
