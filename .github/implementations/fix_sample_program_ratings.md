# Fix Sample Program Ratings Implementation Plan

## Problem Description
The 5 sample programs recently populated in the database have incorrect rating values. The sample data uses numeric rating scale (1-5) with status values like "in-progress", "on-track", "delayed", "ahead-of-schedule", "completed", but the actual system uses text-based rating values.

## Actual Rating System
Based on system analysis, the correct rating values are:
- `target-achieved` - "Monthly Target Achieved" (Green/Success)
- `on-track-yearly` - "On Track for Year" (Yellow/Warning)  
- `severe-delay` - "Severe Delays" (Red/Danger)
- `not-started` - "Not Started" (Gray/Secondary)

## Implementation Steps

### Phase 1: Database Investigation
- [x] Connect to pcds2030_dashboard database
- [x] Retrieve current sample program data (IDs 165-169)
- [x] Analyze current incorrect rating structure

### Phase 2: Rating Correction
- [x] Update sample program ratings with correct values
- [x] Ensure proper JSON structure in content_json field
- [x] Distribute ratings across different categories for variety

### Phase 3: Validation
- [x] Verify updated data in database
- [x] Test rating display in admin interface
- [x] Confirm rating functionality works correctly

### Phase 4: Documentation
- [x] Document the correction process
- [x] Update sample data structure for future reference

## Sample Program Rating Distribution
After correction, the 5 sample programs (across 10 submissions) now have the following rating distribution:
- **target-achieved**: 4 submissions (Forest Conservation, Reforestation Project x2, Wildlife Habitat)
- **on-track-yearly**: 4 submissions (Forest Conservation, Timber Management, Wildlife Habitat, Forest Research)  
- **severe-delay**: 1 submission (Timber Management)
- **not-started**: 1 submission (Forest Research)

## Updated Sample Data Structure
Each program submission now has the correct JSON structure in `content_json`:
```json
{
  "overall_rating": "target-achieved|on-track-yearly|severe-delay|not-started",
  "progress_percentage": 0-100,
  "targets": { ... },
  "achievements": [ ... ],
  "challenges": [ ... ],
  "next_quarter_goals": [ ... ]
}
```

## Expected Outcome
Sample programs will have correct rating values that match the actual system implementation, ensuring proper display and functionality in the dashboard.