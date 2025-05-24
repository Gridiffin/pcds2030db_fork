# Fix Accordion on Reporting Periods Page

The accordion on the `app/views/admin/reporting_periods.php` page is not functioning. Clicks on the year toggles do not expand or collapse the content, and the indicator icon does not change.

## TODO

- [ ] **Identify the cause of the JavaScript malfunction.**
    - The JS was attempting to find content elements by an ID (`collapse${year}`) that was not being set in the PHP.
- [ ] **Correct the JavaScript selector for the accordion content.**
    - Modify `assets/js/admin/reporting_periods.js` to use `this.nextElementSibling` to find the `.year-content` div, which is a sibling of the clicked `.year-toggle` button.
- [ ] **Implement icon change (chevron-up/down) in JavaScript.**
    - Update the JS to toggle `fa-chevron-up` and `fa-chevron-down` classes on the icon within the `.year-toggle` button.
- [ ] **Correct the initial state of the chevron icon in PHP.**
    - Modify `app/views/admin/reporting_periods.php` to set the initial chevron icon class (`fa-chevron-up` or `fa-chevron-down`) based on whether the year group is initially expanded.
- [ ] **Verify the fix:**
    - Test the accordion on the reporting periods page to ensure years expand/collapse correctly on click.
    - Confirm the chevron icon changes appropriately.
    - Confirm the first year is expanded by default and its icon is `fa-chevron-up`.

## Files to Modify:

- `d:\laragon\www\pcds2030_dashboard\app\views\admin\reporting_periods.php` (Fix initial icon state)
- `d:\laragon\www\pcds2030_dashboard\assets\js\admin\reporting_periods.js` (Fix accordion JS logic and icon toggle)
