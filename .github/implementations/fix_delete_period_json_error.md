## Fix JSON Parsing Error in Period Deletion

- [ ] **Investigate `app/ajax/delete_period.php`**:
    - [ ] Read the content of `app/ajax/delete_period.php`.
    - [ ] Ensure it sets the `Content-Type` header to `application/json`.
    - [ ] Ensure it always uses `json_encode` to output responses (e.g., `{"success": true}` or `{"success": false, "message": "Error details"}`).
    - [ ] Implement proper error handling (try-catch blocks) to return JSON errors instead of PHP errors/HTML.
- [ ] **Update `assets/js/admin/periods-management.js`**:
    - [ ] Read the `deletePeriod` function.
    - [ ] Modify the `fetch` call in `deletePeriod` to better handle the response.
    - [ ] Check `response.ok` and the `Content-Type` header before attempting `response.json()`.
    - [ ] If the response is not JSON, try to read it as text to provide more context for the error.
    - [ ] Display user-friendly error messages.
- [ ] **Test Deletion**:
    - [ ] Test the delete functionality thoroughly to ensure the fix works and no new issues are introduced.
