# Database Schema Update Task

## Problem
The old database schema (`oldpcds2030db.sql`) contains columns that don't exist in the new database schema (`newpcds2030db.sql`). We need to update the old database schema to match the new one by removing columns that exist in the old but not in the new database.

## Analysis

### Old Database Users Table Structure:
```sql
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agency_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','agency','focal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector_id` int DEFAULT NULL,
  `agency_group_id` int NOT NULL COMMENT '0-STIDC\r\n1-SFC\r\n2-FDS',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `agency_name` (`agency_name`),
  KEY `sector_id` (`sector_id`),
  KEY `agency_id` (`agency_group_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`agency_group_id`) REFERENCES `agency_group` (`agency_group_id`)
)
```

### New Database Users Table Structure:
```sql
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pw` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agency_id` int NOT NULL,
  `role` enum('admin','agency','focal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `agency_id` (`agency_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)
)
```

## Differences Found

### Columns to Remove from Old Database:
- [x] `agency_name` - replaced by `agency_id` foreign key
- [x] `sector_id` - no longer used in new schema
- [x] `agency_group_id` - replaced by `agency_id` foreign key

### Columns to Add to Old Database:
- [x] `pw` (renamed from `password`)
- [x] `fullname` (new column)
- [x] `email` (new column)
- [x] `agency_id` (new foreign key)

### Constraints to Update:
- [x] Remove `UNIQUE KEY agency_name`
- [x] Remove `KEY sector_id`
- [x] Remove `KEY agency_id` (old one)
- [x] Remove foreign key constraints to `sectors` and `agency_group`
- [x] Add `UNIQUE KEY username`
- [x] Add `UNIQUE KEY email`
- [x] Add `KEY agency_id` (new one)
- [x] Add foreign key constraint to `agency`

## Solution Steps

1. [x] Create SQL script to update old database schema
2. [x] Remove columns that don't exist in new schema
3. [x] Add columns that exist in new schema but not in old
4. [x] Update constraints and foreign keys
5. [ ] Test the updated schema
6. [ ] Update any related code that references removed columns

## Implementation Plan

### Step 1: Create Schema Update Script
- [x] Create `scripts/update_old_db_schema.sql`
- [x] Add ALTER TABLE statements to remove old columns
- [x] Add ALTER TABLE statements to add new columns
- [x] Update constraints and foreign keys

### Step 2: Test Schema Update
- [ ] Run script on test database
- [ ] Verify all changes are applied correctly
- [ ] Check for any constraint violations

### Step 3: Update Related Code
- [ ] Scan codebase for references to removed columns
- [ ] Update any SQL queries that use removed columns
- [ ] Update any PHP code that references removed columns

## Notes
- The old database uses `agency_group` table while new database uses `agency` table
- The old database has `sectors` table that's not used in new schema
- Password field is renamed from `password` to `pw` in new schema
- New schema adds `fullname` and `email` fields
- Agency relationship changes from `agency_group_id` to `agency_id`

## Programs Table Changes

### Old Database Programs Table:
- `owner_agency_id` - removed
- `agency_group` - removed  
- `sector_id` - removed
- `is_assigned` - removed
- `edit_permissions` - removed
- `status_indicator` - removed

### New Database Programs Table:
- `agency_id` - new foreign key to agency table
- `users_assigned` - new foreign key to users table
- `status` - new enum field
- `targets_linked` - new integer field

### Mapping:
- `agency_group` 0 (STIDC) → `agency_id` 1
- `agency_group` 1 (SFC) → `agency_id` 2
- `agency_group` 2 (FDS) → `agency_id` 3 