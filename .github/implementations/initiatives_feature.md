# Vision & Implementation Plan: Evolving the PCDS2030 Dashboard

## 1. Introduction & Vision

This document outlines the strategic vision and detailed implementation plan for transforming the PCDS2030 Dashboard. The goal is to evolve the platform from a straightforward report generator into a dynamic, multi-layered strategic management system.

The current system effectively tracks individual "Programs" (Action Steps) within specific reporting periods. However, to fully support the strategic goals of PCDS 2030, we must introduce a more sophisticated structure that reflects the real-world hierarchy of government efforts and provides deeper analytical capabilities.

Our vision is to create a system that:
-   **Provides a hierarchical view:** Grouping programs under broader strategic **Initiatives**.
-   **Tracks long-term progress:** Allowing a single program to be monitored across multiple reporting periods without data duplication.
-   **Creates a clear "golden thread":** Directly linking the execution of programs to their impact on strategic **Outcomes**.
-   **Offers smarter analytics:** Differentiating between metrics that accumulate over time and those that are period-specific.

This plan details the necessary changes to the database, backend, and frontend to achieve this vision.

---

## 2. Feature: Initiatives Management

**The "Why":** The current flat structure of tracking only programs is limiting. To provide a high-level, strategic overview for leadership, we need to group related programs under overarching **Initiatives**. This aligns the dashboard with the PCDS 2030 framework and allows for better strategic analysis and reporting.

**The "How":**

-   **Database:**
    -   [x] Create a new `initiatives` table to store initiative details (e.g., `id`, `name`, `description`, `pillar_id`).
    -   [x] Add a foreign key `initiative_id` to the `programs` table to establish a clear one-to-many relationship.
-   **Backend:**
    -   [x] Develop standard CRUD (Create, Read, Update, Delete) API endpoints for managing initiatives.
    -   [x] Update all program-related business logic to be "initiative-aware."
    -   [x] Modify dashboard data endpoints to allow filtering by initiative.
    -   [x] Enhance the report generation logic to group programs by their parent initiative.
-   **Frontend:**
    -   [ ] Build a new UI section for administrators to manage initiatives.
    -   [ ] Add a dropdown menu to the program creation and editing forms to assign a program to an initiative.
    -   [ ] Update the main dashboard to include an "Initiative" filter and display data aggregated at the initiative level.

---

## 3. Feature: Multi-Period Program Management

**The "Why":** Programs are often multi-year efforts, but the current system tethers a program to a single reporting period. This forces users to create duplicate programs for each new period, making it impossible to track the continuous progress of a single program over time. Decoupling programs from periods is essential for accurate, long-term monitoring.

**The "How":**

-   [ ] **Refactor Logic:** Shift the core concept from "a program belongs to a period" to "a program has submissions for one or more periods."
-   [ ] **Leverage `program_submissions`:** Utilize the existing `program_submissions` table as the central point for storing periodic updates, status, and financial data for each program. The `programs` table will hold the static, core details of the program itself.
-   [ ] **Backend:**
    -   [ ] Refactor data retrieval logic. When a user requests data for a specific period, the system will fetch the core program details from `programs` and join them with the relevant period-specific data from `program_submissions`.
-   **Frontend:**
    -   [ ] Redesign the program management UI. Users should be able to select a program and view its entire history of submissions across all periods, as well as submit a new update for the current period.

---

## 4. Feature: Program-Outcome Linking

**The "Why":** The connection between program execution and its impact on strategic outcomes is currently manual and implicit. To create a truly data-driven system, we must explicitly link programs to the outcomes they are designed to influence. This creates a "golden thread" from action to impact and enables powerful automation, such as updating outcome data automatically when a linked program is completed.

**The "How":**

**Note:** This linking will be optional. The system will accommodate programs that do not have a direct, measurable impact on a specific outcome.

-   **Database:**
    -   [x] Create a new `program_outcome_links` table (`program_id`, `outcome_id`) to model the many-to-many relationship between programs and outcomes.
-   **Backend:**
    -   [x] Create API endpoints to manage these links (create, delete).
    -   [x] Create an API to fetch all available outcomes, so they can be displayed in the frontend for linking.
    -   [x] Develop an automated mechanism (e.g., a function triggered on program status updates) that updates the data in `sector_outcomes_data` when a linked program's status changes (e.g., to 'Completed').
-   **Frontend:**
    -   [ ] In the program creation/editing form, add a multi-select dropdown or checkbox list that allows users to link a program to one or more outcomes (or none).
    -   [ ] On the program details page, clearly display the outcomes it is linked to, or indicate that none are linked.

---

## 5. Feature: Enhanced Outcome Graphing

**The "Why":** Not all metrics are created equal. Some are cumulative (e.g., "Total number of people trained"), while others are non-cumulative or point-in-time (e.g., "Quarterly satisfaction rating"). The current graphing system likely treats all data as non-cumulative, which can be misleading. This enhancement, requested by SFC, will ensure our visualizations are accurate and reflect the true nature of the data.

**The "How":**

-   **Database**:
    -   [x] Add an `is_cumulative` boolean/tinyint column to the `outcomes_details` table. This flag will tell the system how to handle the data for each outcome.
-   **Backend**:
    -   [x] Update the outcome creation/editing API to set the `is_cumulative` flag.
    -   [x] Modify the data retrieval logic for graphs. Before generating a graph, the system will check the `is_cumulative` flag:
        -   If `true`, it will aggregate data by summing values from all previous periods up to the selected period.
        -   If `false`, it will fetch data only for the single, selected period (the current behavior).
-   **Frontend**:
    -   [ ] Add a simple checkbox or toggle in the outcome creation/editing UI labeled "This is a cumulative metric."
    -   [ ] Ensure graphing components render the data correctly, as the backend will be providing the appropriate cumulative or non-cumulative dataset.

---

## 6. Testing & Documentation

-   **Testing:**
    -   [ ] Write unit tests for all new backend logic (API endpoints, data aggregation).
    -   [ ] Perform comprehensive end-to-end testing of the full user flow, from initiative creation to viewing a cumulative graph.
-   **Documentation:**
    -   [ ] Update all relevant user and administrator documentation to reflect these significant new features and workflows.

---

## 7. Impact on Report Generation

This is an excellent question. The ultimate goal of these changes is to make the generated reports **more structured, accurate, and strategically insightful**. While the core function of generating reports remains, its output will be significantly enhanced. Here’s how each feature directly impacts the final reports:

### 1. **Strategic Hierarchy (from Initiatives)**
-   **Current Report:** A flat list of programs, likely grouped by agency or pillar.
-   **New Report:** Programs will be nested under their parent **Initiatives**. This provides a clear, hierarchical structure that mirrors the PCDS 2030 strategic plan. A reader can immediately see which programs are contributing to which broader strategic goals.

### 2. **Point-in-Time Accuracy (from Multi-Period Management)**
-   **Current Report:** The report might show the latest status of a program, even if that status was updated in a later period.
-   **New Report:** When you generate a report for a specific period (e.g., Q2 2025), the data for each program (status, budget spent, remarks) will be the exact data that was submitted **for that period**. This ensures historical accuracy and provides a true snapshot in time.

### 3. **The "Golden Thread" (from Program-Outcome Linking)**
-   **Current Report:** The link between a program and its intended outcome is implicit, relying on the reader's knowledge.
-   **New Report:** Each program listed in the report will explicitly state which strategic outcome(s) it supports. This creates a clear, traceable line—the "golden thread"—from operational activities to strategic impact, making the report far more powerful for accountability and analysis.

### 4. **More Meaningful Analytics (from Enhanced Graphing)**
-   **Current Report:** Graphs might treat all data the same, potentially showing a cumulative metric (like "total people trained") as a point-in-time value, which can be misleading.
-   **New Report:** The graphs within the reports will be smarter. **Cumulative metrics** will be displayed as running totals (e.g., a line graph that always goes up or stays flat), while **non-cumulative metrics** will be shown as distinct values for the period (e.g., bar charts). This ensures the visual data is analytically sound and easy to interpret correctly.

**In summary, the reports will evolve from simple operational lists into rich, multi-layered strategic documents that are more valuable for decision-makers.** The focus on the report is not lost; it is sharpened.

---

## Backend Implementation Summary (Completed)

**Core Initiative Features:**
- [x] Updated `get_period_programs.php` to include initiative information and group programs by initiatives
- [x] Enhanced `report_data.php` to include initiative metadata in program data
- [x] Modified `DashboardController.php` to support initiative filtering across all dashboard components
- [x] Updated `dashboard_data.php` AJAX endpoint to accept initiative filter parameter

**Program-Outcome Linking:**
- [x] Created `outcome_automation.php` library with automated outcome data updates
- [x] Built `enhanced_outcome_data.php` API for cumulative/non-cumulative outcome data retrieval
- [x] Created `update_outcome.php` API for managing outcome is_cumulative flags
- [x] Integrated outcome automation into program submission process

**Enhanced Outcome Graphing:**
- [x] Implemented cumulative data calculation logic in `getOutcomeDataWithCumulative()`
- [x] Added support for both cumulative and non-cumulative data retrieval
- [x] Created APIs that respect the `is_cumulative` flag for proper data aggregation

**Key Functions Created:**
- `updateOutcomeDataOnProgramStatusChange()` - Automatically updates outcome data when programs are completed
- `getOutcomeDataWithCumulative()` - Retrieves outcome data with proper cumulative calculations
- `getLinkedPrograms()` - Gets programs linked to specific outcomes
- Enhanced dashboard filtering with initiative support

**Data Structure Changes:**
- Programs now grouped by initiatives in API responses (with fallback for programs without initiatives)
- Outcome data includes program completion tracking and statistics
- Dashboard endpoints support optional initiative filtering
- Report data includes initiative metadata for hierarchical reporting
