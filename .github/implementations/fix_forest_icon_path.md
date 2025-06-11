# Fix Forest Icon Path Issue in PPTX Report Generation

## Problem Description
The forest icon is not loading correctly in the PPTX slide reports. The error indicates that the path to the forest icon is incorrect when PptxGenJS tries to load the image.

## Current Issue
- **File**: `/assets/js/report-modules/report-slide-populator.js`
- **Line**: 382
- **Current Path**: `'assets/images/forest-icon.png'`
- **Error**: The icon doesn't load properly during PPTX generation

## Root Cause Analysis
PptxGenJS requires either:
1. An absolute URL for external images
2. A base64 encoded image
3. A path that's correctly resolved from the execution context

The current relative path may not resolve correctly when JavaScript executes from the report generation page.

## Solution Steps

### ✅ Step 1: Create documentation file
- [x] Document the problem and solution approach

### ✅ Step 2: Investigate PptxGenJS image requirements
- [x] Check how PptxGenJS handles image paths
- [x] Determine if absolute URL is needed
- [x] Test different path formats

### ✅ Step 3: Fix the icon path
- [x] Convert to absolute URL format using ReportGeneratorConfig.appUrl
- [x] Add fallback to window.APP_URL and window.location.origin
- [x] Test the fix with report generation

### 🔄 Step 4: Test and verify
- [ ] Generate a test report
- [ ] Verify forest icon appears in the slide
- [ ] Confirm no console errors

## Solution Implemented
Changed the forest icon path from a relative path to an absolute URL:

**Before:**
```javascript
ReportStyler.addSectorIcon(slide, pptx, themeColors, 'assets/images/forest-icon.png');
```

**After:**
```javascript
// Use absolute URL for PptxGenJS image loading
const baseUrl = window.ReportGeneratorConfig?.appUrl || window.APP_URL || window.location.origin;
const iconPath = `${baseUrl}/assets/images/forest-icon.png`;
ReportStyler.addSectorIcon(slide, pptx, themeColors, iconPath);
```

This ensures PptxGenJS can properly load the image by providing a complete absolute URL.

## Implementation Notes
- The forest icon file exists at: `/assets/images/forest-icon.png`
- Report generation page is typically at: `/app/views/admin/reports/generate_reports.php`
- PptxGenJS may need absolute URLs for image loading

## Files to Modify
- `/assets/js/report-modules/report-slide-populator.js` (line 382)

## Testing
- Generate a forestry sector report
- Check if forest icon appears in the sector box
- Verify no JavaScript console errors related to image loading
