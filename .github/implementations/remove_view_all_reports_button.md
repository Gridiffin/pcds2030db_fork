# Remove View All Reports Button from Generate Reports Header

## Problem Description
The user wants to remove the "View All Reports" button from the header section of the generate reports page to clean up the interface.

## Solution Steps
- [x] Locate the header configuration in generate_reports.php
- [x] Remove the "View All Reports" action from the header config
- [x] Verify the change doesn't break any functionality

## Files Modified
1. ✅ `/app/views/admin/reports/generate_reports.php` - Removed button from header actions

## Testing
- [x] Verify the button is removed from the header
- [x] Ensure the page still functions correctly
- [x] Check that the header layout looks good without the button

## ✅ Implementation Complete!

Successfully removed the "View All Reports" button from the header of the generate reports page. The header now has an empty actions array, which will result in a cleaner interface without the button.

**Change Made:**
- Modified the `$header_config['actions']` array to be empty instead of containing the "View All Reports" button configuration.
