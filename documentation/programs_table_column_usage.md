# How `programs` and `program_submissions` Table Columns Are Used

## Introduction
This guide explains, in simple terms, how each column in the `programs` and `program_submissions` tables is used in the PCDS2030 Dashboard. If you know basic PHP and want to understand how the database connects to the code, this is for you!

---

## Table: `programs`
This table stores information about every program in the system. Each row is a program.

| Column             | What It Means & How It's Used                                                                 |
|--------------------|----------------------------------------------------------------------------------------------|
| **program_id**     | The unique number for each program. Used everywhere to find, update, or delete a program.    |
| **program_name**   | The name of the program. Shown in lists, forms, and details. You can search or edit this.    |
| **description**    | A short explanation about the program. Shown and editable in forms and details.              |
| **owner_agency_id**| The agency (organization) that owns this program. Used to show/filter programs by agency.    |
| **sector_id**      | Which sector (like Forestry, etc.) the program belongs to. Used for filtering and stats.     |
| **start_date**     | When the program starts. Shown in details and used for timelines.                            |
| **end_date**       | When the program ends. Shown in details and used for timelines.                              |
| **created_at**     | When the program was first made. Used for sorting and history.                              |
| **updated_at**     | When the program was last changed. Used for sorting and history.                            |
| **is_assigned**    | Shows if the program was assigned by an admin (1) or created by an agency (0).              |
| **edit_permissions**| Stores what fields the agency can edit (in JSON format). Used to control edit access.       |
| **created_by**     | Who created the program (admin or agency user). Used for tracking.                          |

---

## Table: `program_submissions`
This table stores every submission (report/update) made for a program. Each row is a submission for a specific period.

| Column           | What It Means & How It's Used                                                                 |
|------------------|----------------------------------------------------------------------------------------------|
| **submission_id**| The unique number for each submission. Used to find or update a submission.                   |
| **program_id**   | Which program this submission is for. Links to the `programs` table.                          |
| **period_id**    | Which reporting period (like Q1 2025) this submission is for.                                |
| **submitted_by** | The user who made the submission (usually an agency user).                                   |
| **status**       | The current status (like 'on-track', 'not-started'). Used for progress tracking and filters.  |
| **content_json** | Stores extra details about the submission (targets, remarks, etc.) in JSON format.           |
| **submission_date**| When the submission was made. Used for sorting and history.                                |
| **updated_at**   | When the submission was last changed. Used for sorting and history.                          |
| **is_draft**     | Shows if the submission is a draft (not final) or finalized. Controls if it can be edited.    |

---

## Tips
- If you see a column name in the code (like `$program['program_name']`), it means the code is using that column.
- Columns marked as "Not used in the code" are safe to ignore for now.
- JSON columns (like `edit_permissions` and `content_json`) store more complex data. You can use `json_decode()` in PHP to read them.
- If you want to add new features, try to use the existing columns before adding new ones.

---

_Last updated: 2025-05-30_
