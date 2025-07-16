# Adapt Agency Initiatives View for Admin Use

## Goal

Adapt the agency initiatives view (`manage_initiatives.php`) to work for admin users, ensuring proper permissions, data access, and UI/UX for admin workflows.

## Steps / TODO

- [ ] Analyze differences between agency and admin initiatives views
- [x] Identify and refactor agency-specific logic (e.g., `is_agency()`, `$_SESSION['agency_id']`) for admin context
- [x] Ensure admin can view all initiatives, not just those assigned to a specific agency
- [x] Update session and permission checks to use admin logic (e.g., `is_admin()`)
- [ ] Adjust filters, queries, and data presentation for admin needs
- [ ] Add or update admin-specific actions and UI elements as needed
- [ ] Scan and update related files (CSS, JS, helpers) for admin compatibility
- [ ] Test the admin initiatives view for correct functionality and permissions
- [ ] Suggest and implement codebase or UX improvements if found
- [ ] Remove any test or obsolete files created during the process

## Notes

- Follow project coding standards and best practices
- Document progress by marking each step as complete
- Ensure all related files are updated and referenced properly
