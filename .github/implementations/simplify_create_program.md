# Simplify Create Program Functionality - Updated Approach

## Overview
Simplify the create program functionality to create programs as "templates" (like initiatives) without any reporting period or submission. Users will add progress reports for specific periods later when they need to report progress.

## Current State
- Complex wizard interface with 3 steps (Basic Info, Targets, Review)
- Handles targets creation during program creation
- Auto-save functionality with targets
- Complex validation and processing
- Creates initial submission with reporting period

## Target State
- Simple single-page form for basic program information only
- No reporting period selection during creation
- No targets or attachments during initial creation
- No initial submission created
- Programs exist as templates until users add progress reports for specific periods

## Tasks

### Phase 1: Simplify Frontend
- [x] Remove wizard interface and progress indicators
- [x] Remove Step 2 (Targets) completely
- [x] Remove Step 3 (Review) completely
- [x] Simplify to single form with basic fields only:
  - [x] Program Name (required)
  - [x] Initiative Selection (optional)
  - [x] Program Number (auto-generated or manual)
  - [x] Brief Description (optional)
  - [x] Start Date (optional)
  - [x] End Date (optional)
- [x] Remove Reporting Period field completely
- [x] Remove auto-save functionality
- [x] Simplify form submission to direct save
- [x] Update page title and description

### Phase 2: Simplify Backend
- [x] Create new simplified function `create_simple_program()` that only handles basic program creation
- [x] Update function to NOT create initial submission
- [x] Remove targets processing from program creation
- [x] Remove auto-save functionality
- [x] Simplify validation to only essential fields
- [x] Remove period_id validation and processing
- [x] Remove complex wizard functions

### Phase 3: Update Program Management
- [ ] Update program list to show programs without submissions
- [ ] Add "Add Submission" functionality for programs
- [ ] Update edit program to allow period selection
- [ ] Ensure proper redirects after creation

### Phase 4: Testing
- [ ] Test simplified program creation
- [ ] Verify program appears in programs list without submissions
- [ ] Test adding submissions for different periods
- [ ] Test validation and error handling
- [ ] Verify redirects work properly

## Benefits
- Programs are not tied to specific reporting periods
- Cleaner separation between program definition and period-specific submissions
- Matches real-world workflow where programs are ongoing
- Users can add submissions for any period at any time
- Simpler initial program creation process

## Notes
- Programs will exist as templates until users add progress reports
- Submissions can be added for any reporting period (quarter, half, yearly)
- Targets and attachments are added when creating/editing submissions
- This approach is more flexible and matches how initiatives work 