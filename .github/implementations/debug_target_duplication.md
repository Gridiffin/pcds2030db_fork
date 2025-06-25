# Debug Target Duplication Issue

## Problem Analysis
Getting "Target number appears multiple times in this submission" even when user only edits other fields. This suggests the issue is NOT in validation logic, but in how form data is being compiled/processed.

## Root Cause Investigation Needed
1. **Form rendering issue**: Same target rendered multiple times in HTML
2. **Data duplication during processing**: Targets getting duplicated during form compilation  
3. **JavaScript issues**: Frontend duplicating target entries
4. **Legacy data conversion**: Old→new format creating duplicates

## Debug Strategy
1. Add logging to see exactly what form data is being submitted
2. Check if targets are being rendered multiple times in HTML
3. Investigate JavaScript target management
4. Look at legacy data conversion logic

## Alternative Solutions
1. **Remove target number validation entirely** (temporary)
2. **Implement deduplication** before validation
3. **Fix root cause** of data duplication
4. **Different validation approach** that doesn't rely on form data

## Status: INVESTIGATING ROOT CAUSE
Need to trace the data flow: Database → Form Rendering → Form Submission → Validation
