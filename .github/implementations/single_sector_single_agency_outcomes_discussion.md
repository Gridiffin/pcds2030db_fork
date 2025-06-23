# Single Sector + Single Agency per Outcome - Discussion Document

## Current Situation Summary
Based on the previous analysis, the current outcomes system:
- Supports multiple sectors via `sector_id` in `sector_outcomes_data` table
- Agency users are restricted by their assigned `$_SESSION['sector_id']`
- Multiple agencies can work on different outcomes within the same sector
- Each agency sees only outcomes for their sector

## New Requirements
1. **Single Sector Focus**: The system now focuses on only one sector
2. **Single Agency per Outcome**: Each outcome has only one agency working on it

## Discussion Points & Questions

### 1. Single Sector Implementation Options

#### Option A: Remove Sector Concept Entirely
**Pros:**
- Simplifies database structure
- Removes sector-based filtering logic
- Cleaner codebase

**Cons:**
- Major breaking change
- Loses sector organization for future scalability
- Requires significant database migration

#### Option B: Default/Primary Sector Approach
**Pros:**
- Maintains sector structure for future flexibility
- Minimal database changes
- Can set one sector as "primary" and filter everything to it

**Cons:**
- Maintains complexity for single-sector use
- Still need sector-based logic

#### Option C: Hide Sector from UI, Keep in Backend
**Pros:**
- Maintains database integrity
- Future-ready for multi-sector expansion
- UI becomes simpler

**Cons:**
- Code complexity remains
- Hidden complexity

**My Recommendation**: Option C - Keep sector in backend but hide from agency users

### 2. Single Agency per Outcome Implementation

#### Option A: Outcome Ownership Assignment
- Add `assigned_agency_id` or `owner_agency_id` to `sector_outcomes_data`
- Only assigned agency can edit/manage that outcome
- Admin assigns outcomes to agencies

#### Option B: Outcome Creation Ownership
- Whoever creates the outcome owns it
- Use existing `submitted_by` field to determine ownership
- Agency that created it is the only one who can edit

#### Option C: Agency-Outcome Relationship Table
- Create new table `outcome_assignments`
- More flexible for future features
- Supports outcome transfer between agencies

**My Recommendation**: Option A - Simple ownership field

### 3. Database Schema Changes Required

#### Minimal Changes (Recommended):
```sql
-- Add agency ownership to outcomes
ALTER TABLE sector_outcomes_data 
ADD COLUMN assigned_agency_id INT NULL,
ADD FOREIGN KEY (assigned_agency_id) REFERENCES users(user_id);

-- Or use existing submitted_by as ownership indicator
-- No schema change needed, just logic change
```

#### Alternative Changes:
```sql
-- If we want more explicit ownership
ALTER TABLE sector_outcomes_data 
ADD COLUMN owner_agency_id INT NULL,
ADD COLUMN can_edit_agency_id INT NULL;
```

### 4. Business Logic Changes

#### Current Logic:
```php
// Agency sees outcomes for their sector
WHERE sector_id = $_SESSION['sector_id']
```

#### New Logic Options:
```php
// Option 1: Agency sees only their owned outcomes
WHERE assigned_agency_id = $_SESSION['user_id'] 
// OR WHERE submitted_by = $_SESSION['user_id']

// Option 2: Agency sees all outcomes but can only edit owned ones
WHERE 1=1 // (show all, control via edit permissions)

// Option 3: Hybrid - show all, highlight owned
WHERE 1=1 // (show all with ownership indicators)
```

### 5. UI/UX Impact

#### Current Agency Dashboard:
- Shows outcomes for their sector
- Can create/edit all draft outcomes in their sector

#### New Agency Dashboard Options:

#### Option A: Ownership-Based View
- Show only outcomes they own
- Clear "My Outcomes" branding
- Simple, focused interface

#### Option B: All Outcomes with Ownership Indicators
- Show all outcomes in the system
- Visual indicators for ownership (owned/not owned)
- Edit controls only for owned outcomes

#### Option C: Tabbed Interface
- "My Outcomes" tab (owned outcomes)
- "All Outcomes" tab (read-only view of others)

**My Recommendation**: Option A for simplicity

### 6. Admin Interface Changes

#### Current Admin Interface:
- Shows outcomes from all sectors
- Can manage any outcome

#### Proposed Admin Interface:
- Shows all outcomes with agency ownership info
- Can reassign outcomes between agencies
- Can see outcome ownership history
- Dashboard shows outcomes by agency

### 7. Key Questions for Decision

1. **Sector Handling**: Do you want to completely remove sectors or keep them hidden?

2. **Outcome Ownership**: Should ownership be based on:
   - Who created the outcome? 
   - Admin assignment?
   - Agency selection during creation?

3. **Visibility**: Should agencies see:
   - Only their owned outcomes?
   - All outcomes with edit restrictions?
   - Hybrid view?

4. **Existing Data**: How should we handle existing outcomes?
   - Assign to creator (`submitted_by`)?
   - Let admin manually assign?
   - Assign based on sector membership?

5. **Future Scalability**: Do you anticipate:
   - Returning to multi-sector?
   - Multiple agencies per outcome?
   - Outcome collaboration features?

### 8. Migration Strategy

#### Database Migration:
1. Add ownership field to `sector_outcomes_data`
2. Assign existing outcomes to their creators
3. Update indexes if needed

#### Code Migration:
1. Update query logic in library functions
2. Modify UI to show ownership
3. Add ownership controls for admins
4. Update access control logic

#### User Migration:
1. Inform agencies about new ownership model
2. Provide admin tools for outcome reassignment
3. Update documentation

## Recommended Approach

Based on the analysis, I recommend:

1. **Sector Handling**: Keep sector in database, set default sector, hide from agency UI
2. **Ownership**: Use `submitted_by` as ownership, add `assigned_agency_id` for admin reassignment
3. **Agency View**: Show only owned outcomes for simplicity
4. **Admin View**: Full oversight with ownership management tools

This provides the cleanest user experience while maintaining system flexibility.

## Next Steps

Please provide feedback on:
1. Which sector handling approach you prefer
2. How outcome ownership should work
3. What agencies should see in their interface
4. How to handle existing data

Once we align on these decisions, I can create the detailed implementation plan.
