# Admin Programs Functionality: Familiarization and Mapping

## Objective
Understand how the "programs" feature works on the admin side to use as a reference for fixing and improving the agency side.

## Step-by-Step Plan

- [x] Identify all relevant files and logic related to "programs" on the admin side:
    - Views: `app/views/admin/programs/`
    - JS: `assets/js/admin/`
    - Libraries: `app/lib/admins/`, `app/lib/admin_functions.php`, `app/lib/admins/statistics.php`
    - AJAX: `app/views/admin/ajax/`
    - Database: `programs`, `program_submissions`, `users`, `sectors` tables
- [x] List the main admin-side features:
    - Programs overview/listing (filter, search, period selector)
    - Assign new program to agency (with edit permissions)
    - Edit program (details, targets, permissions)
    - View program (details, history, current submission, status)
    - Delete program (with confirmation, deletes submissions too)
    - Reopen submission (convert finalized to draft for agency editing)
    - Unsubmit/resubmit program (change submission status)
- [x] Note key admin-side logic:
    - Uses `get_admin_programs_list`, `get_admin_program_details` for data
    - Handles both assigned and agency-created programs
    - Edit permissions (JSON) control what agencies can edit
    - All actions are permission-checked (`is_admin()`)
    - Uses modular includes for maintainability
    - JS for table filtering, modals, confirmation dialogs
- [x] Identify how admin-side features map to agency-side needs:
    - Agency must see both assigned and self-created programs
    - Agency editability is controlled by admin-set permissions
    - Agency can only edit draft submissions (unless reopened)
    - Agency needs clear feedback/messages for actions
- [ ] Document this mapping and use it as a checklist for fixing the agency side

## Next Steps
- [ ] Review agency-side implementation and compare with admin-side reference
- [ ] Identify and list all problems/gaps on the agency side
- [ ] Propose and implement fixes, referencing admin-side best practices

---

**This file will be updated as tasks are completed.**
