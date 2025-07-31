# Live Deployment Problems & Solutions

## Overview
This document tracks all problems encountered during the live deployment of PCDS2030 Dashboard to cPanel hosting at `sarawakforestry.com/pcds2030` and their solutions.

## Problems Encountered & Solutions

### 1. Database Configuration Issues
**Problem**: Local development database credentials were hardcoded for localhost.
**Impact**: Application couldn't connect to production database.
**Solution**: 
- Updated `app/config/config.php` with dynamic database configuration
- Production: `localhost:3306`, `sarawak3_admin1`, `attendance33**`
- Local: `localhost`, `root`, empty password
- Auto-detects environment based on `$_SERVER['HTTP_HOST']`

### 2. Hardcoded URL Paths
**Problem**: Multiple files contained hardcoded development paths like `/pcds2030_dashboard_fork/`
**Impact**: URLs and redirects pointed to wrong paths in production.
**Files Affected**:
- `app/helpers/vite-helpers.php` (lines 53-55)
- Various other files with hardcoded localhost paths

**Solution**:
- Updated `vite-helpers.php` to use dynamic `BASE_URL` from config
- Modified config.php to detect `sarawakforestry.com` and force `/pcds2030` path
- Added production domain detection for automatic path switching

### 3. APP_URL Detection Issues
**Problem**: Application couldn't properly detect the correct base URL in production.
**Impact**: Asset loading, redirects, and form actions used wrong URLs.
**Solution**:
- Enhanced `app/config/config.php` with robust production detection
- Added explicit handling for `sarawakforestry.com` domain
- Forces `https://www.sarawakforestry.com/pcds2030` for production

### 4. Session Management Failures
**Problem**: Sessions weren't starting properly due to "headers already sent" errors.
**Impact**: Login would succeed but users couldn't stay logged in, causing blank dashboard pages.
**Root Cause**: `session_start()` was called after output (echo statements, HTML) had already been sent.

**Solution**:
- Moved `session_start()` to the very beginning of `login.php` (before any output)
- Removed duplicate session handling code
- Sessions now start immediately at line 8-11 before any other output

### 5. Login Redirect Problems
**Problem**: After successful login, users were redirected to blank pages or wrong URLs.
**Multiple Issues**:
- PHP `header()` redirects failed due to output before headers
- Session variables weren't persisting between login and dashboard
- Dashboard authentication checks failed due to missing session data

**Solution**:
- Replaced PHP `header()` redirects with JavaScript redirects to avoid header issues
- Added comprehensive session variable setting (user_id, role, agency_id, username, fullname)
- Fixed session persistence by starting sessions before any output

### 6. Asset Loading Issues (Vite Bundles)
**Problem**: CSS and JS bundles weren't loading with correct paths in production.
**Impact**: Styling and JavaScript functionality broken.
**Solution**:
- Updated `app/helpers/vite-helpers.php` to use `BASE_URL` instead of hardcoded paths
- Production automatically uses `/pcds2030` path for all assets

### 7. Environment Detection
**Problem**: Application couldn't distinguish between local development and production environments.
**Solution**:
- Added robust environment detection in `config.php`
- Uses `$_SERVER['HTTP_HOST']` to detect `sarawakforestry.com`
- Automatic switching between development and production configurations

## Key Configuration Changes

### app/config/config.php
```php
// Dynamic database configuration
$current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if ($current_host === 'www.sarawakforestry.com' || $current_host === 'sarawakforestry.com') {
    // Production settings
    define('DB_HOST', 'localhost:3306');
    define('DB_USER', 'sarawak3_admin1');
    define('DB_PASS', 'attendance33**');
} else {
    // Local development settings
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Force production paths
if ($current_host === 'www.sarawakforestry.com' || $current_host === 'sarawakforestry.com') {
    define('APP_URL', 'https://www.sarawakforestry.com/pcds2030');
    define('BASE_URL', '/pcds2030');
}
```

### login.php
```php
// Session MUST start before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// JavaScript redirect instead of header redirect
echo "<script>window.location.href = '" . $redirect_url . "';</script>";
```

### app/helpers/vite-helpers.php
```php
// Use BASE_URL instead of hardcoded paths
if (defined('BASE_URL') && BASE_URL !== '') {
    $app_base_path = BASE_URL;
}
```

## Deployment Checklist
1. ✅ Update database credentials for production
2. ✅ Remove hardcoded development paths
3. ✅ Fix session handling (start sessions before output)
4. ✅ Update asset loading paths
5. ✅ Test login functionality
6. ✅ Verify dashboard access
7. ✅ Check all redirects work properly

## Testing Strategy
Created multiple debug files to isolate issues:
- `debug_config.php` - Test configuration loading
- `debug_simple.php` - Test server variables and host detection  
- `test_db.php` - Test database connection
- `test_login.php` - Test login functionality
- `test_includes.php` - Test file includes step by step
- `test_basic.php` - Test basic PHP functionality

## Final Status
✅ **RESOLVED**: All major deployment issues have been fixed. Application successfully deployed to production at `https://www.sarawakforestry.com/pcds2030/`

## Lessons Learned
1. **Always start sessions before any output** - PHP requirement
2. **Use dynamic configuration** instead of hardcoded paths for multi-environment deployment
3. **Test each component separately** when debugging complex deployment issues
4. **JavaScript redirects** can be more reliable than PHP headers in some hosting environments
5. **Host-based environment detection** is effective for automatic configuration switching