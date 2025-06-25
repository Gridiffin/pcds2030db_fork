# Program-Outcome Linking System - How It Works

## Overview
This document explains the complete workflow of how program-outcome links are managed, stored, and updated in the database.

## Database Table Structure

### `program_outcome_links` Table
```sql
CREATE TABLE program_outcome_links (
    link_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT,                    -- References programs.program_id
    outcome_id INT,                    -- References outcomes_details.detail_id
    created_by INT,                    -- User who created the link
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE,
    FOREIGN KEY (outcome_id) REFERENCES outcomes_details(detail_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);
```

## Why Multiple Rows Per Program? - Concrete Example

**Important:** Each row represents **ONE program linked to ONE outcome**, not one "linking action".

### Example Scenario:
Program ID 176 is linked to 3 different outcomes:
- Outcome 19: "Forest area certification"
- Outcome 21: "Certification of FMU & FPMU" 
- Outcome 39: "Obtain world recognition for sustainable management"

### Database Rows:
```
| link_id | program_id | outcome_id | created_by | created_at          |
|---------|------------|------------|------------|---------------------|
| 45      | 176        | 19         | 1          | 2025-06-25 10:00:00 |
| 46      | 176        | 21         | 1          | 2025-06-25 10:00:00 |
| 47      | 176        | 39         | 1          | 2025-06-25 10:00:00 |
```

### Why 3 Rows?
- **Row 45**: Program 176 ↔ Outcome 19 (one relationship)
- **Row 46**: Program 176 ↔ Outcome 21 (another relationship)  
- **Row 47**: Program 176 ↔ Outcome 39 (third relationship)

**Each row = One specific program-outcome connection**

### What Happens During Edit:
If admin changes Program 176 to only link to outcomes 19 and 39 (removing 21):

1. **Delete ALL existing rows** for program 176 (rows 45, 46, 47)
2. **Insert NEW rows** for the selected outcomes:
```
| link_id | program_id | outcome_id | created_by | created_at          |
|---------|------------|------------|------------|---------------------|
| 48      | 176        | 19         | 1          | 2025-06-25 14:30:00 |
| 49      | 176        | 39         | 1          | 2025-06-25 14:30:00 |
```

**Result:** 2 rows (one for each outcome link), not 1 row representing "the linking action"

## How Links Are Updated

### Current Implementation: "Replace All" Strategy

When a program's outcome links are changed, the system uses a **"Replace All"** approach:

1. **Delete ALL existing links** for the program
2. **Insert ALL new links** from the form

```php
// Step 1: Delete existing links
$delete_links_query = $conn->prepare("DELETE FROM program_outcome_links WHERE program_id = ?");
$delete_links_query->bind_param("i", $program_id);
$delete_links_query->execute();

// Step 2: Insert new links
foreach ($new_outcome_ids as $outcome_id) {
    $insert_link_query = $conn->prepare("INSERT INTO program_outcome_links (program_id, outcome_id, created_by, created_at) VALUES (?, ?, ?, NOW())");
    $insert_link_query->bind_param("iii", $program_id, $outcome_id, $current_user_id);
    $insert_link_query->execute();
}
```

### What This Means:
- ✅ **Simple and reliable** - No complex update logic needed
- ✅ **Consistent state** - Always reflects exactly what's in the form
- ❌ **Loses creation timestamps** - All links get new timestamps
- ❌ **Loses original creator info** - All links show current editor as creator

## Complete Workflow Example

### Scenario: Changing Program Links

**Initial State:**
```
Program ID: 176
Linked to outcomes: [19, 21]
```

**User Action:** Admin edits program and changes outcomes to [19, 39]

**Database Operations:**
```sql
-- 1. Current state capture (for change tracking)
SELECT outcome_id FROM program_outcome_links WHERE program_id = 176;
-- Result: [19, 21]

-- 2. Form submission processing
-- new_outcome_ids = [19, 39]

-- 3. Delete all existing links
DELETE FROM program_outcome_links WHERE program_id = 176;
-- Removes links to outcomes 19 and 21

-- 4. Insert new links
INSERT INTO program_outcome_links (program_id, outcome_id, created_by, created_at) 
VALUES (176, 19, 1, '2025-06-25 14:30:00');

INSERT INTO program_outcome_links (program_id, outcome_id, created_by, created_at) 
VALUES (176, 39, 1, '2025-06-25 14:30:00');
```

**Final State:**
```
Program ID: 176
Linked to outcomes: [19, 39]
```

**Edit History Records:**
- "Linked Outcomes: Removed: 'Certification of FMU & FPMU'" (outcome 21)
- "Linked Outcomes: Added: 'Obtain world recognition for sustainable management'" (outcome 39)

## Change Tracking Process

### 1. Before State Capture
```php
// In get_current_program_state()
$outcome_links_stmt = $conn->prepare("SELECT outcome_id FROM program_outcome_links WHERE program_id = ? ORDER BY outcome_id");
// Result stored in before_state['linked_outcomes']
```

### 2. After State Building
```php
// In edit_program.php
$new_outcome_ids = isset($_POST['outcome_id']) ? array_filter($_POST['outcome_id']) : [];
$after_state['linked_outcomes'] = $new_outcome_ids;
```

### 3. Change Detection
```php
// In generate_field_changes()
$before_outcomes = $before_state['linked_outcomes']; // [19, 21]
$after_outcomes = $after_state['linked_outcomes'];   // [19, 39]

$added_outcomes = array_diff($after_outcomes, $before_outcomes);   // [39]
$removed_outcomes = array_diff($before_outcomes, $after_outcomes); // [21]
```

### 4. History Storage
Changes are stored in `program_submissions.content_json`:
```json
{
  "changes_made": [
    {
      "field": "outcome_links",
      "field_label": "Linked Outcomes",
      "before": "Certification of FMU & FPMU",
      "after": null,
      "change_type": "removed"
    },
    {
      "field": "outcome_links", 
      "field_label": "Linked Outcomes",
      "before": null,
      "after": "Obtain world recognition for sustainable management",
      "change_type": "added"
    }
  ]
}
```

## Data Integrity & Automation

### Where is the `completed_programs` Array Stored?

**Answer: In the Database** - Specifically in the `sector_outcomes_data.data_json` column

The `completed_programs` array is **permanently stored in the database**, not just in backend memory. Here's exactly how:

#### Database Storage Details:
```sql
-- Table: sector_outcomes_data
-- Column: data_json (TEXT/JSON type)
-- Sample content:
{
  "total_value": 3,
  "completed_programs": [
    {
      "program_id": 176,
      "program_name": "Forest Conservation Initiative",
      "completion_date": "2025-06-25",
      "period_id": 12
    },
    {
      "program_id": 189,
      "program_name": "Sustainable Timber Program", 
      "completion_date": "2025-06-20",
      "period_id": 12
    }
  ],
  "auto_generated": true,
  "source": "program_completion"
}
```

#### How Programs Get Added to the Array:
1. **Program Status Change**: When any linked program changes to "completed" or "target-achieved"
2. **Database Query**: System finds the outcome record in `sector_outcomes_data`
3. **JSON Update**: Adds program details to the `completed_programs` array
4. **Database Save**: Updates the `data_json` column with the modified JSON

#### What Gets Stored Per Completed Program:
- `program_id`: The program's database ID
- `program_name`: Program title for easy reference
- `completion_date`: When the program was marked as completed
- `period_id`: Which reporting period this completion occurred in

#### Persistence & Retrieval:
- ✅ **Persistent**: Data survives server restarts, database backups, etc.
- ✅ **Queryable**: Can search for outcomes by completed programs
- ✅ **Auditable**: Full history of which programs contributed to outcomes
- ✅ **Reportable**: Can generate reports showing program impact on outcomes

### What Happens After Links Change:

1. **Immediate**: Links are updated in database
2. **Edit History**: Changes are recorded with timestamps
3. **Future Automation**: When program status changes to "completed":
   ```php
   // In outcome_automation.php
   $links_query = "SELECT pol.outcome_id FROM program_outcome_links pol WHERE pol.program_id = ?";
   // System finds all linked outcomes and updates their values automatically
   ```

### Cascade Behavior:
- **Program deleted** → All its outcome links are deleted (CASCADE)
- **Outcome deleted** → All links to it are deleted (CASCADE)
- **User deleted** → Links remain but created_by becomes NULL

## Alternative Approaches (Not Implemented)

### 1. Incremental Update Strategy
```php
// Find specific additions/removals and update accordingly
$to_add = array_diff($new_outcomes, $current_outcomes);
$to_remove = array_diff($current_outcomes, $new_outcomes);

// Delete only removed links
foreach ($to_remove as $outcome_id) { /* DELETE specific links */ }

// Insert only new links  
foreach ($to_add as $outcome_id) { /* INSERT specific links */ }
```

**Pros:** Preserves original timestamps and creators
**Cons:** More complex, potential for inconsistencies

### 2. Versioned Links Strategy
```sql
-- Add version/status columns
ALTER TABLE program_outcome_links ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE program_outcome_links ADD COLUMN deactivated_at TIMESTAMP NULL;
```

**Pros:** Full audit trail of all changes
**Cons:** Much more complex queries and storage overhead

## Current Benefits & Trade-offs

### ✅ Benefits:
- **Simple & Reliable**: Easy to understand and maintain
- **Consistent State**: Database always matches form exactly
- **Good Performance**: Minimal database operations
- **Complete Tracking**: Edit history captures all changes

### ⚠️ Trade-offs:
- **Lost Metadata**: Original creation time/user lost on edits
- **Bulk Operations**: All links recreated even for small changes

## Recommendations

The current "Replace All" approach is **appropriate for this use case** because:

1. **Outcome links are not frequently changed** - Usually set once during program creation
2. **Simplicity is valuable** - Reduces bugs and maintenance overhead  
3. **Edit history compensates** - We still track what changed and when
4. **Automation works well** - The system finds current links regardless of creation method

If **more detailed audit trails** become critical, consider migrating to the versioned approach in the future.

## Frequently Asked Questions

### Q: Why are there multiple rows for the same program in `program_outcome_links`?
**A:** Each row represents **one specific program-outcome relationship**, not a "linking action". If a program is linked to 5 outcomes, there will be 5 rows - one for each unique program-outcome pair.

### Q: Does each row represent a historical linking action?
**A:** No. Each row represents a **current active link**. When links are updated, all old rows are deleted and new rows are inserted. The `created_at` timestamp shows when the current set of links was established, not individual historical actions.

### Q: How is change history tracked if rows are deleted/replaced?
**A:** Change history is stored in the `programs_edit_history` table, which captures what outcomes were added or removed during each edit session. The raw linking data in `program_outcome_links` only shows current active relationships.

### Q: Can I see historical linking data?
**A:** Yes, but not in `program_outcome_links`. Check the `programs_edit_history` table for entries like:
- "Outcome links changed: Added Forest area certification, Removed Certification of FMU & FPMU"

### Q: What happens if I link a program to 10 outcomes?
**A:** You'll get 10 rows in `program_outcome_links` - one row per outcome. Each row contains the same `program_id` but a different `outcome_id`.

### Q: Is this database design efficient?
**A:** Yes, this is a standard "many-to-many" relationship design. It allows:
- Easy querying of all outcomes for a program
- Easy querying of all programs for an outcome  
- Proper foreign key constraints
- Clean deletion when programs/outcomes are removed

### Current Automation Limitations

#### 1. **Binary Impact System**
```php
// Current: Every completed program = +1 to total_value
if ($is_cumulative && isset($data_json['total_value'])) {
    $data_json['total_value'] = ($data_json['total_value'] ?? 0) + 1;  // Always +1
}
```
- **Problem**: A small pilot program gets the same +1 as a major national initiative
- **No Consideration**: Program budget, scope, beneficiaries, or actual impact

#### 2. **Equal Weight Treatment**
```php
// All linked programs are treated identically
$links_query = "SELECT pol.outcome_id FROM program_outcome_links pol WHERE pol.program_id = ?";
// No weight or priority field - every link is equal
```
- **Problem**: No differentiation between programs' relative importance
- **Missing**: Contribution weights, impact factors, or priority levels

#### 3. **Simplistic Value Calculation**
```php
// For cumulative outcomes: just count completed programs
'total_value' => $is_cumulative ? 1 : 0,

// For non-cumulative outcomes: no automatic value change at all
// Manual data entry still required
```
- **Problem**: No sophisticated calculation based on program characteristics
- **Missing**: Baseline values, target calculations, formula-based updates

#### 4. **No Historical Impact Tracking**
- **Problem**: Can't track how much each individual program contributed over time
- **Missing**: Program-specific impact values, milestone-based contributions

#### 5. **Database Design Limitations**
Current `program_outcome_links` table:
```sql
CREATE TABLE program_outcome_links (
    link_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT,
    outcome_id INT,
    created_by INT,
    created_at TIMESTAMP
    -- MISSING: contribution_weight, impact_value, baseline_contribution
);
```

### What This Means in Practice:

**Current Reality:**
- Program A (Budget: $10,000, Scope: Small village) → Completes → Outcome +1
- Program B (Budget: $1,000,000, Scope: National) → Completes → Outcome +1

**Better Approach Would Be:**
- Program A → Completes → Outcome +0.2 (small impact)
- Program B → Completes → Outcome +5.0 (major impact)
