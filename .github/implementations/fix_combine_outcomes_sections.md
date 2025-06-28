# Implementation: Combine Outcomes Sections into "Other Outcomes"

## Overview
Fix the issue where the submitted outcomes section is still finding draft outcomes and combine both submitted and draft outcomes sections into a single unified "Other Outcomes" section on the agency side.

## Issues to Address

### ✅ 1. Draft Outcomes Appearing in Submitted Section
- [ ] Investigate why draft outcomes are showing in submitted outcomes section
- [ ] Check the query logic for `get_agency_sector_outcomes()` function
- [ ] Ensure proper filtering between submitted and draft outcomes

### ✅ 2. Combine Sections into "Other Outcomes"
- [ ] Replace separate "Submitted Outcomes" and "Draft Outcomes" sections
- [ ] Create single unified "Other Outcomes" section
- [ ] Merge the outcome arrays properly
- [ ] Update section headers and badges

## Files to Modify

### Primary File
- `app/views/agency/outcomes/submit_outcomes.php` - Main outcomes listing page

### Investigation Files
- `app/lib/functions.php` - Check `get_agency_sector_outcomes()` function
- `app/lib/agencies/index.php` - Check `get_draft_outcome()` function

## Implementation Steps

### Step 1: ✅ Investigate Current Query Logic
- [ ] Examine `get_agency_sector_outcomes()` function
- [ ] Examine `get_draft_outcome()` function
- [ ] Identify why drafts appear in submitted section

### Step 2: ✅ Fix Query Logic
- [ ] Ensure proper filtering in outcome queries
- [ ] Fix any incorrect `is_draft` filtering
- [ ] Test queries to verify correct separation

### Step 3: ✅ Combine Sections in UI
- [ ] Replace current section structure with unified "Other Outcomes"
- [ ] Merge `$regular_outcomes` array properly
- [ ] Update section headers and counts
- [ ] Remove duplicate outcome entries

### Step 4: ✅ Validate Changes
- [ ] Test that outcomes appear only once in the correct section
- [ ] Verify no duplicate entries
- [ ] Check that all outcomes are accessible

## Expected Outcome

- Single "Other Outcomes" section containing all non-important outcomes
- No duplicate entries between sections
- Proper filtering to prevent drafts from appearing in wrong sections
- Clean, unified UI experience

---
**Status**: 🚧 In Progress
**Priority**: High
**Impact**: Agency UI, Outcome Management
