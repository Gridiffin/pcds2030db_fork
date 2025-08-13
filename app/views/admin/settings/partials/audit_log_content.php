<?php
// Define nice table names for display
$nice_table_names = [
    'users' => 'Users',
    'programs' => 'Programs',
    'outcomes' => 'Outcomes',
    'initiatives' => 'Initiatives',
    'agencies' => 'Agencies',
    'reporting_periods' => 'Reporting Periods',
    'sector_metrics_data' => 'Sector Metrics Data',
    'program_submissions' => 'Program Submissions',
    'program_targets' => 'Program Targets',
    'audit_logs' => 'Audit Logs'
];

// Get filter values from GET parameters
$filter_event_type = $_GET['event_type'] ?? 'All';
$filter_table = $_GET['table_name'] ?? 'All';

// Filter audit entries based on filters
function filter_audit_entries($entries, $event_type, $table_name) {
    return array_filter($entries, function($entry) use ($event_type, $table_name) {
        $event_type_match = ($event_type === 'All') || ($entry['event_type'] === $event_type);
        $table_match = ($table_name === 'All') || ($entry['table_name'] === $table_name);
        return $event_type_match && $table_match;
    });
}

// Filtered audit entries
$filtered_audit_entries = [];

// Connect to database
global $conn;

// Get all tables with created_at and updated_at columns
$sql = "
    SELECT TABLE_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND COLUMN_NAME IN ('created_at', 'updated_at')
    GROUP BY TABLE_NAME
    HAVING COUNT(DISTINCT COLUMN_NAME) = 2
";
$result = $conn->query($sql);
$tables = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row['TABLE_NAME'];
    }
}
// Ensure outcomes table is included in audit logs
if (!in_array('outcomes', $tables)) {
    $tables[] = 'outcomes';
}

$audit_entries = [];

foreach ($tables as $table) {
    // Try to get primary key column name
    $pk_sql = "
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = ?
        AND COLUMN_KEY = 'PRI'
        LIMIT 1
    ";
    $stmt = $conn->prepare($pk_sql);
    $stmt->bind_param("s", $table);
    $stmt->execute();
    $pk_result = $stmt->get_result();
    $pk_column = null;
    if ($pk_result && $pk_row = $pk_result->fetch_assoc()) {
        $pk_column = $pk_row['COLUMN_NAME'];
    }
    $stmt->close();

    if (!$pk_column) {
        // If no primary key, skip this table
        continue;
    }

    // Determine if we need to fetch a name column for display instead of record_id
    $name_column = null;
    if ($table === 'sector_metrics_data') {
        $name_column = 'table_name';
    } elseif ($table === 'users') {
        $name_column = 'username';        
    } elseif ($table === 'programs') {
        $name_column = 'program_name';
    } elseif ($table === 'outcomes') {
        $name_column = 'title'; // Use title column for outcomes
    }

    // Build select columns string - handle tables without created_at
    $timestamp_columns = "updated_at";
    if ($table !== 'outcomes') {
        $timestamp_columns = "created_at, updated_at";
    }
    
    $select_columns = "$pk_column AS record_id, $timestamp_columns";
    if ($name_column) {
        $select_columns = "$pk_column AS record_id, $name_column, $timestamp_columns";
    }

    // Fetch records with id, name (if applicable), created_at, updated_at
    $data_sql = "SELECT ? AS table_name, $select_columns FROM $table";
    $stmt = $conn->prepare($data_sql);
    $stmt->bind_param("s", $table);
    $stmt->execute();
    $data_result = $stmt->get_result();
    $rows = [];
    if ($data_result) {
        while ($data_row = $data_result->fetch_assoc()) {
            // Initialize display_id with the record_id
            $display_id = $data_row['record_id'];
            // Override with name column if available
            if ($name_column && isset($data_row[$name_column])) {
                $display_id = $data_row[$name_column];
            }
            // Add created_at entry only if the column exists
            if (isset($data_row['created_at'])) {
                $rows[] = [
                    'table_name' => $table,
                    'record_id' => $display_id,
                    'event_type' => 'Created',
                    'event_date' => $data_row['created_at']
                ];
                // Add updated_at entry only if different from created_at
                if ($data_row['updated_at'] !== $data_row['created_at']) {
                    $rows[] = [
                        'table_name' => $table,
                        'record_id' => $display_id,
                        'event_type' => 'Updated',
                        'event_date' => $data_row['updated_at']
                    ];
                }
            } else {
                // For tables without created_at, just show updated_at
                $rows[] = [
                    'table_name' => $table,
                    'record_id' => $display_id,
                    'event_type' => 'Updated',
                    'event_date' => $data_row['updated_at']
                ];
            }
        }
    }
    $stmt->close();

    $audit_entries = array_merge($audit_entries, $rows);
}

// Sort audit entries by event_date descending
usort($audit_entries, function($a, $b) {
    return strtotime($b['event_date']) <=> strtotime($a['event_date']);
});

// Apply filters
$filtered_audit_entries = filter_audit_entries($audit_entries, $filter_event_type, $filter_table);
?>

<div class="container-fluid px-4 py-4">
    <!-- Filter form -->
    <form method="get" class="mb-4 d-flex gap-3 align-items-center">
        <div>
            <label for="event_type" class="form-label">Filter by Action:</label>
            <select name="event_type" id="event_type" class="form-select">
                <?php
                $event_types = ['All', 'Created', 'Updated'];
                foreach ($event_types as $type) {
                    $selected = ($filter_event_type === $type) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($type) . "\" $selected>" . htmlspecialchars($type) . "</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label for="table_name" class="form-label">Filter by Table:</label>
            <select name="table_name" id="table_name" class="form-select">
                <option value="All" <?php echo ($filter_table === 'All') ? 'selected' : ''; ?>>All</option>
                <?php
                foreach ($tables as $table) {
                    $selected = ($filter_table === $table) ? 'selected' : '';
                    $nice_name = $nice_table_names[$table] ?? ucwords(str_replace('_', ' ', $table));
                    echo "<option value=\"" . htmlspecialchars($table) . "\" $selected>" . htmlspecialchars($nice_name) . "</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary mt-4">Apply Filters</button>
        </div>
    </form>

    <?php if (empty($filtered_audit_entries)): ?>
        <div class="alert alert-info">No audit log entries found.</div>
    <?php else: ?>
        <div class="audit-log" style="max-width: auto; margin-left: auto; margin-right: auto;">
            <?php foreach ($filtered_audit_entries as $entry): ?>
                <?php
                $nice_table_name = $nice_table_names[$entry['table_name']] ?? ucwords(str_replace('_', ' ', $entry['table_name']));
                ?>
                <div class="audit-log-entry audit-log-bubble">
                    <span style="font-weight: bold; display: flex; align-items: center; gap: 6px;">
                        <?php
                        $icon = '';
                        $color = '';
                        if ($entry['event_type'] === 'Created') {
                            $icon = '<span class="material-icons" style="color: green; font-size: 1.2em;">playlist_add</span>';
                        } elseif ($entry['event_type'] === 'Updated') {
                            $icon = '<span class="material-icons" style="color: goldenrod; font-size: 1.2em;">edit_square</span>';
                        }
                        ?>
                        <?php echo $icon; ?>
                        <?php echo htmlspecialchars($entry['record_id'] ?? 'N/A'); ?>
                        <small>was</small> <?php echo htmlspecialchars($entry['event_type']); ?>
                        at <small style="color: #666;"><?php echo htmlspecialchars($entry['event_date']); ?></small>
                    </span>
                    <div style="font-size: 0.9em; color: #555; margin-top: 4px;">
                        <?php echo htmlspecialchars($nice_table_name); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
