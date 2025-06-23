# Add Program Numbers to Reports Interface

## Overview
Update the reports generation interface to display program numbers in the program selection list, maintaining consistency with other program displays throughout the application.

## Tasks

### ✅ 1. API Update
- [x] Update `app/api/get_period_programs.php` to include `program_number` in JSON output
- [x] Ensure backward compatibility with existing functionality

### ✅ 2. Frontend Update  
- [x] Update JavaScript in `assets/js/report-generator.js` to display program number badges
- [x] Match the styling used in other program lists (info badge before program name)
- [x] Update data structures to store and pass program numbers throughout the interface
- [x] Update program container attributes to include program name and number
- [x] Ensure responsive behavior

### 3. Testing
- [ ] Test program selection interface displays program numbers correctly
- [ ] Verify programs without numbers still display properly
- [ ] Check that report generation still works as expected

## Notes
- Focus only on frontend display, not the generated PPTX reports
- Maintain consistency with program number display patterns used elsewhere
- Ensure optional program numbers are handled gracefully
