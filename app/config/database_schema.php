<?php
// Centralized database schema definition for the project
// Each table is defined as an associative array with columns and their properties

return [
    'agency' => [
        'columns' => [
            'agency_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'agency_name' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'],
        ],
    ],
    'audit_logs' => [
        'columns' => [
            'id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'user_id' => ['type' => 'int', 'nullable' => false],
            'action' => ['type' => 'varchar', 'length' => 128, 'nullable' => false],
            'details' => ['type' => 'text', 'nullable' => true],
            'ip_address' => ['type' => 'varchar', 'length' => 45, 'nullable' => true],
            'status' => ['type' => 'varchar', 'length' => 16, 'nullable' => false],
            'created_at' => ['type' => 'datetime', 'nullable' => true, 'default' => 'CURRENT_TIMESTAMP'],
        ],
    ],
    'initiatives' => [
        'columns' => [
            'initiative_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'initiative_name' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'initiative_number' => ['type' => 'varchar', 'length' => 20, 'nullable' => true],
            'initiative_description' => ['type' => 'text', 'nullable' => true],
            'start_date' => ['type' => 'date', 'nullable' => true],
            'end_date' => ['type' => 'date', 'nullable' => true],
            'is_active' => ['type' => 'tinyint', 'length' => 1, 'nullable' => false, 'default' => 1],
            'created_by' => ['type' => 'int', 'nullable' => false],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'],
        ],
    ],
    'notifications' => [
        'columns' => [
            'notification_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'user_id' => ['type' => 'int', 'nullable' => false],
            'message' => ['type' => 'text', 'nullable' => false],
            'type' => ['type' => 'varchar', 'length' => 50, 'nullable' => false, 'default' => 'update'],
            'read_status' => ['type' => 'tinyint', 'length' => 1, 'nullable' => false, 'default' => 0],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'action_url' => ['type' => 'varchar', 'length' => 255, 'nullable' => true],
        ],
    ],
    'outcomes_details' => [
        'columns' => [
            'detail_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'detail_name' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'display_config' => ['type' => 'json', 'nullable' => true],
            'detail_json' => ['type' => 'longtext', 'nullable' => false],
            'is_cumulative' => ['type' => 'tinyint', 'length' => 1, 'nullable' => false, 'default' => 0],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'],
            'is_important' => ['type' => 'tinyint', 'length' => 1, 'nullable' => true, 'default' => 0],
            'indicator_type' => ['type' => 'varchar', 'length' => 100, 'nullable' => true],
            'agency_id' => ['type' => 'int', 'nullable' => true],
        ],
    ],
    'programs' => [
        'columns' => [
            'program_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'program_name' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'program_number' => ['type' => 'varchar', 'length' => 20, 'nullable' => true],
            'initiative_id' => ['type' => 'int', 'nullable' => true],
            'agency_id' => ['type' => 'int', 'nullable' => false],
            'users_assigned' => ['type' => 'int', 'nullable' => true],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'],
            'created_by' => ['type' => 'int', 'nullable' => false, 'default' => 1],
            'attachment_count' => ['type' => 'int', 'nullable' => true, 'default' => 0],
            'status' => ['type' => 'set', 'values' => ['not-started','in-progress','completed'], 'nullable' => true],
            'hold_point' => ['type' => 'json', 'nullable' => true],
            'targets_linked' => ['type' => 'int', 'nullable' => true, 'default' => 0],
        ],
    ],
    'program_attachments' => [
        'columns' => [
            'attachment_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'program_id' => ['type' => 'int', 'nullable' => false],
            'file_name' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'file_path' => ['type' => 'varchar', 'length' => 500, 'nullable' => false],
            'file_size' => ['type' => 'bigint', 'nullable' => false],
            'file_type' => ['type' => 'varchar', 'length' => 100, 'nullable' => false],
            'uploaded_by' => ['type' => 'int', 'nullable' => false],
            'uploaded_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'is_active' => ['type' => 'tinyint', 'length' => 1, 'nullable' => true, 'default' => 1],
        ],
    ],
    'program_outcome_links' => [
        'columns' => [
            'link_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'program_id' => ['type' => 'int', 'nullable' => false],
            'outcome_id' => ['type' => 'int', 'nullable' => false],
            'created_by' => ['type' => 'int', 'nullable' => false],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ],
    ],
    'program_submissions' => [
        'columns' => [
            'submission_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'program_id' => ['type' => 'int', 'nullable' => false],
            'period_id' => ['type' => 'int', 'nullable' => false],
            'submitted_by' => ['type' => 'int', 'nullable' => false],
            'content_json' => ['type' => 'text', 'nullable' => true],
            'submission_date' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'],
            'is_draft' => ['type' => 'tinyint', 'length' => 1, 'nullable' => false, 'default' => 0],
        ],
    ],
    'reporting_periods' => [
        'columns' => [
            'period_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'year' => ['type' => 'int', 'nullable' => false],
            'period_type' => ['type' => 'enum', 'values' => ['quarter','half','yearly'], 'nullable' => false, 'default' => 'quarter'],
            'period_number' => ['type' => 'int', 'nullable' => false],
            'start_date' => ['type' => 'date', 'nullable' => false],
            'end_date' => ['type' => 'date', 'nullable' => false],
            'status' => ['type' => 'enum', 'values' => ['open','closed'], 'nullable' => true, 'default' => 'open'],
            'updated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ],
    ],
    'reports' => [
        'columns' => [
            'report_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'period_id' => ['type' => 'int', 'nullable' => false],
            'report_name' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'description' => ['type' => 'text', 'nullable' => true],
            'pdf_path' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'pptx_path' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'generated_by' => ['type' => 'int', 'nullable' => false],
            'generated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'is_public' => ['type' => 'tinyint', 'length' => 1, 'nullable' => false, 'default' => 0],
        ],
    ],
    'targets' => [
        'columns' => [
            'target_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'target_name' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'status_description' => ['type' => 'varchar', 'length' => 500, 'nullable' => true],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'],
        ],
    ],
    'users' => [
        'columns' => [
            'user_id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
            'username' => ['type' => 'varchar', 'length' => 100, 'nullable' => false],
            'pw' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'fullname' => ['type' => 'varchar', 'length' => 200, 'nullable' => true],
            'email' => ['type' => 'varchar', 'length' => 255, 'nullable' => false],
            'agency_id' => ['type' => 'int', 'nullable' => false],
            'role' => ['type' => 'enum', 'values' => ['admin','agency','focal'], 'nullable' => false],
            'created_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'timestamp', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'],
            'is_active' => ['type' => 'tinyint', 'length' => 1, 'nullable' => true, 'default' => 1],
        ],
    ],
]; 