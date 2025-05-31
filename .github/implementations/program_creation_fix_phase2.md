# Program Creation Fix - Phase 2

## PROBLEM SUMMARY

After initial fixes, several critical issues remain:

### Issues Identified:
1. **Description field not stored**: Main `description` field is not being saved to database
2. **Wrong data storage architecture**: Targets and status data currently stored in `programs.extended_data` but should be in `program_submissions.content_json`
3. **program_submissions table not updated**: No records created in submission table
4. **Duplicate programs created**: "Save Draft" button creates duplicate instead of updating existing program
5. **Data structure inconsistency**: Extended_data JSON structure differs between initial and duplicate records

### Current Incorrect Data Flow:
```
Form Data → programs.extended_data (WRONG)
```

### Correct Data Flow Should Be:
```
Basic Info (name, description, dates) → programs table
Targets, Status, Submission Data → program_submissions table
```

## ARCHITECTURE UNDERSTANDING

### programs table should store:
- program_id, program_name, description
- start_date, end_date
- owner_agency_id, sector_id
- Basic metadata (created_at, updated_at, etc.)
- extended_data: minimal program-level JSON data only

### program_submissions table should store:
- submission_id, program_id, period_id
- status, submitted_by, submission_date
- content_json: ALL submission-specific data (targets, status_descriptions, outcomes, etc.)

## IMPLEMENTATION PLAN

### Phase 1: Fix Data Storage Architecture ⚠️ URGENT
- [ ] Update `create_wizard_program_draft()` to store basic info in programs table only
- [ ] Create corresponding record in program_submissions table with targets/status in content_json
- [ ] Fix `update_program_draft_only()` to update program_submissions content_json
- [ ] Ensure description field is properly saved to programs.description

### Phase 2: Fix Duplicate Creation Issue ⚠️ URGENT  
- [ ] Investigate why "Save Draft" creates duplicate instead of updating
- [ ] Fix form submission handling to update existing program
- [ ] Ensure auto-save and final save use same program_id

### Phase 3: Fix Missing Description Storage ⚠️ URGENT
- [ ] Ensure programs.description field gets populated from form
- [ ] Fix any field mapping issues

### Phase 4: Data Cleanup ⚠️ URGENT
- [ ] Clean up incorrect extended_data entries
- [ ] Ensure proper JSON structure in program_submissions.content_json

### Phase 5: Testing ⚠️ READY FOR TESTING
- [ ] Test complete workflow without duplicates
- [ ] Verify data goes to correct tables
- [ ] Test auto-save functionality
- [ ] Verify description storage
- [ ] Test program_submissions table gets updated

## CRITICAL INSIGHTS

The main architectural misunderstanding was:
- I put submission-specific data (targets, status) in programs.extended_data
- This data should be in program_submissions.content_json
- programs table should only store basic program information
- program_submissions should handle all dynamic/submission content

This explains why program_submissions table isn't being updated and why the data architecture feels wrong.
