# Simple Before/After Change Tracking System

## Problem
Need to record EXACT changes made during each save session, not just which fields changed. For example:
- Target changed from "1000 ha by today" → "5000 ha by today"  
- Program name changed from "Old Program Name" → "New Program Name"
- Start date changed from "2025-01-01" → "2025-02-01"

## Audit Log vs Change Tracking
**Audit Log**: Records WHO did WHAT action and WHEN
- Example: "User admin updated Program #123 at 2025-06-25 10:30"

**Change Tracking**: Records EXACT VALUES that changed during the action  
- Example: "Target 1: '1000 ha by today' → '5000 ha by today'"

## Solution Approach

### Option A: Store Changes in Submission JSON ✅ CHOSEN
- Add `changes_made` field to existing `content_json` in `program_submissions` table
- No new database tables needed
- Changes tied directly to the submission that made them
- Simple to implement and display

### Implementation Strategy
1. **Capture Current State** before any changes are made
2. **Compare States** after form submission to detect exact changes  
3. **Store Changes** in submission JSON with before/after values
4. **Display Changes** in history table showing exact before/after text

## Tasks
- [x] Create function to capture current program state before saving
- [x] Create function to compare states and generate before/after changes
- [x] Modify save process to capture before state
- [x] Modify save process to generate and store changes in submission JSON
- [x] Update edit history display to show specific before/after changes
- [x] Add CSS styles for change tracking display
- [x] Fix fatal error: undefined function get_enhanced_program_edit_history()
- [x] Remove pagination code and update display logic to work with current function structure
- [ ] Test with various field changes (targets, program info, dates)
- [ ] Handle special cases (new targets, deleted targets, empty fields)

## Implementation Details

### Step 1: Capture Current State Function
```php
function get_current_program_state($program_id) {
    // Get program table data + latest submission content
    // Return complete current state for comparison
}
```

### Step 2: Compare and Generate Changes
```php
function generate_field_changes($before_state, $after_state) {
    // Compare each field
    // Return array of specific changes with before/after values
}
```

### Step 3: Data Storage in Submission JSON
Add `changes_made` field to content_json:
```json
{
    "rating": "in-progress",
    "targets": [...],
    "changes_made": [
        {
            "field": "target_1",
            "field_label": "Target 1",
            "before": "1000 ha by today", 
            "after": "5000 ha by today"
        }
    ]
}
```

### Step 4: Display Before/After in History Table
Replace generic badges with specific change descriptions:
- "Target 1: '1000 ha by today' → '5000 ha by today'"
- "Program Name: 'Old Name' → 'New Name'" 
- "Start Date: '2025-01-01' → '2025-02-01'"

### Fields to Track
- **Program Info**: name, number, description, start/end dates
- **Assignment**: owner agency, sector, assignment status  
- **Content**: rating, remarks, brief description
- **Targets**: individual target text and status descriptions
- **Permissions**: edit permissions

### Special Cases to Handle
- **New Targets**: "Added Target 3: 'New target text'"
- **Deleted Targets**: "Removed Target 2: 'Old target text'"  
- **Empty Fields**: Show as "(empty)" or "(not set)"
- **Target Reordering**: Handle target number changes properly

## Files to Modify
- `app/views/admin/programs/edit_program.php` (main display logic and save process)
- `app/lib/agencies/programs.php` (change tracking functions)
