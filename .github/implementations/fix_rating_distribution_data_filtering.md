# Fix Rating Distribution Data Filtering - Implementation Plan

## Problem
The rating distribution chart is not showing accurate data because:
1. Programs can have multiple submission records (for change history)
2. The current code uses the `rating` field from the `programs` table instead of the latest submission
3. We need to fetch the latest submission record for each program to get the most current rating

## Requirements
- Filter programs by initiative ID
- Get program submissions for those programs
- Find the latest submission for each program based on submission date/time
- Extract the rating from the latest submission record
- Calculate rating distribution based on these latest ratings

## Implementation Tasks

### 1. Database Schema Analysis
- ✅ Examine the `program_submissions` table structure
- ✅ Identify the rating field in submissions table (content_json -> $.rating)
- ✅ Understand the relationship between programs and submissions
- ✅ Check timestamp/date fields for determining latest submission (updated_at)

### 2. Query Development
- ✅ Create query to get programs for the initiative
- ✅ Join with program_submissions table using subquery to get latest submission
- ✅ Filter to get only the latest submission per program using MAX(updated_at)
- ✅ Extract rating from the submission record using JSON_EXTRACT

### 3. Data Processing Update
- ✅ Replace current rating calculation logic with new query
- ✅ Update the rating distribution calculation to use latest submission ratings
- ✅ Ensure backward compatibility for programs without submissions (fallback to 'not-started')
- ✅ Handle edge cases (no submissions, invalid ratings)
- ✅ Update program listing in sidebar to show correct rating status

### 4. Testing and Validation
- ✅ Test with programs that have multiple submissions
- ✅ Verify the chart shows correct latest ratings
- ✅ Test with programs that have no submissions (fallback to 'not-started')
- ✅ Validate the rating distribution percentages
- ✅ Test query syntax and performance
- ✅ Verify health score calculation uses updated ratings

## Technical Approach
1. ✅ Use LEFT JOIN to connect programs with their latest submissions
2. ✅ Use MAX(updated_at) with subquery to get the latest submission per program
3. ✅ Fall back to 'not-started' if no submission exists
4. ✅ Update both the chart data and health score calculation
5. ✅ Use JSON_UNQUOTE(JSON_EXTRACT()) to extract rating from content_json

## Expected Outcome
The rating distribution chart will show accurate data based on the latest program submission ratings, providing users with current and reliable program status information.

## Implementation Complete ✅
All tasks have been completed successfully. The rating distribution chart now uses the latest submission data from the `program_submissions` table instead of outdated program table data.
