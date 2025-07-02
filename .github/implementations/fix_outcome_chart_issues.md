# Fix Chart Issues in View Outcome Page

## Problems
1. Data is not being passed correctly to the chart due to inconsistent column data formats
2. The chart appears too small on the page

## Analysis
- The JavaScript code for chart initialization doesn't handle the case where column data is a string rather than an object
- The chart container has a fixed height that may be too small
- We need to add proper type checking for column data just like we did in the PHP side

## Solution Steps

### Step 1: Fix the data processing in JavaScript
- [x] Update the `datasets` mapping function to check if each column is an object or a string
- [x] Use column directly as ID when it's a string, or column['id'] when it's an object
- [x] Same for label: use column as label when it's a string, or column['label'] when it's an object
- [x] Apply the same fix to the CSV download functionality

### Step 2: Increase the chart size
- [x] Modify the chart container's height from 400px to 600px for better visibility
- [x] Added margin at the bottom for better spacing

### Step 3: Test the changes
- [x] Verify that the chart properly displays data when columns are strings
- [x] Verify that the chart properly displays data when columns are objects
- [x] Confirm that the chart size is appropriate and displays well

## Files Modified
- `app/views/agency/outcomes/view_outcome.php` - Updated JavaScript chart initialization code and chart container CSS

## Changes Made
1. Added type checking for column data in the chart initialization code
2. Used conditional logic to extract column IDs and labels based on data type
3. Applied the same fix to the CSV export functionality
4. Increased chart container height from 400px to 600px
5. Added margin at the bottom of the chart for better spacing
