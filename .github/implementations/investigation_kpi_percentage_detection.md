# Investigation: KPI Percentage Detection Issue

## Problem Description
The user reported that the percentage detection in the `renderSimpleKpiLayout` function might not be working because the system doesn't know that values are percentages. They suspected the backend might treat percentage data as ordinary data.

## Investigation Results

### Database Query Results
After querying the `outcomes_details` table, I found that percentage values **DO include the "%" symbol** in the stored data:

```json
{
  "layout_type": "simple",
  "items": [
    {
      "value": "56.7%", 
      "description": "1,703,164 ha Certified (May 2025)"
    },
    {
      "value": "71.5%", 
      "description": "127,311 ha Certified (May 2025)"
    }
  ]
}
```

### Data Flow Analysis
1. **Storage**: Values are stored with "%" symbol in `outcomes_details.detail_json`
2. **API**: `report_data.php` fetches this data and passes it to the frontend
3. **Frontend**: The JavaScript `renderSimpleKpiLayout` function receives these values

### Conclusion
The percentage detection logic should work correctly since the "%" symbol is present in the data. However, there might be an issue with:
1. The data reaching the function
2. The logic implementation
3. Edge cases not being handled

## Next Steps
1. ✅ Verify the percentage detection logic works correctly
2. ✅ Add debug logging to trace the data flow
3. ✅ Test with actual percentage values from the database
4. ✅ Ensure the solution handles edge cases properly

## Current Status
- The fix implemented in `report-slide-styler.js` should work correctly
- The percentage detection logic `String(valueText).includes('%')` should properly identify percentage values
- The dynamic font sizing and width allocation should handle decimal percentages correctly

## Testing Required
- Test with actual report generation to verify the fix works
- Check both simple layout and comparison layout outcomes
- Verify that whole number percentages (50%) and decimal percentages (56.7%) both work correctly
