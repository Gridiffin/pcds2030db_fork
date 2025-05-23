# Fix Report Generator Script Loading Issues

## Problem
The report generation page is failing to load required JavaScript files in the generate_reports.php file with the following errors:

```
Loading failed for the <script> with source "http://localhost/pcds2030_dashboard/app/assets/js/report-modules/report-slide-styler.js".
Loading failed for the <script> with source "http://localhost/pcds2030_dashboard/app/assets/js/report-modules/report-api.js".
Loading failed for the <script> with source "http://localhost/pcds2030_dashboard/app/assets/js/report-modules/report-slide-populator.js".
Loading failed for the <script> with source "http://localhost/pcds2030_dashboard/app/assets/js/report-modules/report-ui.js".
Loading failed for the <script> with source "http://localhost/pcds2030_dashboard/app/assets/js/report-generator.js".
```

The issue is that the script paths are incorrect. The paths in the script tags use:
```
../../assets/js/report-modules/...
```

But the actual structure of the project shows that the files are located in:
```
/assets/js/report-modules/...
```

The issue is that the include statements attempt to use a relative path that's incorrect based on the current file location. The script is looking in `/app/assets/js/` but the files are actually in the root `/assets/js/` directory.

## Solution

- [x] Identify the script inclusion issue in generate_reports.php
- [x] Update script references to use the APP_URL constant to ensure paths are absolute and correct
- [x] Verify that all script files are available in the expected locations
- [x] Test that the report generator functions correctly after the changes

## Implementation

The current implementation in generate_reports.php uses:

```html
<script src="../../assets/js/report-modules/report-slide-styler.js"></script>
<script src="../../assets/js/report-modules/report-api.js"></script>
<script src="../../assets/js/report-modules/report-slide-populator.js"></script>
<script src="../../assets/js/report-modules/report-ui.js"></script>
<script src="../../assets/js/report-generator.js"></script>
```

We need to replace these with absolute paths using the APP_URL constant:

```html
<script src="<?php echo APP_URL; ?>/assets/js/report-modules/report-slide-styler.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/report-modules/report-api.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/report-modules/report-slide-populator.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/report-modules/report-ui.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/report-generator.js"></script>
```

This ensures the paths are correct regardless of the file location within the project structure.
