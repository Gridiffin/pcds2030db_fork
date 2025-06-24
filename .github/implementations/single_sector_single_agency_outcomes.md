# Single Sector, Single Agency Outcomes Implementation

## Problem Statement
The system needs to be modified to accommodate:
1. Focus on only one sector instead of multiple sectors
2. Each outcome is managed by only one agency (no shared outcomes)
3. Simplify the data flow and user experience based on these constraints

## Current State Analysis
Based on the previous analysis, the current system:
- Supports multiple sectors via `sector_id` in tables
- Uses `$_SESSION['sector_id']` for agency access control
- Has sector-based filtering throughout the codebase

## Required Changes

### 1. Database Schema Updates
- [ ] Analyze if we need to modify `sector_outcomes_data` table
- [ ] Check if foreign key constraints need updates
- [ ] Verify indexes are still optimal for single-sector usage
- [ ] Consider adding agency ownership fields if needed

### 2. Authentication & Session Management
- [ ] Simplify sector assignment logic
- [ ] Update session variables
- [ ] Modify agency user creation process
- [ ] Review access control mechanisms

### 3. User Interface Updates
- [ ] Remove sector selection dropdowns from agency views
- [ ] Simplify outcome creation forms
- [ ] Update dashboard displays
- [ ] Modify navigation elements

### 4. Business Logic Changes
- [ ] Update outcome creation functions
- [ ] Modify outcome retrieval queries
- [ ] Simplify validation rules
- [ ] Update audit logging

### 5. File-Specific Changes

#### Agency Outcomes Files
- [ ] `app/views/agency/outcomes/create_outcome.php` - Remove sector selection
- [ ] `app/views/agency/outcomes/submit_outcomes.php` - Simplify display
- [ ] `app/views/agency/outcomes/edit_outcomes.php` - Remove sector filters
- [ ] `app/views/agency/outcomes/view_outcome.php` - Simplify view
- [ ] `app/lib/agencies/outcomes.php` - Update functions

#### Admin Files (if applicable)
- [ ] Review admin outcome management
- [ ] Update admin dashboard
- [ ] Modify reporting functions

### 6. Configuration & Settings
- [ ] Add system configuration for single-sector mode
- [ ] Update default values
- [ ] Modify installation/setup scripts

## Implementation Strategy

### Phase 1: Configuration & Core Changes
1. Add system configuration for single-sector mode
2. Update core database queries
3. Modify authentication logic

### Phase 2: Agency Interface Updates
1. Remove sector selection from agency forms
2. Simplify outcome management pages
3. Update dashboard displays

### Phase 3: Testing & Validation
1. Test outcome creation flow
2. Verify data integrity
3. Test multi-user scenarios
4. Validate audit trails

### Phase 4: Documentation & Cleanup
1. Update documentation
2. Remove unused code
3. Optimize queries for single-sector usage

## Questions to Clarify

1. **Sector Identification**: How is the single sector defined? Is it:
   - Hardcoded in configuration?
   - Set during system setup?
   - The first/default sector in the database?

2. **Agency-Outcome Relationship**: Should we:
   - Add explicit agency ownership to outcomes?
   - Rely on user sessions for ownership?
   - Implement outcome assignment mechanism?

3. **Data Migration**: For existing multi-sector data:
   - Should we migrate to the target sector?
   - Archive old sectors?
   - Maintain backward compatibility?

4. **Admin Functionality**: Should admins still:
   - See all agencies' outcomes?
   - Have cross-agency management capabilities?
   - Maintain sector-based reporting?

## Implementation Priority
1. **HIGH**: Core database and authentication changes
2. **HIGH**: Agency user interface simplification
3. **MEDIUM**: Admin interface updates
4. **LOW**: Performance optimizations and cleanup

## Status: 🔄 PLANNING
Ready to begin implementation once clarification questions are answered.
