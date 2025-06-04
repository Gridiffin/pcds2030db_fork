-- Add submitted_by column to sector_outcomes_data
ALTER TABLE sector_outcomes_data
ADD COLUMN submitted_by INT NULL,
ADD CONSTRAINT fk_submitted_by 
FOREIGN KEY (submitted_by) REFERENCES users(user_id);

-- Create outcome_history table
CREATE TABLE IF NOT EXISTS outcome_history (
  history_id INT NOT NULL AUTO_INCREMENT,
  outcome_record_id INT NOT NULL,
  metric_id INT NOT NULL,
  data_json LONGTEXT NOT NULL,
  action_type VARCHAR(50) NOT NULL,
  status VARCHAR(50) NOT NULL,
  changed_by INT NOT NULL,
  change_description TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (history_id),
  CONSTRAINT fk_outcome_history_user FOREIGN KEY (changed_by) REFERENCES users(user_id),
  CONSTRAINT fk_outcome_history_metric FOREIGN KEY (metric_id) REFERENCES sector_outcomes_data(metric_id),
  CONSTRAINT fk_outcome_history_record FOREIGN KEY (outcome_record_id) REFERENCES sector_outcomes_data(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
