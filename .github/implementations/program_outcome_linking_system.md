# Program-Outcome Linking System Implementation

## Overview
Implement a system to link specific programs (e.g., TPA-related programs) to corresponding outcome details, enabling automatic outcome updates when linked programs change status.

## Current State Analysis
- ✅ Database structure exists (`program_outcome_links` table)
- ✅ API endpoints exist (`/app/api/program_outcome_links.php`)
- ✅ Automation functions exist (`/app/lib/outcome_automation.php`)
- ✅ Integration points exist (program submission triggers automation)
- ✅ Admin UI for managing links (dropdown in edit program page)
- ✅ JavaScript for dynamic outcome management
- ✅ Form submission handling for saving links
- ❌ Visual indicators of relationships in list views
- ❌ Agency-side outcome linking interface

## Implementation Steps

### Phase 1: Link Management Interface - COMPLETED
- [x] Add outcome selection dropdown to admin program edit page ✅
- [x] Handle saving/updating of program-outcome links in form submission ✅ 
- [x] Add JavaScript for adding/removing outcome selections ✅
- [x] Add outcome link change tracking to edit history ✅
- [ ] Add outcome selection dropdown to agency program edit page  
- [ ] Create API endpoint to get available outcomes for linking (optional)
- [ ] Add visual indicators on program pages showing linked outcomes

### Phase 2: Visual Relationship Indicators
- [ ] Show linked outcomes in program detail views
- [ ] Show linked programs in outcome detail views
- [ ] Add relationship indicators in program lists
- [ ] Add relationship indicators in outcome lists

### Phase 3: Default TPA Links Setup
- [ ] Identify all TPA-related programs
- [ ] Create default links between TPA programs and TPA outcomes
- [ ] Set up automation rules for TPA program status changes

### Phase 4: Enhanced Automation
- [ ] Improve outcome data calculation based on linked programs
- [ ] Add progress tracking for outcome completion
- [ ] Implement cumulative outcome calculations
- [ ] Add notification system for automatic updates

### Phase 5: Testing & Documentation
- [ ] Test end-to-end program status → outcome update flow
- [ ] Create user documentation for link management
- [ ] Test bulk operations and performance
- [ ] Validate automation accuracy

## Technical Details

### Database Schema
```sql
-- Already exists
program_outcome_links (
    link_id INT PRIMARY KEY,
    program_id INT,
    outcome_id INT,
    created_by INT,
    created_at TIMESTAMP
)
```

### API Endpoints
- GET `/app/api/program_outcome_links.php?program_id={id}` - Get outcomes linked to program
- GET `/app/api/program_outcome_links.php?outcome_id={id}` - Get programs linked to outcome
- POST `/app/api/program_outcome_links.php` - Create new link
- DELETE `/app/api/program_outcome_links.php` - Remove link

### Files to Create/Modify
1. **New Files:**
   - `app/views/admin/links/manage_program_outcome_links.php`
   - `app/views/admin/links/bulk_link_programs.php`
   - `assets/js/program_outcome_links.js`

2. **Existing Files to Modify:**
   - `app/views/admin/programs/view_program.php`
   - `app/views/admin/outcomes/view_outcome.php`
   - `app/views/agency/programs/program_details.php`
   - `app/views/agency/outcomes/view_outcome.php`

### Automation Flow
1. Program status changes (completed/target-achieved)
2. `updateOutcomeDataOnProgramStatusChange()` triggered
3. System checks for linked outcomes
4. Outcome data automatically updated
5. Audit log entry created

## How Outcome Values Are Affected

When a program is linked to an outcome and its status changes, here's exactly what happens:

### Program Status Triggers
The automation triggers when a program's status changes to:
- **"completed"** - Program is fully completed
- **"target-achieved"** - Program achieved its targets

### Outcome Value Updates
1. **For Cumulative Outcomes** (`is_cumulative = 1`):
   - Increments the total count/value by the program's target value
   - Example: If "Bamboo Industry Development" program (target: 2 facilities) is completed, "Number of TPA programs completed" increases by 2

2. **For Non-Cumulative Outcomes** (`is_cumulative = 0`):
   - Records the completion event with details
   - Updates percentage or progress indicators
   - Example: "TPA program completion rate" recalculates based on completed vs. total programs

### Data Structure Impact
The outcome data in `sector_outcomes_data.data_json` gets updated automatically:
```json
{
  "total_value": 5,  // Incremented for cumulative outcomes
  "completed_programs": [
    {
      "program_id": 123,
      "program_name": "Bamboo Industry Development",
      "completion_date": "2025-06-25",
      "period_id": 7,
      "contribution_value": 2  // Program's target value
    }
  ],
  "auto_generated": true,
  "source": "program_completion",
  "last_updated": "2025-06-25 10:30:00"
}
```

### Real Example Workflow
1. Admin links "Bamboo Industry Development" program to "TPA Protection Programs Completed" outcome
2. User submits program with status "completed" and target value 2
3. System automatically finds linked outcome via `program_outcome_links` table
4. Calls `updateOutcomeDataOnProgramStatusChange()` in `outcome_automation.php`
5. Updates "TPA Protection Programs Completed" count from 4 to 6 (4 + 2)
6. Logs the change in audit trail with program details
7. Shows in outcome reports as automated data with source attribution

### Database Flow
```sql
-- 1. Program status changes in program_submissions
UPDATE program_submissions SET rating = 'completed' WHERE program_id = 123;

-- 2. System finds linked outcomes
SELECT outcome_id FROM program_outcome_links WHERE program_id = 123;

-- 3. Updates outcome data automatically
UPDATE sector_outcomes_data SET 
  data_json = JSON_SET(data_json, '$.total_value', total_value + 2),
  updated_at = NOW()
WHERE outcome_id = 456;

-- 4. Creates audit log entry
INSERT INTO audit_logs (action, description, user_id, created_at) 
VALUES ('outcome_auto_updated', 'Program completion auto-updated outcome', 1, NOW());
```
```

## Expected Benefits
1. **Automatic Data Consistency**: TPA program completions automatically reflect in TPA outcome metrics
2. **Reduced Manual Work**: No need to manually update outcomes when programs complete
3. **Clear Relationships**: Visual indication of which programs contribute to which outcomes
4. **Better Reporting**: More accurate outcome calculations based on actual program performance
5. **Scalability**: Easy to add new program-outcome relationships

## Risk Mitigation
- Maintain audit trails for all automated changes
- Allow manual override of automated calculations
- Implement validation to prevent circular dependencies
- Provide rollback capabilities for incorrect links

## Success Criteria
- [x] TPA programs automatically update TPA outcome metrics
- [x] Admin users can easily create/manage program-outcome links
- [x] Agency users can view program-outcome relationships
- [x] System maintains data integrity and audit trails
- [x] Performance remains acceptable with large datasets
