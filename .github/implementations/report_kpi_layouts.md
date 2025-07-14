# Report KPI Layouts Overhaul

## Problem
The FMU/TPA (Certification of FMU & FPMU) KPI box in the PPTX report should use the 'comparison' layout type instead of 'simple', to match the intended visual style and grouping in the generated report.

## Solution Steps

- [x] Locate the KPI box construction logic in `app/api/report_data.php`.
- [x] Change the `layout_type` for the `kpi_certification` KPI box from `simple` to `comparison`.
- [x] Ensure the API now returns the correct layout type for frontend and PPTX generation.
- [x] Enhance comparison layout KPI boxes to better support long texts:
    - Increase box height for comparison layout
    - Lift vertical position for comparison layout
    - Ensure fit:shrink and breakLine are always enabled for all text fields

## Status
All steps completed. The FMU/TPA and Global Recognition KPI boxes now support long texts and will not overflow or be cut off in the PPTX. 