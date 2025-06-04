<?php
require_once dirname(__DIR__) . '/lib/db_connect.php';

function run_migrations() {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();

        // Add submitted_by column to sector_outcomes_data if it doesn't exist
        $check_column = "SELECT COLUMN_NAME 
                        FROM INFORMATION_SCHEMA.COLUMNS 
                        WHERE TABLE_NAME = 'sector_outcomes_data' 
                        AND COLUMN_NAME = 'submitted_by'";
        
        $result = $conn->query($check_column);
        if ($result && $result->num_rows === 0) {
            $alter_table_query = "ALTER TABLE sector_outcomes_data
                                ADD COLUMN submitted_by INT NULL,
                                ADD CONSTRAINT fk_submitted_by 
                                FOREIGN KEY (submitted_by) REFERENCES users(user_id)";
            
            if (!$conn->query($alter_table_query)) {
                throw new Exception("Error adding submitted_by column: " . $conn->error);
            }
            echo "Added submitted_by column to sector_outcomes_data\n";
        }

        // Create outcome_history table if it doesn't exist, without foreign key on metric_id
        $create_table_query = "CREATE TABLE IF NOT EXISTS outcome_history (
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
            CONSTRAINT fk_outcome_history_record FOREIGN KEY (outcome_record_id) REFERENCES sector_outcomes_data(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$conn->query($create_table_query)) {
            throw new Exception("Error creating outcome_history table: " . $conn->error);
        }
        echo "Created outcome_history table\n";

        // Commit transaction
        $conn->commit();
        echo "Migrations completed successfully!\n";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error running migrations: " . $e->getMessage() . "\n";
    }
}

// Run the migrations
run_migrations();
