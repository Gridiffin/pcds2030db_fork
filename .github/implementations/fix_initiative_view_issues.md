# Fix Initiative View Issues

## Issues to Address

### 1. Remove Timeline Progress Bar
- [ ] Remove the timeline-progress-wrapper div completely from the first metric card
- [ ] Keep only the percentage, label, and sublabel

### 2. Fix Database Query Error
- [ ] Access database to study the actual table structure
- [ ] Identify correct table names for activity feed
- [ ] Update the query to use the correct table structure
- [ ] Test the query to ensure it works

## Implementation Steps

### Step 1: Remove Progress Bar
- Remove the entire timeline-progress-wrapper section
- Keep the metric card clean with just the percentage display

### Step 2: Database Investigation
- Use DBCode extension to examine database structure
- Find tables related to program submissions/outcomes
- Understand the relationships between tables
- Create proper query for recent activity

### Step 3: Update Activity Feed Query
- Replace the current query with correct table names
- Ensure proper joins and relationships
- Add error handling for database queries

## Files to Modify
- `app/views/agency/initiatives/view_initiative.php`

## Notes
- Timeline progress bar removal requested by user
- Database error indicates 'outcomes' table doesn't exist
- Need to investigate actual table structure before writing queries
