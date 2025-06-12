# Remove View Details Button from Sector Overview Card

## Problem
The user wants to remove the "View Details" button from the Sector Overview card header in the admin dashboard.

## Solution Steps

### ✅ Step 1: Identify the Sector Overview section
- Located in the admin dashboard under the MULTI_SECTOR_ENABLED conditional section
- Has a card header with "Sector Overview" title and "View Details" button

### ✅ Step 2: Examine current structure
- Found two Sector Overview sections:
  - MULTI_SECTOR_ENABLED: "Sector Overview" card
  - Single sector mode: "Forestry Sector Overview" card
- Both had "View Details" buttons in their headers

### ✅ Step 3: Remove the View Details button
- Removed button from MULTI_SECTOR_ENABLED section
- Removed button from single sector (Forestry) section
- Simplified both header structures to show only titles

### ✅ Step 4: Clean up header classes
- Removed `d-flex justify-content-between align-items-center` classes
- Headers now use simple card-header class with title only

## Expected Result
The Sector Overview card will have a clean header with only the "Sector Overview" title, removing the "View Details" button for a simpler interface.
