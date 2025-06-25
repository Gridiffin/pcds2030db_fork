# Simple Before/After Change Tracking System

## Problem
The current comprehensive change tracking only shows badges for which fields changed. We need a simple system that records the exact "before" and "after" values for each field that was modified during a saving session.

## Requirements
- Record exact changes: "Field X changed from 'old value' to 'new value'"
- Show clear before/after comparisons
- Apply to ALL fields in the edit program form
- Simple, readable format
- Store changes in a readable format in the database

## Examples of Desired Output
- **Target 1**: Changed from "1000 ha by today" to "5000 ha by today"  
- **Program Name**: Changed from "Forest Conservation" to "Enhanced Forest Conservation Program"
- **Start Date**: Changed from "2025-01-01" to "2025-02-15"
- **Owner Agency**: Changed from "Agency A" to "Agency B"

## Solution Approach
1. **Before Save**: Capture current state of all program fields
2. **After Save**: Compare with new values and record exact differences
3. **Storage**: Store changes in a simple, readable text format
4. **Display**: Show changes in clean before/after format

## Tasks
- [ ] Create function to capture current program state before saving
- [ ] Create function to compare before/after states and generate change descriptions
- [ ] Modify save process to record exact changes
- [ ] Update display logic to show before/after changes clearly
- [ ] Handle different field types (text, dates, dropdowns, arrays)
- [ ] Test with various field changes

## Implementation Details

### Fields to Track
- Program name
- Program number  
- Owner agency
- Sector
- Start date
- End date
- Assignment status
- Edit permissions
- Brief description
- All targets (individual target text and status)
- Rating/remarks

### Change Format
```
Program Name: "Old Program Name" → "New Program Name"
Target 1: "Old target text" → "New target text"  
Target 1 Status: "Old status" → "New status"
Start Date: "2025-01-01" → "2025-02-15"
```

### Storage
Store changes as JSON or formatted text in the program_submissions table or create a new changes column.

## Files to Modify
- `app/views/admin/programs/edit_program.php` (capture before state, trigger change tracking)
- `app/lib/agencies/programs.php` (change detection and storage functions)
- Database: Add changes storage mechanism

## Expected Outcome
Each edit session will show exactly what changed:
```
Changes made on June 25, 2025:
• Program Name: "Forest Program" → "Enhanced Forest Program" 
• Target 1: "1000 ha by today" → "5000 ha by today"
• Start Date: "2025-01-01" → "2025-02-15"
```
