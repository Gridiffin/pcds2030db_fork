# Users Table Refactor Documentation

## Summary
This document tracks the refactor of the user management system to improve maintainability and align with the new database schema.

---

## Key Changes

### 1. Agency Group to Agency Migration
- The `agency_group` table was renamed to `agency`.
- Column `agency_group_id` was renamed to `agency_id`.
- Column `group_name` was renamed to `agency_name`.
- All code references and logic were updated to use the new table and column names.

### 2. Sector Removal
- All logic, UI, and validation related to `sector` and `sector_id` were removed from user management files.
- The `sector_id` column should be dropped from both `users` and `agency` tables in the database.

### 3. Config-Driven Naming
- Table and column names for `agency` and `users` are now defined in `config/db_names.php`.
- All code references use the config file for maintainability and easy future changes.

---

## Affected Files
- `app/views/admin/users/edit_user.php`
- `app/views/admin/users/add_user.php`
- `app/lib/admins/users.php`
- `config/db_names.php`
- Migration SQL: `app/database/migrate_agency_group_to_agency.sql`

---

## Rationale
- **Maintainability:** Centralizing table/column names in config makes future changes safer and easier.
- **Simplicity:** Removing unused sector logic reduces code complexity.
- **Consistency:** Aligns codebase with the new database schema and project direction.

---

## Steps Taken
1. Listed and updated all code references to `agency_group` and `sector`.
2. Refactored code to use config-driven naming for `agency` and `users`.
3. Removed all sector-related code and UI from user management.
4. Created and applied a migration SQL script to update the database schema.
5. Documented the changes here for future reference.

---

## Additional Step
- Removed the sector column from the users list table in the frontend (`app/views/admin/users/manage_users.php`). 