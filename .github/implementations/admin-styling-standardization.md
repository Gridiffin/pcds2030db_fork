# Admin Pages Styling Standardization Plan

## Problem Analysis

Different admin pages are using inconsistent styling approaches:

- **manage_initiatives.php**: Uses Vite bundles + modern base.php layout + green header ✅
- **programs.php**: Uses old header.php + blue header + no bundling ❌
- **generate_reports.php**: Uses old header.php + additionalStyles + no bundling ❌
- **Other pages**: Mix of approaches ❌

## Standardization Strategy

1. **Create comprehensive admin bundles** for different admin sections
2. **Update key admin pages** to use modern base.php layout
3. **Standardize header theming** to use green forest theme
4. **Ensure consistent CSS loading** across all admin pages

## Admin Bundles Created

- `admin-common` - Base admin styling (13.37kB)
- `admin-programs` - Programs management pages
- `admin-reports` - Reports generation pages
- `admin-manage-initiatives` - Initiatives management

## Pages to Update

### High Priority (Main Admin Pages)

1. `app/views/admin/programs/programs.php` - Main programs listing
2. `app/views/admin/reports/generate_reports.php` - Reports generator
3. `app/views/admin/periods/reporting_periods.php` - Period management

### Medium Priority

4. `app/views/admin/outcomes/view_outcome.php` - Outcomes management
5. `app/views/admin/audit/audit_log.php` - Audit logs

## Implementation Plan

- [ ] Update programs.php to use admin-programs bundle + modern layout + green header
- [ ] Update generate_reports.php to use admin-reports bundle + modern layout + green header
- [ ] Update other key admin pages
- [ ] Test styling consistency across all admin pages
- [ ] Document admin page styling standards
