# Field Change History for Submissions - Full Implementation Plan

## Problem Statement
Client requires a feature to view the change history of specific fields in a submission, including what changed, when, and who made the change. This is to enable tracking of progress over time for each submission field.

---

## Implementation Steps

- [x] **1. Audit Logging Review & Enhancement**
    - [x] 1.1. Confirm that all submission field changes are logged in `audit_logs` and `audit_field_changes`.
    - [x] 1.2. If not, update submission create/edit logic to log field-level changes.
    - [x] 1.3. Ensure `audit_logs` reliably references the `submission_id` (add column if needed for performance).

- [x] **2. Backend API Endpoint**
    - [x] 2.1. Create an endpoint (e.g., `get_field_history.php` and `get_submission_audit_history.php`) to fetch field change history for a given submission and field.
    - [x] 2.2. Query joins `audit_logs`, `audit_field_changes`, and `users` to return: old/new value, timestamp, user, etc.
    - [ ] 2.3. Secure the endpoint (auth, permissions) [REVIEWED: endpoints check user session and role].

- [x] **3. Frontend Integration**
    - [x] 3.1. Add a UI component (modal/tab/section) on the submission details page to view field change history. (Already present in `update_program.php` for key fields)
    - [ ] 3.2. Allow user to select a field and view its change history (table/timeline).
    - [ ] 3.3. Display: Old Value → New Value, Who, When. (Enhance UI to use audit log data for more granular tracking if needed)

- [ ] **4. History Sidebar Implementation**
    - [ ] 4.1. Design a modern, visually appealing sidebar to display field change history, to be placed after the attachments section on the edit submission page.
    - [ ] 4.2. Sidebar should be collapsible and responsive, matching the current UI style.
    - [ ] 4.3. Sidebar displays a searchable/filterable list of all field changes for the current submission, grouped by field and/or date.
    - [ ] 4.4. Clicking a field in the sidebar highlights or scrolls to the corresponding field in the form (optional, for enhanced UX).
    - [ ] 4.5. Integrate AJAX calls to `get_submission_audit_history.php` to fetch and render the change history dynamically.
    - [ ] 4.6. Ensure accessibility and performance (lazy load, pagination if needed).

- [ ] **5. Documentation & Testing**
    - [ ] 5.1. Document the new feature and usage for admins/agencies.
    - [ ] 5.2. Test with various submission edits to ensure all changes are tracked and displayed correctly.
    - [ ] 5.3. Clean up any test files/data after implementation.

---

## Notes
- The codebase already logs field-level changes for targets and submissions in `audit_field_changes` and `audit_logs`.
- Endpoints `get_field_history.php` and `get_submission_audit_history.php` exist for fetching field change history and audit logs.
- The `update_program.php` view already provides field-level history for key fields (program name, description, targets, remarks, etc.).
- **Next:** Implement a modern History Sidebar after the attachments section in the edit submission page, ensuring it fits visually and functionally with the current UI.
- Update this file as each step is completed. 