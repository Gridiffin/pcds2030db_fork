# Hold Point Management Table Implementation (Program Details View)

## Objective
Display a table of all hold points (active and ended) for a program on the program details page, visible to all users. No edit/end/add actions are available here; this is a read-only view for transparency.

## Tasks
- [ ] Fetch all hold points for the program in `program_details.php` (via API or direct DB call).
- [ ] Render a Bootstrap table or list with columns: Reason, Remarks, Start Date, End Date, Status.
- [ ] Place the table in a logical position (after core program info, before timeline/attachments).
- [ ] Ensure the table is only visible in details view, not in edit view.
- [ ] Add a note if there are no hold points.
- [ ] Style the table to match project conventions.
- [ ] (Optional) Show a badge or icon for active/ended status.
- [ ] Update this file as each step is completed.

## Notes
- Editing/ending/adding hold points is only available in the edit page (with permission).
- The table is for transparency/read-only display.
- Use existing Bootstrap 5 styles as much as possible.
- Reference all styles via layouts/base.css if new CSS is needed.

---

## Progress Log
- [ ] Initial plan and checklist written (this file)
