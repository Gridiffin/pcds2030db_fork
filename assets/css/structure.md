# CSS Directory Structure

This directory contains all CSS files for the PCDS2030 Dashboard application.

## Directory Structure:

- **bootstrap/**
  - bootstrap.min.css (Bootstrap framework CSS)
  - bootstrap-grid.min.css (Optional: Bootstrap grid system only)
  - bootstrap-utilities.min.css (Optional: Bootstrap utilities only)

- **vendors/**
  - datatables.min.css (DataTables styling)
  - select2.min.css (Select2 dropdown styling)
  - chart.min.css (Chart.js styling if needed)

- **custom/**
  - common.css (Shared styles across pages)
  - login.css (Login page specific styles)
  - style.css (Main application styles)
  - admin.css (Admin panel specific styles)
  - agency.css (Agency panel specific styles)

## Usage Guidelines:

1. Do not modify vendor CSS files directly
2. Place all custom styling in the custom/ directory
3. Import common.css at the top of each custom CSS file
4. Use CSS variables defined in common.css for consistent colors
