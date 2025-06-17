# Fix Asset Paths Issue - PCDS 2030 Dashboard

## Problem Description
When the project is exported to another computer with XAMPP, the CSS and JS files are not loading. The pages are accessible but have no styling or JavaScript functionality. This is a common issue when moving projects between different development environments.

## Root Cause Analysis
The issue is likely caused by:
1. **Incorrect APP_URL configuration** - The `APP_URL` constant in `config.php` is hardcoded to `http://localhost/pcds2030_dashboard`
2. **Different folder structure** - The new computer might have the project in a different folder structure
3. **Virtual host configuration** - The new XAMPP setup might require different virtual host settings
4. **Document root path issues** - Path resolution might be different on the new computer

## Current Asset Loading Mechanism
- Assets are loaded using `asset_url()` function from `app/lib/asset_helpers.php`
- Function generates URLs like: `APP_URL . '/assets/' . $type . '/' . $file`
- `APP_URL` is defined in `app/config/config.php` as `http://localhost/pcds2030_dashboard`

## Solution Steps

### ✅ Step 1: Create dynamic APP_URL detection
- [x] Modify config.php to automatically detect the correct APP_URL based on server environment
- [x] Add fallback mechanism for different server configurations

### ✅ Step 2: Add debugging capabilities
- [x] Add debugging function to display current paths and URLs
- [x] Create test page to verify asset loading

### ✅ Step 3: Improve asset path resolution
- [x] Make asset_url() function more robust with different server configurations
- [x] Add relative path fallback option
- [x] Include asset_helpers.php in functions.php for global availability

### ✅ Step 4: Create setup documentation
- [x] Document the correct XAMPP setup process
- [x] Add troubleshooting guide for common issues

### ✅ Step 5: Test and validate
- [ ] Test on multiple environments
- [ ] Verify all assets load correctly
- [ ] Clean up test files

## Implementation Notes
- Consider server differences (Apache configuration, document root, etc.)
- Ensure solution works on both development and production environments (cPanel)
- Maintain backward compatibility with existing installations
