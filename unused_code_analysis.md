# Unused Code Analysis Report

Date: May 21, 2025

## Initial Scan Results

Our initial scan identified 79 potentially unused files, but many of these are likely entry points, AJAX endpoints, or view files that are accessed directly rather than included.

## Updated Analysis with JavaScript References

After analyzing JavaScript files for references to AJAX and API endpoints, we've identified several endpoints that are not referenced in any JS files and may be candidates for removal.

## File Categories for Review

### 1. Test/Debug Files - Safe to Remove after Verification
- `check_db.php` - Likely a database connection test
- `test_program_filter.html` - Test file that can be reviewed for removal

### 2. AJAX/API Endpoints - Not Referenced in JS

#### AJAX Files with No JS References:
- `ajax/add_quarter_column.php`
- `ajax/add_reporting_period_column.php`

#### API Files with No JS References:
- `api/check_metric.php`
- `api/check_outcome.php`
- `api/get_metric_data.php`
- `api/get_outcome_data.php`
- `api/get_periods.php`
- `api/get_recent_reports.php`
- `api/get_sectors.php`
- `api/save_metric_json.php`

### 3. In Use AJAX/API Endpoints - Keep These

#### AJAX Files Referenced in JS:
- `ajax/admin_dashboard_data.php` - Used in period_selector.js
- `ajax/agency_dashboard_data.php` - Used in period_selector.js
- `ajax/dashboard_data.php` - Used in multiple JS files
- `ajax/toggle_period_status.php` - Used in reporting_periods.js

#### API Files Referenced in JS:
- `api/delete_report.php` - Used in report-api.js
- `api/get_period_programs.php` - Used in report-generator.js
- `api/report_data.php` - Used in report-api.js
- `api/save_outcome_json.php` - Used in outcome-editor.js
- `api/save_report.php` - Used in report-api.js

### 4. View Files
View files typically don't need to be included by other PHP files as they are entry points accessed directly via URLs. These require manual verification:
- All files in `/views/admin/` and `/views/agency/`

### 5. Database Files
These files may be used for one-time operations or migrations:
- `database/validate_migration.php`

### 6. Helper Files
These files need verification to determine if their functions are used elsewhere:
- `includes/history_helpers.php`
- `includes/status_helpers.php`

## Action Plan for Safe Cleanup

### 1. Immediate Action - Add Logging
Add temporary logging to files identified as potentially unused to confirm their non-usage:

```php
// Add at the beginning of each file to be verified
file_put_contents('access_log.txt', date('Y-m-d H:i:s') . ' - ' . __FILE__ . "\n", FILE_APPEND);
```

### 2. Safe Removal Candidates (After 2 Weeks of Logging)
These files can likely be safely removed after confirming via logging that they're unused:
- `check_db.php`
- `test_program_filter.html`
- AJAX/API endpoints with no JS references (verify they're not called from PHP or directly via HTTP requests)

### 3. Further Investigation Required
- Helper files in the includes directory
- Database scripts
- View files with no direct references

### 4. Add Deprecation Notices
For files that seem unused but you're not confident about removing yet:

```php
/**
 * @deprecated This file appears to be unused and is marked for removal.
 * If you're seeing this message and know this file is needed, 
 * please contact the development team.
 */
```

## Methodology for Safe Removal

1. **Move Instead of Delete**: Move suspected unused files to a `/deprecated/` folder first
2. **Monitor**: Run the application for at least 2 weeks to ensure nothing breaks
3. **Document**: Maintain a record of moved files and their original locations
4. **Delete**: Only permanently remove files after confirming they're truly unused

## Next Steps

1. Implement logging for verification
2. Create the `/deprecated/` directory for staged removal
3. Begin moving the safest candidates first
4. Monitor application functionality closely

## Additional Notes

All files appear to have the same last modified timestamp (May 21, 2025), which suggests they might be part of a batch update or deployment. This makes it difficult to identify unused files based on timestamps alone.
