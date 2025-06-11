# Admin Programs Page Rewrite

## Problem Description
The current admin programs page needs to be rewritten to follow the filtering and search functionality structure exactly like the agency side programs page. This includes:

1. Implementing the same filter structure as agency side
2. Adding proper search functionality
3. Separating draft and submitted programs into different card sections
4. Replacing "draft" terminology with "unsubmitted" in the frontend while keeping backend functionality

## Implementation Plan

### Phase 1: Data Structure Changes
- [x] Analyze current admin programs data retrieval
- [x] Understand agency side programs structure
- [x] Modify data retrieval to separate programs by submission status
- [x] Update program query to include proper draft/submitted status

### Phase 2: Frontend Structure
- [x] Create separate card sections for "Unsubmitted Programs" and "Submitted Programs"
- [x] Implement filtering system for each card section separately
- [x] Add search functionality similar to agency side
- [x] Replace "Draft" terminology with "Unsubmitted" in UI

### Phase 3: Filter Implementation
- [x] Implement search filters for both sections
- [x] Add rating/status filters
- [x] Add program type filters (assigned vs agency-created)
- [x] Add sector and agency filters
- [x] Implement filter reset functionality

### Phase 4: JavaScript Functionality
- [x] Create client-side filtering for both tables
- [x] Implement search functionality
- [x] Add filter badges/indicators
- [x] Handle table sorting and pagination

### Phase 5: Testing and Refinement
- [x] Test all filter combinations
- [x] Verify search functionality
- [x] Test program actions (edit, delete, resubmit/unsubmit)
- [x] Verify proper data separation between drafts and submitted
- [x] Added CSS styling for improved UI
- [x] Fixed database query to include is_assigned field

## Completed Implementation

The admin programs page has been successfully rewritten to follow the agency side structure with the following improvements:

1. **Separate Card Sections**: Programs are now separated into "Unsubmitted Programs" and "Submitted Programs" cards
2. **Independent Filtering**: Each section has its own set of filters that work independently
3. **Improved Search**: Real-time search functionality similar to agency side
4. **Terminology Updates**: "Draft" has been replaced with "Unsubmitted" in the UI while maintaining backend compatibility
5. **Enhanced UI**: Added filter badges, better styling, and improved user experience
6. **Full Functionality**: All program actions (view, edit, delete, submit/unsubmit) are preserved and working

## Key Features Implemented

- ✅ Real-time search filtering for both sections
- ✅ Rating/status filtering
- ✅ Program type filtering (Assigned vs Agency-Created)
- ✅ Sector and agency filtering
- ✅ Filter reset functionality
- ✅ Filter badges to show active filters
- ✅ Table sorting for all columns
- ✅ Responsive design
- ✅ Proper program action buttons (Submit/Unsubmit)
- ✅ Program counts in card headers

### Draft vs Submitted Logic
- **Draft/Unsubmitted**: Programs where `is_draft = 1` in `program_submissions` table
- **Submitted**: Programs where `is_draft = 0` in `program_submissions` table
- Frontend will show "Unsubmitted" but backend will continue using `is_draft` field

### Filter Structure
Following agency side pattern:
- Search input with icon
- Rating/Status filter dropdown
- Program Type filter (Assigned/Agency-Created)
- Reset button for each section

### Data Flow
1. Get all programs with latest submissions
2. Separate into unsubmitted and submitted arrays
3. Render separate card sections
4. Apply client-side filtering to each section independently
