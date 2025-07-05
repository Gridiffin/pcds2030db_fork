# Development Environment Setup for Database Migration

## Overview
Set up a safe development environment to test the full database migration from `pcds2030_dashboard` to `pcds2030_db` structure before applying changes to production.

## Step 1: Create Development Database

### 1.1 Backup Current Production Database
- [ ] Create full backup of current `pcds2030_dashboard` database
- [ ] Store backup in safe location with timestamp
- [ ] Verify backup integrity

### 1.2 Create Development Database
- [ ] Create new database: `pcds2030_dashboard_dev`
- [ ] Import current production data into development database
- [ ] Verify all tables and data are present

### 1.3 Create Target Database Structure
- [ ] Create new database: `pcds2030_db_dev` 
- [ ] Import new database structure from `app/database/newpcds2030db.sql`
- [ ] Verify all tables are created correctly

## Step 2: Configure Development Environment

### 2.1 Create Development Configuration
- [ ] Copy `app/config/config.php` to `app/config/config_dev.php`
- [ ] Update database connection settings for development
- [ ] Ensure development uses `pcds2030_dashboard_dev` initially
- [ ] Add environment flag to distinguish dev from production

### 2.2 Update Application for Development Mode
- [ ] Create environment detection in main configuration
- [ ] Add development-specific error reporting
- [ ] Enable detailed logging for debugging
- [ ] Disable production-only features (emails, external APIs)

## Step 3: Test Current System in Development

### 3.1 Verify Development Environment Works
- [ ] Test login functionality with development database
- [ ] Verify basic CRUD operations work
- [ ] Test key application features
- [ ] Ensure no production data is affected

### 3.2 Document Current State
- [ ] Record current record counts for all tables
- [ ] Document current functionality status
- [ ] Note any existing issues or limitations

## Step 4: Execute Migration in Development

### 4.1 Prepare Migration
- [ ] Copy current data from `pcds2030_dashboard_dev` to `pcds2030_db_dev`
- [ ] Run `master_migration_script.sql` on development target database
- [ ] Verify migration completed without errors

### 4.2 Update Development Configuration
- [ ] Switch development config to use `pcds2030_db_dev`
- [ ] Test database connectivity
- [ ] Verify application can connect to new structure

## Step 5: Initial Testing & Validation

### 5.1 Data Integrity Checks
- [ ] Compare record counts between old and new dev databases
- [ ] Verify foreign key relationships work
- [ ] Test basic database queries
- [ ] Validate data types and constraints

### 5.2 Application Testing
- [ ] Test login functionality (expect failures - needs code updates)
- [ ] Identify immediate breaking points
- [ ] Document which features break and why
- [ ] Prioritize critical fixes needed

## Quick Start Commands

### Database Setup (using phpMyAdmin or MySQL client)
```sql
-- 1. Create development databases
CREATE DATABASE pcds2030_dashboard_dev;
CREATE DATABASE pcds2030_db_dev;

-- 2. Import current production to dev
-- (Use phpMyAdmin export/import or mysqldump)

-- 3. Import new structure to target
-- Import: app/database/newpcds2030db.sql into pcds2030_db_dev
```

### Development Configuration Template
```php
// app/config/config_dev.php
<?php
// Development Environment Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'pcds2030_dashboard_dev'); // Initially old structure
define('ENVIRONMENT', 'development');
define('DEBUG_MODE', true);

// After migration testing, switch to:
// define('DB_NAME', 'pcds2030_db_dev');
?>
```

## Files You'll Need to Refactor (Reference)

Based on our previous analysis, here are the key files that will need updates after migration:

### Priority 1: Core Files
- `app/config/config.php` - Database configuration
- `app/lib/admin_functions.php` - User and agency management
- `login.php` - Authentication system
- `logout.php` - Session management

### Priority 2: Agency & User Management
- `app/ajax/admin_dashboard_data.php`
- `app/ajax/agency_dashboard_data.php` 
- `app/lib/agency_functions.php`
- `app/handlers/admin/` (all files)

### Priority 3: Program Management
- `app/api/programs.php`
- `app/ajax/get_program_submission.php`
- All files with `owner_agency_id` references

### Priority 4: Reporting & Dashboard
- `app/api/report_data.php`
- `app/ajax/dashboard_data.php`
- Files with `sector_id` and `agency_group_id` references

## Next Steps After Setup

1. **Complete this development setup**
2. **Run migration script in development**
3. **Test and document what breaks**
4. **Begin systematic code refactoring**
5. **Test each module as you update it**

## Safety Notes

⚠️ **IMPORTANT**: 
- Never run migration scripts on production database
- Always test in development first
- Keep production backups handy
- Document every change you make
- Test thoroughly before production deployment

Ready to start? Begin with Step 1: Database setup!
