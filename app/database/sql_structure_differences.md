# SQL Structure Differences: `newpcds2030db.sql` vs `oldpcds2030db.sql`

---

## 1. Tables Only in `newpcds2030db.sql`
- `agency`
- `targets`

## 2. Tables Only in `oldpcds2030db.sql`
- `agency_group`
- `metrics_details`
- `outcome_history`
- `sectors`
- `sector_outcomes_data`
- `sector_outcomes_data_backup_2025_06_29_15_11_05`
- `sector_outcomes_data_backup_2025_06_30_04_59_21`

## 3. Tables Present in Both Files (Identical Structure)
- `audit_logs`
- `initiatives`
- `notifications`
- `outcomes_details`
- `programs`
- `program_attachments`
- `program_outcome_links`
- `program_submissions`
- `reporting_periods`
- `reports`
- `users`

All columns, types, defaults, and indexes for these tables are identical in both files.

---

## 4. Unique Table Structures

### Tables Only in `newpcds2030db.sql`

#### `agency`
```sql
CREATE TABLE IF NOT EXISTS `agency` (
  `agency_id` int NOT NULL AUTO_INCREMENT,
  `agency_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`agency_id`)
)
```

#### `targets`
```sql
-- (Structure not shown here, add if needed)
```

### Tables Only in `oldpcds2030db.sql`

#### `agency_group`
```sql
CREATE TABLE IF NOT EXISTS `agency_group` (
  `agency_group_id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `sector_id` int NOT NULL,
  PRIMARY KEY (`agency_group_id`),
  KEY `sector_id` (`sector_id`),
  CONSTRAINT `agency_group_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`)
)
```

#### `metrics_details`, `outcome_history`, `sectors`, `sector_outcomes_data`, `sector_outcomes_data_backup_2025_06_29_15_11_05`, `sector_outcomes_data_backup_2025_06_30_04_59_21`
- (Structures not shown here, add if needed)

---

## 5. Notes
- All tables present in both files have identical columns, types, defaults, and indexes.
- Each file has a few unique tables not present in the other.
- For full column details of any table, refer to the respective SQL file. 