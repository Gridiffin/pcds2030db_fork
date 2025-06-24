# Implementation Plan: Single Agency Outcomes with Hidden Sectors

## ✅ Decisions Made

1. **Sectors**: Hide from UI, keep in backend
2. **Ownership**: Creator-based (use `submitted_by`)
3. **Visibility**: Edit only owned outcomes, but view all in "All Outcomes" page
4. **Existing Data**: Manual mapping via editable column
5. **Focus**: One agency per outcome

## 🔍 Implementation Details

### 1. Hide Sectors ✅ Clear
- Remove sector selection from all agency UI forms
- Keep `sector_id` in database for future flexibility
- Set default sector value when creating outcomes

### 2. Creator-Based Ownership ✅ Clear
- Use existing `submitted_by` field as ownership indicator
- Agency can edit outcomes where `submitted_by = $_SESSION['user_id']`
- No admin assignment features needed

### 3. Hybrid Visibility ✅ Clear
**Agency Interface Structure:**
```
/app/views/agency/outcomes/
├── my_outcomes.php          # Edit only owned outcomes
└── all_outcomes.php         # View all outcomes (read-only except owned)
```

### 4. Manual Mapping Column - **NEEDS DECISION**

For manually assigning existing outcomes to agencies, we have these options:

#### Option A: Use existing `submitted_by` field
**Pros:** 
- No schema change needed
- Already links to users table
- Direct ownership mapping

**Cons:** 
- Overwrites original creator info
- Might confuse audit trails

#### Option B: Add new `owner_user_id` field
```sql
ALTER TABLE sector_outcomes_data 
ADD COLUMN owner_user_id INT NULL,
ADD FOREIGN KEY (owner_user_id) REFERENCES users(user_id);
```
**Pros:**
- Preserves original creator info
- Clear ownership semantics
- Clean separation of concerns

**Cons:**
- Requires schema change
- Additional field to maintain

#### Option C: Use existing `submitted_by` + backup original to history
**Pros:**
- No schema change
- Preserves history
- Clean current state

**Cons:**
- More complex migration
- History dependency

**My Recommendation**: Option B (add `owner_user_id`) - cleanest approach

### 5. One Agency Per Outcome ✅ Clear
- Enforce in business logic
- UI prevents multiple ownership
- Audit logs track ownership changes

## 🤔 Outcome Details Integration - NEEDS CLARIFICATION

You mentioned wanting to combine `outcome_details` into `outcomes`. Let me understand the current structure:

### Current `outcome_details` table:
```sql
CREATE TABLE outcomes_details (
    detail_id INT PRIMARY KEY,
    detail_name VARCHAR(255),
    detail_json LONGTEXT,       -- Template/structure data
    is_draft INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Current `sector_outcomes_data` table:
```sql
CREATE TABLE sector_outcomes_data (
    id INT PRIMARY KEY,
    metric_id INT,
    table_name VARCHAR(255),
    data_json LONGTEXT,         -- Actual monthly data
    is_draft TINYINT,
    ...
);
```

### **Questions about combining them:**

1. **What is `outcome_details` currently used for?**
   - Templates for creating outcomes?
   - Different types of outcome structures?
   - Reusable outcome definitions?

2. **How do you want them combined?**
   - Merge both tables into one?
   - Use `outcome_details` as templates and eliminate separate storage?
   - Add template functionality to main outcomes table?

3. **What happens to existing data in both tables?**

4. **Sample scenario**: Can you give me an example of:
   - An `outcome_details` record and what it contains
   - A `sector_outcomes_data` record and how they might relate
   - How you envision them working together

## 📋 Implementation Plan (Pending Clarifications)

### Phase 1: Database Changes
- [ ] **DECISION NEEDED**: Which column to use for manual mapping?
- [ ] **DECISION NEEDED**: How to combine outcome_details?
- [ ] Add ownership column (if Option B chosen)
- [ ] Create migration script for existing data

### Phase 2: Backend Logic Changes
- [ ] Update `get_agency_sector_outcomes()` to use ownership instead of sector
- [ ] Create `get_my_outcomes()` function
- [ ] Create `get_all_outcomes()` function  
- [ ] Update permission checks in all outcome files
- [ ] Remove sector-based filtering

### Phase 3: UI Changes
- [ ] Remove sector selection from create/edit forms
- [ ] Create "My Outcomes" page (editable)
- [ ] Create "All Outcomes" page (view-only except owned)
- [ ] Update navigation
- [ ] Add ownership indicators in UI

### Phase 4: Admin Tools
- [ ] Create manual mapping interface for existing outcomes
- [ ] Add ownership transfer functionality (if needed)

## 🎯 Next Steps

**I need clarification on:**

1. **Mapping Column**: Which option (A, B, or C) for the manual mapping column?

2. **Outcome Details**: 
   - What does `outcome_details` currently contain?
   - How should it be combined with `sector_outcomes_data`?
   - Can you show me some sample records?

Once these are clarified, I can create the detailed implementation steps and start coding the changes.
