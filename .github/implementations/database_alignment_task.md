# Database Alignment Task - Agency and Users Tables

## Objective
Compare old and new PCDS2030 databases and align the agency group (old) / agency (new) and users tables.

## Tasks

### Phase 1: Database Structure Analysis
- [x] Get available database connections
- [x] Compare old and new database structures
- [x] Document differences between agency_group (old) and agency (new) tables
- [x] Document differences between users tables in old vs new

## Analysis Results

### Agency Table Comparison
**Old DB (pcds2030_dashboard) - `agency` table:**
- agency_id (int, PK, auto_increment)
- agency_name (varchar(255), not null)

**New DB (pcds2030_db) - `agency` table:**
- agency_id (int, PK, auto_increment)
- agency_name (varchar(255), not null)
- created_at (timestamp, not null, default CURRENT_TIMESTAMP)
- updated_at (timestamp, not null, default CURRENT_TIMESTAMP)

**Differences:**
- New DB has additional `created_at` and `updated_at` timestamp columns
- Old DB is missing these timestamp tracking columns

### Users Table Comparison
**Old DB (pcds2030_dashboard) - `users` table:**
- user_id (int, PK, auto_increment)
- username (varchar(100), not null)
- password (varchar(255), not null)
- agency_name (varchar(100), null)
- role (enum: admin, agency, focal, not null)
- agency_id (int, not null)
- created_at (timestamp, not null, default CURRENT_TIMESTAMP)
- updated_at (timestamp, not null, default CURRENT_TIMESTAMP)
- is_active (tinyint, null, default 1)

**New DB (pcds2030_db) - `users` table:**
- user_id (int, PK, auto_increment)
- username (varchar(100), not null)
- pw (varchar(255), not null)
- fullname (varchar(200), null)
- email (varchar(255), not null)
- agency_id (int, not null)
- role (enum: admin, agency, focal, not null)
- created_at (timestamp, not null, default CURRENT_TIMESTAMP)
- updated_at (timestamp, not null, default CURRENT_TIMESTAMP)
- is_active (tinyint, null, default 1)

**Differences:**
- Old DB has `password` column, New DB has `pw` column (same purpose, different name)
- Old DB has `agency_name` column, New DB doesn't have this
- New DB has `fullname` column, Old DB doesn't have this
- New DB has `email` column, Old DB doesn't have this

### Phase 2: Schema Alignment
- [x] Align agency_group/agency table columns
  - [x] Add missing columns from new to old
  - [x] Remove columns that exist in old but not in new
- [x] Align users table columns
  - [x] Add missing columns from new to old
  - [x] Remove columns that exist in old but not in new

### Phase 3: Data Migration
- [x] Populate old database agency table with data from new database
- [x] Populate old database users table with data from new database

### Phase 4: Verification
- [x] Verify column alignment is correct
- [x] Verify data migration is complete
- [x] Document final results

## Final Results

### Schema Alignment Completed Successfully

**Agency Table:**
- ✅ Added `created_at` and `updated_at` timestamp columns to old database
- ✅ Table structure now matches new database exactly
- ✅ All 3 agency records migrated successfully

**Users Table:**  
- ✅ Renamed `password` column to `pw` to match new database
- ✅ Added `fullname` (varchar(200), nullable) column  
- ✅ Added `email` (varchar(255), not null) column with index
- ✅ Removed `agency_name` column (not in new database)
- ✅ Updated existing users with data from new database
- ✅ Old database now has complete user data with enhanced schema

### Migration Summary
- **Agency Records**: 3 records successfully migrated
- **User Records**: 13 users from new DB updated existing users in old DB (old DB had 22 total users)
- **Schema Changes**: Both tables now have identical structure to new database
- **Data Integrity**: All foreign key relationships maintained

### SQL Operations Performed
1. Added timestamp columns to agency table
2. Renamed password → pw in users table
3. Added fullname and email columns to users table
4. Removed agency_name column from users table  
5. Added email index to users table
6. Migrated all agency data from new to old database
7. Updated user data based on new database records

The old database (`pcds2030_dashboard`) is now fully aligned with the new database (`pcds2030_db`) schema and contains the updated data.
