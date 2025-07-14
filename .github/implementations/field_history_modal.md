# Field-Level Audit History Modal

## Goal
- Add a “Show History” button next to each editable field (e.g., target description, status, etc.) in the target form.
- When clicked, show a modal with the full audit history for that specific field (for that target).

## Steps

- [ ] Update the target form UI to include a “Show History” button/icon next to each editable field.
- [ ] When the button is clicked, trigger a JS function to fetch the audit history for that field and target via AJAX.
- [ ] Create or update a backend AJAX endpoint to return the audit history for a given `target_id` and `field_name`.
- [ ] Display the audit history in a modal (showing old value, new value, who changed it, and when).
- [ ] Style the modal for clarity and usability.
- [ ] Test the feature for all editable fields in the target form.

## Notes
- The modal should be reusable for any field.
- The backend should return a list of changes (old value, new value, user, timestamp) for the requested field and target.
- Use FontAwesome or similar for the history icon. 