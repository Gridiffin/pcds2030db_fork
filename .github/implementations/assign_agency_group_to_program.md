# Assign Agency Group to Program on Creation

## Problem

When a user creates a new program, the program should automatically be assigned the `agency_group` according to the owner's group. Currently, this assignment does not happen.

## Steps to Solve

- [x] Analyze the current database structure to ensure `agency_group_id` exists in the `programs` table.
- [x] Confirm there is a way to map owner/user IDs to their agency group (e.g., via a `users` or `agencies` table).
- [ ] Locate the backend code responsible for program creation.
- [ ] Update the logic to fetch the owner's agency group and assign it to the new program.
- [ ] Ensure all queries are parameterized for security.
- [ ] Test the feature to confirm correct assignment.
- [ ] Remove any test files after implementation.

## Notes

- Follow project coding standards and best practices.
- Use meaningful variable and function names.
- Document all changes and keep the code modular.
- Suggest improvements if any bad practices are found during implementation.
