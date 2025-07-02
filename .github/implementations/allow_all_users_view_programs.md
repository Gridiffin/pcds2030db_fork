# Allow all users to view each other's programs

## TODO

- [x] Remove agency ownership security check in `program_details.php`
- [x] Ensure `$allow_view` is always true
- [x] Remove or update `$is_owner` logic if not needed
- [ ] Test that all users can view any program
- [ ] Delete this file after implementation is complete

## Notes

- This change will make all programs visible to any logged-in user, regardless of agency.
- No restrictions will be applied based on program ownership.
- Review for any other related security checks elsewhere if needed.
