# Outcomes vs Outcome Details: Program-Linked Data Structure

## 📊 Understanding the Distinction (Based on Image)

### **Outcomes (Graph-based, Time-series)**
- **Timber Export Value (RM)**: Monthly line graph data
- **Total Degraded Area (Ha)**: Multi-year line graph data
- **Characteristics**: Time-based data that changes monthly/yearly and gets visualized as graphs

### **Outcome Details (Static Numbers, Non-graph)**
- **IPA Protection & Biodiversity Conservation Programs**: 32 (count)
- **Obtain world recognition for sustainable management**: 50%, 100% (percentages)  
- **Certification of FMU & FPMU**: 56.7% (percentage)
- **Characteristics**: Static indicators that represent current status/achievements

## 🔗 The Current Issues & Clarifications

### **Issues Identified:**
- **FMU & FPMU Certification (56.7%)**: Should be table-based outcome like graphs, not a static number
- **Program Linking**: Not all outcomes are program-linked, and relationships are unclear
- **Mixed Data Types**: Some data should be graphable tables, others should stay as static indicators

### **User Clarifications:**
- ❌ Don't know which programs affect which outcomes
- ❌ Not every outcome is affected by programs  
- ✅ **FMU & FPMU should be table-based** (like Timber Export Value and Total Degraded Area)
- ✅ Want to create/edit FMU & FPMU tables just like other graph outcomes

### **Immediate Priority:**
Convert "Certification of FMU & FPMU: 56.7%" from static outcome detail to table-based outcome that can generate graphs and reports.

## 💡 Revised Solution Architecture

### **Immediate Focus: Convert FMU/FPMU to Table-Based Outcome**

#### Current State:
- **Timber Export Value**: ✅ Table-based outcome (has graph)
- **Total Degraded Area**: ✅ Table-based outcome (has graph)  
- **FMU & FPMU Certification**: ❌ Static outcome detail (56.7%)

#### Target State:
- **Timber Export Value**: ✅ Table-based outcome (has graph)
- **Total Degraded Area**: ✅ Table-based outcome (has graph)
- **FMU & FPMU Certification**: ✅ Table-based outcome (will have graph)

#### Keep as Static Outcome Details:
- **IPA Protection Programs**: 32 (manual entry)
- **World Recognition**: 50%, 100% (manual entry)

### **Database Structure - Simplified Approach**

#### Enhanced `sector_outcomes_data` (for ALL table-based data)
```sql
-- Modified to handle both graph outcomes and table-based outcome details
CREATE TABLE sector_outcomes_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_id INT NOT NULL,
    table_name VARCHAR(255) NOT NULL,
    data_json LONGTEXT NOT NULL,        -- Monthly/yearly data OR calculated percentages
    outcome_type ENUM('timeseries', 'percentage', 'count') DEFAULT 'timeseries',
    display_as ENUM('graph', 'table', 'indicator') DEFAULT 'graph',
    is_draft TINYINT NOT NULL DEFAULT 1,
    owner_user_id INT NULL,             -- New ownership field
    sector_id INT NOT NULL,             -- Keep for backend, hide in UI
    period_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_user_id) REFERENCES users(user_id)
);
```

#### Simple `outcome_details` (for static indicators only)
```sql
CREATE TABLE outcome_details (
    detail_id INT PRIMARY KEY AUTO_INCREMENT,
    detail_name VARCHAR(255) NOT NULL,
    current_value DECIMAL(10,2) NOT NULL,
    target_value DECIMAL(10,2) NULL,
    unit VARCHAR(50) NULL,              -- programs, %, etc.
    description TEXT NULL,
    owner_user_id INT NULL,
    is_draft TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_user_id) REFERENCES users(user_id)
);
```

### **Data Examples**

#### FMU/FPMU as Table-Based Outcome
```json
{
    "table_name": "FMU & FPMU Certification Progress",
    "outcome_type": "percentage",
    "display_as": "indicator",
    "data_json": {
        "columns": ["Total FMU", "Certified FMU", "Total FPMU", "Certified FPMU"],
        "data": {
            "January": {"Total FMU": 100, "Certified FMU": 45, "Total FPMU": 50, "Certified FPMU": 35},
            "February": {"Total FMU": 100, "Certified FMU": 50, "Total FPMU": 50, "Certified FPMU": 38},
            // ... monthly tracking
        },
        "calculation": {
            "formula": "(Certified FMU + Certified FPMU) / (Total FMU + Total FPMU) * 100",
            "current_percentage": 56.7
        }
    }
}
```

#### Static Outcome Details (Keep Simple)
```json
{
    "detail_name": "IPA Protection & Biodiversity Conservation Programs",
    "current_value": 32,
    "target_value": 50,
    "unit": "programs"
}
```

## 🎯 Revised Implementation Plan

### **Phase 1: Database Structure Updates** ⏳
- [ ] Add `owner_user_id` to existing `sector_outcomes_data` table
- [ ] Add `outcome_type` and `display_as` fields to `sector_outcomes_data`
- [ ] Create simplified `outcome_details` table for static indicators
- [ ] Migrate FMU/FPMU from outcome_details to sector_outcomes_data
- [ ] Set up ownership for existing outcomes

### **Phase 2: FMU/FPMU Table Creation** ⏳
- [ ] Create table management interface for FMU/FPMU certification
- [ ] Allow monthly data entry for FMU/FPMU progress
- [ ] Implement percentage calculation logic
- [ ] Add FMU/FPMU to outcomes list alongside other table-based outcomes

### **Phase 3: Backend Updates** ⏳
- [ ] Update outcome functions to handle ownership (`owner_user_id`)
- [ ] Modify queries to filter by ownership instead of sector
- [ ] Create separate functions for table-based vs static outcomes
- [ ] Update access controls for edit permissions

### **Phase 4: UI Restructuring** ⏳
- [ ] Hide sector selection from all forms
- [ ] Update "My Outcomes" page to show owned table-based outcomes
- [ ] Update "All Outcomes" page to show all table-based outcomes with edit restrictions
- [ ] Create separate management for static outcome details
- [ ] Add FMU/FPMU to table creation options

### **Phase 5: Clean Up** ⏳
- [ ] Remove old outcome_details references where migrated to outcomes
- [ ] Update navigation and dashboards
- [ ] Test ownership and permission controls
- [ ] Update report generation to include FMU/FPMU tables

## 🚀 Immediate Next Steps

**Priority 1**: Convert FMU/FPMU to table-based outcome
**Priority 2**: Add ownership controls to existing outcomes
**Priority 3**: Create "My Outcomes" vs "All Outcomes" interface

## 📝 Migration Strategy

### **FMU/FPMU Conversion**
1. **Current**: Static "56.7%" in outcome_details
2. **Target**: Table with monthly FMU/FPMU certification progress data
3. **Benefits**: Can track progress over time, generate graphs, include in reports

### **Ownership Assignment**
- Use existing `submitted_by` field to determine current ownership
- Add new `owner_user_id` for admin reassignment capability
- Agencies edit only their owned outcomes

### **Program Linking (Future)**
- Keep simple outcome_details for manual static indicators
- Program linking can be added later when relationships are clearer
- Focus on immediate user needs first
