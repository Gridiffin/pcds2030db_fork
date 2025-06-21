# Fix Rating Dropdown Inconsistency Between Draft and Finalized Programs

## Problem Statement
The rating dropdown filters for draft programs and finalized programs have different options. Need to standardize them by removing extra options from the finalized programs dropdown to match the draft programs dropdown.

## Implementation Plan

### ✅ Tasks
- [x] Examine current rating dropdown options in draft programs section
- [x] Examine current rating dropdown options in finalized programs section
- [x] Identify extra options in finalized dropdown
- [x] Remove extra options from finalized dropdown to match draft dropdown
- [x] Test both dropdowns work correctly
- [x] Update implementation documentation

### Analysis Results

**Draft Programs Rating Filter (4 options):**
- All Ratings
- Monthly Target Achieved
- On Track for Year  
- Severe Delays
- Not Started

**Finalized Programs Rating Filter (Originally 7 options):**
- All Ratings
- Monthly Target Achieved
- On Track for Year
- ~~On Track~~ ← **Removed**
- ~~Delayed~~ ← **Removed**
- Severe Delays
- ~~Completed~~ ← **Removed**
- Not Started

### Fix Applied
Removed 3 extra options from finalized programs dropdown: "On Track", "Delayed", and "Completed" to match the draft programs dropdown exactly.

### Files to Modify
- `app/views/agency/programs/view_programs.php` - Update finalized programs rating filter options

### Expected Result
Both draft and finalized program rating dropdowns will have identical options for consistent user experience.
