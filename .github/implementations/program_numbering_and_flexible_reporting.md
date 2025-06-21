# Program Numbering and Flexible Reporting System

## Implementation Status: IN PROGRESS

### ✅ COMPLETED TASKS

#### Database Changes
- [x] Added `program_number` column (VARCHAR(20), nullable) to `programs` table
- [x] Created index on `program_number` for better performance  
- [x] Confirmed `start_date` and `end_date` are already nullable
- [x] Tested database changes with sample data

#### Backend Implementation
- [x] Updated `app/lib/agencies/programs.php` program functions:
  - [x] `create_wizard_program_draft()` - handles program_number
  - [x] `auto_save_program_draft()` - includes program_number  
  - [x] `update_program_draft_only()` - handles program_number updates
  - [x] `create_agency_program()` - includes validation and storage
- [x] Added program_number validation (numbers and dots only)
- [x] Updated admin queries in `app/lib/admins/statistics.php` 
- [x] Updated API `app/api/get_period_programs.php`

#### Frontend Implementation
- [x] Agency program creation form (`app/views/agency/programs/create_program.php`)
- [x] Program listing views with badges:
  - [x] `app/views/agency/programs/view_programs.php` 
  - [x] `app/views/admin/programs/programs.php`
- [x] Program edit form (`app/views/agency/programs/update_program.php`)
- [x] Admin assign programs form (`app/views/admin/programs/assign_programs.php`)
- [x] Enhanced search functionality (both agency and admin JS)
- [x] Updated program display templates and search placeholders

---

## Problem Statement
The client (Mudern) has a specific way of organizing programs that differs from the current system:
1. **Program Numbers**: All programs have numbers (like "31.1") that connect to initiatives
2. **Flexible Timelines**: Programs don't always have specific start/end dates
3. **Manual Report Selection**: Admins should manually choose which programs appear in reports, not automatic date-based filtering

## Current Issues
- Programs require start_date and end_date
- Report generation filters programs by dates automatically
- No way to search/identify programs by their initiative numbers
- Sub-activities (targets) are handled quarterly but programs need persistent identification

## Solution Overview
1. Add program numbering system for easy identification
2. Make program dates optional 
3. Modify report generation to allow manual program selection
4. Update UI to display and search by program numbers

## Implementation Tasks

### Phase 1: Database Changes
- [x] Add `program_number` column to programs table (VARCHAR(20), nullable, numbers/dots only)
- [x] Make `start_date` and `end_date` nullable in programs table (already nullable)
- [x] Create database migration script (executed via DBCode)
- [x] Leave existing programs with null program_number values (to be edited later)

### Phase 2: Core Functionality Updates
- [x] Update program creation/editing forms to include program number field
- [x] Add program number validation (numbers and dots only)
- [x] Modify program creation functions to handle program numbers
- [ ] Update program listing queries to include program numbers
- [ ] Update search functionality to search by program numbers
- [ ] Display program numbers in program lists/cards

### Phase 3: UI/UX Updates
- [ ] Display program numbers in program lists/tables
- [ ] Add program number search filter
- [ ] Update program creation/edit forms
- [ ] Modify program display cards to show numbers prominently
- [ ] Update admin program selection interface

### Phase 4: Report Generation Changes
- [ ] Modify report generation to show program selection interface
- [ ] Remove automatic date-based filtering for program inclusion
- [ ] Add manual program selection checkboxes/interface
- [ ] Update report templates to show program numbers
- [ ] Ensure selected programs appear regardless of dates

### Phase 5: Testing and Validation
- [ ] Test program creation with and without dates
- [ ] Test program search by number
- [ ] Test report generation with manual selection
- [ ] Validate existing data migration
- [ ] Test quarterly submission flow (should remain unchanged)

### Phase 6: Documentation and Cleanup
- [ ] Update user documentation
- [ ] Update admin documentation for report generation
- [ ] Clean up any test files
- [ ] Update system context documentation

## Technical Considerations

### Database Schema Changes
```sql
-- Add program number column
ALTER TABLE programs ADD COLUMN program_number VARCHAR(20) NULL;

-- Make dates nullable (if not already)
ALTER TABLE programs MODIFY COLUMN start_date DATE NULL;
ALTER TABLE programs MODIFY COLUMN end_date DATE NULL;

-- Add index for program number searching
CREATE INDEX idx_program_number ON programs(program_number);
```

### Key Files to Modify
- `app/views/admin/programs/` - Program management interfaces
- `app/views/agency/programs/` - Agency program views
- `app/views/admin/reports/generate_reports.php` - Report generation
- `app/lib/functions.php` - Core program functions
- Database migration files

### Backward Compatibility
- Existing programs with dates should continue to work
- Quarterly submission system remains unchanged
- Program status and rating system stays the same
- Only report generation logic changes

## Benefits
1. **Easier Program Identification**: Search by "31.1" instead of remembering program names
2. **Flexible Timeline Management**: Programs can exist without specific dates
3. **Client-Friendly**: Matches their existing initiative/action step structure
4. **Improved Report Control**: Admins choose exactly what appears in reports
5. **Maintains Current Functionality**: Quarterly reporting system unchanged

## Migration Strategy
1. Add new columns with default NULL values
2. Gradually populate program numbers for existing data
3. Update UI to show both old and new identification methods
4. Train users on new program selection for reports
5. Eventually make program numbers required for new programs

## Notes
- Program numbers should follow client's existing format (XX.X)
- Consider adding program number uniqueness validation
- Report generation interface needs clear program selection UI
- Quarterly targets/achievements system remains unchanged
