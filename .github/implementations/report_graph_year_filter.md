# Report Graph Year Filter Implementation

## Requirement

The graph in report generation should only display data for the current and previous year.

## Implementation Plan

- [x] 1. Identify where the report graph data is generated and filtered.  
     _Located in `app/api/report_data.php` and outcome data from `app/lib/admins/outcomes.php`._
- [x] 2. Locate the code responsible for fetching and preparing the data for the graph.  
     _Outcome data is fetched with `get_all_outcomes()` and used for chart data in `report_data.php`._
- [x] 3. Update the logic to include only the current and previous year data in the graph.  
     _Chart data is now filtered to only include columns and rows for the current and previous year before being returned by the API._
- [ ] 4. Test the report generation to ensure only the required years are shown in the graph.  
     _Next: Test the report generation and verify the graph only displays current and previous year data._
- [ ] 5. Update this file to mark completed steps and summarize the changes.
