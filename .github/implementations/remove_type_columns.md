# Remove Type Columns from Programs Overview Tables

## Problem
The user wants to remove the "Type" columns from both tables in the Programs Overview section of the admin dashboard.

## Solution Steps

### ✅ Step 1: Identify the tables that need modification
- Assigned Programs table
- Agency-Created Programs table
- Both tables currently have 4 columns: Program Name, Agency, Created Date, Type

### ✅ Step 2: Update Assigned Programs table
- Remove the "Type" header column
- Remove the "Type" data column with the "Assigned" badge
- Update column group widths to redistribute space

### ✅ Step 3: Update Agency-Created Programs table
- Remove the "Type" header column  
- Remove the "Type" data column with the "Agency-Created" badge
- Update column group widths to redistribute space

### ✅ Step 4: Adjust column widths
- Redistribute the 12% width from the Type column across the remaining 3 columns
- Update colgroup percentages for better spacing
- New distribution: Program Name (45%), Agency (30%), Created Date (25%)

## Expected Result
Both tables will show only: Program Name, Agency, and Created Date columns, providing a cleaner view since the program type is already indicated by the section headers and badges.
