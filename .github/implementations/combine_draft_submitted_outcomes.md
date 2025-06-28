# Implementation: Combine Draft and Submitted Outcomes into "Other Outcomes"

## Overview
Combine the separate "Submitted Outcomes" and "Draft Outcomes" sections in the agency outcomes page into a single unified section called "Other Outcomes".

## Current Issue
The agency outcomes page (`submit_outcomes.php`) currently has:
1. Important Outcomes section (working correctly)
2. Submitted Outcomes section (separate)
3. Draft Outcomes section (separate)

## Target Structure
After implementation:
1. Important Outcomes section (unchanged)
2. **Other Outcomes section** (combined draft + submitted outcomes)

## Implementation Steps

### Step 1: ✅ Analyze Current Structure
- [x] Examine the current submit_outcomes.php file
- [x] Identify where outcomes sections are displayed
- [x] Understand data flow and variables used

### Step 2: ✅ Modify Data Preparation
- [x] Update the PHP logic to combine outcomes and draft_outcomes into a single array
- [x] Remove duplicate metric_ids to avoid showing same outcome twice
- [x] Ensure proper data structure for the unified section

### Step 3: ✅ Update HTML Structure  
- [x] Replace the two separate sections with one "Other Outcomes" section
- [x] Update section headers and styling
- [x] Ensure proper table structure and actions

### Step 4: ✅ Test and Validate
- [x] Check for PHP syntax errors
- [x] Verify no duplicate outcomes are shown
- [x] Ensure all actions (View & Edit, Delete) work correctly

---
**Status**: ✅ Complete
**Priority**: Medium
**Impact**: Agency UI

## Summary of Changes

### Data Structure:
- Already had unified query that gets all outcomes regardless of draft status
- Used existing `$regular_outcomes` variable (non-important outcomes)
- Implemented duplicate prevention using `metric_id` as unique key

### UI Changes:
- **Removed**: "Submitted Outcomes" section
- **Removed**: "Draft Outcomes" section  
- **Added**: Single "Other Outcomes" section that combines both
- **Features**: 
  - Shows total count of all outcomes
  - Unified table with consistent actions (View & Edit, Delete)
  - Proper empty state handling
  - Context-aware description text

### Benefits:
- Eliminates confusion between draft and submitted states
- Prevents duplicate outcomes from appearing in multiple sections
- Simpler, cleaner UI that's easier to understand
- Consistent with the removal of submit/unsubmit functionality

## Files to Modify
- `app/views/agency/outcomes/submit_outcomes.php`

## Key Variables
- `$outcomes` - submitted outcomes
- `$draft_outcomes` - draft outcomes  
- `$regular_outcomes` - already combined (from previous changes)

---
**Status**: 🚧 In Progress
**Priority**: Medium
**Impact**: Agency UI
