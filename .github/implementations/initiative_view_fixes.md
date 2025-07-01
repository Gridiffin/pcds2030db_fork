# Initiative View Fixes and Improvements

## Issues to Address

### 1. Fix Hardcoded "10 years" Text
- [ ] Remove hardcoded "10 years" text in progress sublabel
- [ ] Calculate actual initiative duration dynamically
- [ ] Display elapsed vs total years based on actual dates

### 2. Center Progress Percentage
- [ ] Add CSS to center the progress percentage properly
- [ ] Ensure timeline progress bar is visually balanced

### 3. Add Recent Activity Feed
- [ ] Create recent activity section for initiative programs
- [ ] Display latest program submissions related to the initiative
- [ ] Show submission dates, program names, and submission types
- [ ] Implement pagination or limit for recent activities

### 4. Remove Quick Actions Section
- [ ] Remove the entire quick actions card from sidebar
- [ ] Clean up any related CSS if needed

## Implementation Plan

### Phase 1: Fix Timeline Display
- Fix hardcoded text and make it dynamic
- Center the progress elements

### Phase 2: Remove Quick Actions
- Remove quick actions section from sidebar

### Phase 3: Add Recent Activity Feed
- Query recent program submissions for this initiative
- Create activity feed UI component
- Display activity items with proper formatting

### Phase 4: Testing
- Test with different initiatives
- Verify dynamic calculations work correctly
- Ensure responsive design is maintained

## Files to Modify
- `app/views/agency/initiatives/view_initiative.php`
- `assets/css/pages/initiative-view.css`
- May need to query submission/outcome tables for activity feed

## Database Queries Needed
- Recent program submissions/outcomes for initiative programs
- Submission dates and types
- Program names and agencies for activity context
