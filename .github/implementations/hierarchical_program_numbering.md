# Hierarchical Program Numbering System

## Problem Description
We need to implement a hierarchical numbering system where:
- One initiative can have multiple programs
- If initiative A has number "30", its programs should be numbered as "30.1", "30.2", "30.3", etc.
- This creates a clear hierarchical relationship between initiatives and their programs
- Program numbers should be automatically generated based on the initiative number and sequential program order

## Current State Analysis
- ✅ Database tables exist: `initiatives` table with `initiative_number` field, `programs` table with `program_number` and `initiative_id` fields
- ✅ Relationship exists: `programs.initiative_id` links to `initiatives.initiative_id`
- ❌ No automatic hierarchical numbering system in place
- ❌ Current program numbers are manually entered or inconsistent

## Implementation Plan

### Phase 1: Database Functions and Helpers
- [x] Create numbering helper functions in `app/lib/numbering_helpers.php`
- [x] Add function to generate next available program number for an initiative
- [x] Add function to update all program numbers when initiative number changes
- [x] Add validation functions for number format consistency

### Phase 2: Backend Integration
- [x] Update program creation logic to auto-generate hierarchical numbers
- [x] Update program assignment to initiatives to trigger renumbering
- [x] Update initiative number changes to cascade to programs
- [x] Add migration script to update existing programs with hierarchical numbers

### Phase 3: Frontend Updates
- [ ] Update program creation forms to show auto-generated numbers
- [ ] Update program editing forms to handle number changes
- [ ] Update bulk initiative assignment to trigger renumbering
- [ ] Add visual indicators showing hierarchy in tables

### Phase 4: API and AJAX Updates
- [ ] Update API endpoints to handle hierarchical numbering
- [ ] Add AJAX endpoints for number preview/validation
- [ ] Update bulk operations to handle renumbering

### Phase 5: Testing and Validation
- [ ] Test program creation with auto-numbering
- [ ] Test initiative number changes cascading to programs
- [ ] Test bulk operations and edge cases
- [ ] Validate number uniqueness and consistency

## Technical Specifications

### Numbering Format
- Initiative numbers: Numeric (e.g., "30", "31", "32")
- Program numbers: Initiative.Sequence (e.g., "30.1", "30.2", "30.3")

### Business Rules
1. Each initiative can have a unique numeric identifier
2. Programs under an initiative get sequential sub-numbers (1, 2, 3, ...)
3. When initiative number changes, all its programs renumber automatically
4. When program is moved to different initiative, it gets new number in target initiative
5. When program is removed from initiative, remaining programs maintain their numbers (no gaps filled)

### Database Changes
- No schema changes needed (existing structure supports this)
- Add indexes for performance on program_number queries
- Add constraints to ensure number format consistency

## Files to Modify

### New Files
- `app/lib/numbering_helpers.php` - Core numbering logic
- `app/database/migrations/update_hierarchical_numbering.php` - Migration script

### Existing Files to Update
- `app/lib/agencies/programs.php` - Program creation/update
- `app/lib/initiative_functions.php` - Initiative management
- `app/views/admin/programs/assign_programs.php` - Program assignment
- `app/views/admin/programs/bulk_assign_initiatives.php` - Bulk operations
- `app/views/agency/programs/create_program.php` - Program creation
- `app/views/agency/programs/update_program.php` - Program editing
- `app/api/initiatives.php` - Initiative API
- `assets/js/admin/programs_admin.js` - Frontend logic
- `assets/js/admin/bulk_assign_initiatives.js` - Bulk operations

## Success Criteria
- ✅ Programs automatically get hierarchical numbers when assigned to initiatives
- ✅ Initiative number changes cascade to all its programs
- ✅ Bulk operations maintain number consistency
- ✅ User interface clearly shows hierarchical relationship
- ✅ All existing functionality continues to work
- ✅ Performance remains acceptable with numbering operations

## Risk Mitigation
- Backup database before implementing changes
- Implement changes in staging environment first
- Add rollback capability for migration script
- Extensive testing of edge cases (concurrent operations, large datasets)
- Graceful handling of numbering conflicts
