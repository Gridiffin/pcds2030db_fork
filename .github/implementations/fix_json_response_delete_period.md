## Fix JSON Response for delete_period.php

**Problem:** The `delete_period.php` script is not returning a valid JSON response, causing a `SyntaxError` in `periods-management.js` when trying to parse the response.

**Error Log:**
```
Error in deletePeriod fetch: SyntaxError: JSON.parse: unexpected character at line 1 column 1 of the JSON data periods-management.js:682:17
```

**Solution Steps:**

1.  [ ] **Inspect `app/ajax/delete_period.php`:**
    *   Check for any `echo` or `print` statements that output non-JSON data.
    *   Ensure PHP errors/warnings are not being output directly to the response body.
    *   Verify that the script sets the `Content-Type` header to `application/json`.
    *   Ensure all possible code paths correctly `echo json_encode(...)` as the final output.
2.  [ ] **Modify `app/ajax/delete_period.php`:**
    *   Implement proper error handling that returns JSON-formatted error messages (e.g., `{"success": false, "message": "Error description"}`).
    *   Ensure successful operations also return a JSON response (e.g., `{"success": true}`).
    *   Set `header('Content-Type: application/json');` at the beginning of the script.
3.  [ ] **Review `assets/js/admin/periods-management.js` (line 682):**
    *   Confirm how the response is being handled. While the primary fix is likely server-side, ensure the client-side code correctly processes the expected JSON structure.
4.  [ ] **Test:** Verify that deleting a reporting period works correctly and no console errors appear.
