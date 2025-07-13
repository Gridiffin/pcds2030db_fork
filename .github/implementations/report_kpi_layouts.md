# Report KPI Layouts Overhaul

## Problem
The FMU/TPA (Certification of FMU & FPMU) KPI box in the PPTX report should use the 'comparison' layout type instead of 'simple', to match the intended visual style and grouping in the generated report.

## Solution Steps

- [x] Locate the KPI box construction logic in `app/api/report_data.php`.
- [x] Change the `layout_type` for the `kpi_certification` KPI box from `simple` to `comparison`.
- [x] Ensure the API now returns the correct layout type for frontend and PPTX generation.

## Status
All steps completed. The FMU/TPA KPI box now uses the 'comparison' layout type in the report data API. 