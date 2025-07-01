# Gantt C- [x] **1. Analyze the Mock Design**
  - Review the mock's color scheme, grid layo- [x] **4. Scaffold the Gantt Chart Container**
  - Add a `<div id="gantt_here"></div>` in the initiatives file where the chart should appear.
  - Ensure proper placement according to the mock layout.

- [x] **5. Configure dhtmlxGantt**
  - Set up the Gantt chart's columns, time scale, and grid to match the mock.
  - Customize colors, bar styles, and status indicators using dhtmlxGantt's templates and CSS overrides.
  - Implement tooltips, milestones, and legends as per the mock.

- [x] **6. Data Integration**
  - Prepare sample or real initiative data in the format required by dhtmlxGantt.
  - Ensure data is loaded dynamically (AJAX or server-side as needed).

- [x] **7. Responsive Design**
  - Test and adjust the Gantt chart for different screen sizes.
  - Use dhtmlxGantt's responsive features and add custom CSS if needed.ators, and responsive behavior.
  - List all visual and interactive requirements.t Implementation Plan for Agency Initiatives

## Problem
The current initiatives dashboard uses a custom, raw-CSS Gantt chart. We need to implement a maintainable, scalable Gantt chart for the agency side, matching the mock’s look and feel, but using the dhtmlxGantt library for better maintainability and features.

---

## Step-by-Step Solution

- [ ] **1. Analyze the Mock Design**
  - Review the mock’s color scheme, grid layout, status indicators, and responsive behavior.
  - List all visual and interactive requirements.

- [x] **2. Prepare the Agency Initiatives File**
  - Identify the correct file (e.g., `initiatives.php` or similar) for the agency side.
  - Ensure the file structure and includes are consistent with project conventions.

- [x] **3. Add dhtmlxGantt to the Project**
  - Download or use CDN for the latest dhtmlxGantt JS and CSS.
  - Import dhtmlxGantt in the centralized CSS/JS reference files (e.g., `main.css`, `main.js`).
  - Document the addition in the project.

**Database Structure Analysis:**
- `initiatives` table: initiative_id, initiative_name, initiative_number, initiative_description, start_date, end_date, is_active
- `programs` table: program_id, program_name, program_number, initiative_id, owner_agency_id, start_date, end_date
- `program_submissions` table: program_id, period_id, content_json (contains targets array)
- `reporting_periods` table: period_id, year, quarter, start_date, end_date, status

**Timeline Structure:**
- **Gantt Chart Timeline**: Hierarchical timeline with **Year header spanning quarters**
- **Timeline Display**:
  ```
  |    2024    |    2025    |    2026    |
  |Q1|Q2|Q3|Q4|Q1|Q2|Q3|Q4|Q1|Q2|Q3|Q4|
  
  Initiative: Biodiversity Conservation
  ├─ Program: Forest Protection
  │  ├─ Target: Plant 1000 trees        [██] (Q2 2024)
  │  ├─ Target: Ranger training         [██] (Q3 2024)
  │  └─ Target: Wildlife monitoring        [██] (Q1 2025)
  └─ Program: Marine Conservation  
     ├─ Target: Coral restoration                [██] (Q2 2025)
     ├─ Target: Fish breeding                    [██] (Q3 2025)
     └─ Target: Beach cleanup                       [██] (Q4 2025)
  ```
  
  **Row Structure:**
  - **Initiative Row**: Spans full timeline (no bars, just container)
    - Display: "Initiative Name" (no badge needed)
  - **Program Rows**: Group header (no timeline bars, just labels)
    - Display: "[P001] Program Name" (program_number as badge)
  - **Target Rows**: Individual bars in specific quarters based on period_id
    - Display: "[T001] Target Description" (target_number as badge)
  ```
- **Initiatives**: Provide overall timeframe (start year to end year)
- **Programs**: Group targets but don't have individual timeline bars
- **Targets**: Display in the quarterly period they belong to (based on program_submissions.period_id)
  - Each program_submission has a period_id linking to reporting_periods
  - Targets appear in their specific quarter (Q1, Q2, Q3, Q4)
  - No individual start/end dates needed - just quarter placement

**dhtmlxGantt Scale Configuration:**
```javascript
gantt.config.scales = [
  {unit: "year", step: 1, format: "%Y"},      // Top row: 2024, 2025, 2026
  {unit: "quarter", step: 1, format: "Q%q"}  // Bottom row: Q1, Q2, Q3, Q4
];
```

**Targets JSON Structure (from submission_id 386):**
```json
{
  "rating": "not-started|in-progress|completed",
  "targets": [
    {
      "target_number": "",
      "target_text": "Target description",
      "status_description": "",
      "target_status": "completed|in-progress|not-started",
      "start_date": null,
      "end_date": null
    }
  ],
  "remarks": "",
  "brief_description": "",
  "program_name": "Program Name",
  "program_number": ""
}
```

- [ ] **4. Scaffold the Gantt Chart Container**
  - Add a `<div id="gantt_here"></div>` in the initiatives file where the chart should appear.
  - Ensure proper placement according to the mock layout.

- [ ] **5. Configure dhtmlxGantt**
  - Set up the Gantt chart’s columns, time scale, and grid to match the mock.
  - Customize colors, bar styles, and status indicators using dhtmlxGantt’s templates and CSS overrides.
  - Implement tooltips, milestones, and legends as per the mock.

- [ ] **6. Data Integration**
  - Prepare sample or real initiative data in the format required by dhtmlxGantt.
  - Ensure data is loaded dynamically (AJAX or server-side as needed).

- [ ] **7. Responsive Design**
  - Test and adjust the Gantt chart for different screen sizes.
  - Use dhtmlxGantt’s responsive features and add custom CSS if needed.

- [ ] **8. Accessibility & Usability**
  - Ensure keyboard navigation and screen reader compatibility.
  - Add ARIA labels and test for accessibility.

- [ ] **9. Documentation**
  - Comment code and document configuration choices.
  - Update project documentation to reflect the new Gantt chart implementation.

- [ ] **10. Clean Up**
  - Remove any obsolete raw CSS or JS related to the old Gantt chart.
  - Delete any test files created during development.

---

## Notes & Suggestions

- **Why dhtmlxGantt?**  
  It provides a robust, feature-rich, and customizable Gantt chart with good documentation and support for theming.
- **Customization:**  
  Use dhtmlxGantt’s CSS and template system to closely match the mock’s visual style.
- **Centralized Imports:**  
  Always import new CSS/JS in the main reference files as per project standards.
- **Maintainability:**  
  Avoid inline styles; use external CSS and dhtmlxGantt’s configuration for all customizations.

---

## Next Steps

- Await approval of this plan.
- Once approved, proceed step by step, marking each task as complete in this