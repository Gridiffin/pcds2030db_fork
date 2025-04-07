<?php
/**
 * Program Creation Debugging Tool
 * 
 * This script helps debug issues with the program creation functionality.
 * It shows recent programs and their status values.
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Security check - allow access only if:
// 1. User is logged in as admin OR
// 2. Accessing from localhost
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if (!$is_admin && !$is_localhost) {
    die('Access denied. This tool is for administrators or development environments only.');
}

// Get recent programs with their status
$query = "SELECT p.program_id, p.program_name, p.description, p.created_at, 
          ps.status, ps.content_json
          FROM programs p 
          LEFT JOIN program_submissions ps ON p.program_id = ps.program_id
          ORDER BY p.created_at DESC 
          LIMIT 20";

$result = $conn->query($query);

// Check if there are any execution errors
if (!$result) {
    echo "Database query error: " . $conn->error;
    exit;
}

// Get submission schema details
$schema_query = "DESCRIBE program_submissions";
$schema_result = $conn->query($schema_query);
$schema_fields = [];

if ($schema_result) {
    while ($field = $schema_result->fetch_assoc()) {
        $schema_fields[] = $field;
    }
}

// Include header (simplified)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Creation Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .badge {
            font-size: 85%;
            padding: 0.35em 0.65em;
        }
        pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
        }
        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .card-header {
            font-weight: 600;
        }
        .debug-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Program Creation Debugging</h1>
            <div>
                <a href="../views/agency/create_program.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Create Program
                </a>
                <a href="../views/agency/view_programs.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-list me-1"></i> View Programs
                </a>
            </div>
        </div>
        
        <div class="debug-note">
            <h5><i class="fas fa-info-circle me-2"></i>Debug Information</h5>
            <p class="mb-0">This page helps diagnose issues with program creation by showing the database schema and recently created programs.</p>
        </div>
        
        <!-- Database Schema Information -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Schema - program_submissions</h5>
            </div>
            <div class="card-body">
                <?php if (empty($schema_fields)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Unable to fetch schema information.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Field</th>
                                    <th>Type</th>
                                    <th>Null</th>
                                    <th>Key</th>
                                    <th>Default</th>
                                    <th>Extra</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schema_fields as $field): ?>
                                    <tr <?php echo ($field['Field'] === 'status') ? 'class="table-primary"' : ''; ?>>
                                        <td>
                                            <strong><?php echo $field['Field']; ?></strong>
                                            <?php if ($field['Field'] === 'status'): ?>
                                                <span class="badge bg-info ms-2">Important</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $field['Type']; ?></td>
                                        <td><?php echo $field['Null']; ?></td>
                                        <td><?php echo $field['Key']; ?></td>
                                        <td><?php echo $field['Default'] ?? '<span class="text-muted">NULL</span>'; ?></td>
                                        <td><?php echo $field['Extra']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Program Submissions -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Programs</h5>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows === 0): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No programs found in the database.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Program Name</th>
                                    <th>Status</th>
                                    <th>Target</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['program_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['program_name']); ?></strong>
                                            <?php if (!empty($row['description'])): ?>
                                                <div class="small text-muted"><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?><?php echo strlen($row['description']) > 100 ? '...' : ''; ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['status']): ?>
                                                <span class="badge bg-<?php 
                                                    switch($row['status']) {
                                                        case 'on-track': echo 'success'; break;
                                                        case 'delayed': echo 'warning'; break;
                                                        case 'completed': echo 'primary'; break;
                                                        default: echo 'secondary';
                                                    }
                                                ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Not set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php 
                                            if ($row['content_json']) {
                                                $content = json_decode($row['content_json'], true);
                                                echo htmlspecialchars($content['target'] ?? 'Not set');
                                            } else {
                                                echo 'Not set';
                                            }
                                        ?></td>
                                        <td><?php echo date('M j, Y H:i:s', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="../views/agency/program_details.php?id=<?php echo $row['program_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Debug SQL Queries Section -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-code me-2"></i>SQL Debug</h5>
            </div>
            <div class="card-body">
                <h6>SQL Queries Used:</h6>
                <pre>-- Schema Query:
<?php echo $schema_query; ?>

-- Program Data Query:
<?php echo $query; ?></pre>
                
                <h6 class="mt-4">Insert Statement for program_submissions:</h6>
                <pre>-- Example INSERT statement for program_submissions:
INSERT INTO program_submissions (program_id, period_id, submitted_by, target, status, status_date) 
VALUES (1, 1, 1, 'Example target', 'on-track', '2023-10-15');</pre>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Make sure your <code>create_agency_program()</code> function correctly passes the status value to the database.
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-4 text-center text-muted">
            <p><small>Debug tool for PCDS2030 Dashboard. For development use only.</small></p>
            <p><a href="../index.php" class="text-decoration-none"><i class="fas fa-home me-1"></i> Return to Dashboard</a></p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>
