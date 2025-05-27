## Fix Incorrect URL Generation in Admin Edit Program

**Problem:**
When editing a program from the admin side, the page navigates to a URL containing unescaped PHP code (`<?php echo $program_id; ?>`) and a literal `/$ViewType/` string, resulting in a "Not Found" error.
The problematic URL is: `http://localhost/pcds2030_dashboard/app/views/$ViewType/edit_program.php?id=%3C?php%20echo%20$program_id;%20?%3E`

**Cause:**
The `action` attribute of the form in `app/views/admin/programs/edit_program.php` is constructed incorrectly:
`action="<?php echo view_url('$ViewType', 'edit_program.php?id=<?php echo $program_id; ?>'); ?>"`
This leads to:
1.  Nested PHP tags: `<?php echo $program_id; ?>` inside another `<?php ... ?>`.
2.  `$ViewType` being treated as a literal string `'$ViewType'` instead of its variable value.

**Solution:**
Modify the `action` attribute in `d:\\laragon\\www\\pcds2030_dashboard\\app\\views\\admin\\programs\\edit_program.php` to correctly concatenate the `$program_id` and use the correct value for the view type (assuming 'admin' for this context).

**Steps:**
- [ ] Locate the form tag in `d:\\laragon\\www\\pcds2030_dashboard\\app\\views\\admin\\programs\\edit_program.php`.
- [ ] Modify the `action` attribute from:
  `action="<?php echo view_url('$ViewType', 'edit_program.php?id=<?php echo $program_id; ?>'); ?>"`
  to:
  `action="<?php echo view_url('admin', 'edit_program.php?id=' . $program_id); ?>"`
- [ ] Verify that `$ViewType` should indeed be `'admin'` in this context or if it's a defined PHP variable that should be used directly (e.g., `$ViewType` without quotes if it's a variable). Given the file path, `'admin'` is the most likely correct value.

**File to Edit:**
- `d:\laragon\www\pcds2030_dashboard\app\views\admin\programs\edit_program.php`
