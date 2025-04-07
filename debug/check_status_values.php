<?php
/**
 * Status Values Debugging Tool
 * 
 * This script helps diagnose issues with program status values by monitoring
 * the status values being submitted and stored in the database.
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Security check - allow access from localhost or for admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if (!$is_admin && !$is_localhost) {
    die('Access denied. This tool is for administrators or development environments only.');
}

// Get status distribution
$status_query = "SELECT status, COUNT(*) as count
                FROM program_submissions 
                GROUP BY status 
                ORDER BY count DESC";
$status_result = $conn->query($status_query);
$status_counts = [];

if ($status_result) {
    while ($row = $status_result->fetch_assoc()) {
        $status_counts[] = $row;
    }
}

// Get submissions with each status value
$status_details_query = "SELECT ps.status, ps.status_date, p.program_name, ps.submission_id, ps.program_id
                        FROM program_submissions ps
                        JOIN programs p ON ps.program_id = p.program_id
                        ORDER BY ps.status, ps.submission_id DESC 
                        LIMIT 100";
$status_details_result = $conn->query($status_details_query);
$status_details = [];

if ($status_details_result) {
    while ($row = $status_details_result->fetch_assoc()) {
        $status_details[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Values Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .badge {
            font-size: 85%;
            padding: 0.35em 0.65em;
        }
        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .debug-tools {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .debug-tools a {
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            color: #495057;
            background-color: #e9ecef;
            transition: all 0.2s;
        }
        .debug-tools a:hover {
            background-color: #dee2e6;
        }
        .debug-tools a.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Status Values Debugging</h1>
            <div>
                <a href="../views/agency/create_program.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Create Program
                </a>
                <a href="../views/agency/view_programs.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-list me-1"></i> View Programs
                </a>
            </div>
        </div>
        
        <div class="debug-tools">
            <a href="check_program_creation.php">
                <i class="fas fa-code me-1"></i> Program Creation
            </a>
            <a href="check_status_values.php" class="active">
                <i class="fas fa-tasks me-1"></i> Status Values
            </a>
        </div>
        
        <!-- Status Distribution Chart -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Distribution</h5>
            </div>
            <div class="card-body">
                <?php if (empty($status_counts)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No status data found.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-4">
                            <canvas id="statusChart" width="100" height="100"></canvas>
                        </div>
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Count</th>
                                            <th>Visual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($status_counts as $status): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        switch($status['status']) {
                                                            case 'on-track': echo 'success'; break;
                                                            case 'delayed': echo 'warning'; break;
                                                            case 'completed': echo 'primary'; break;
                                                            case 'not-started': echo 'secondary'; break;
                                                            default: echo 'info';
                                                        }
                                                    ?>">
                                                        <?php echo $status['status'] ? htmlspecialchars($status['status']) : 'NULL'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $status['count']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?php 
                                                            switch($status['status']) {
                                                                case 'on-track': echo 'success'; break;
                                                                case 'delayed': echo 'warning'; break;
                                                                case 'completed': echo 'primary'; break;
                                                                case 'not-started': echo 'secondary'; break;
                                                                default: echo 'info';
                                                            }
                                                        ?>" style="width: <?php echo $status['count'] * 5; ?>%">
                                                            <?php echo $status['count']; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Status Details -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Status Details</h5>
            </div>
            <div class="card-body">
                <?php if (empty($status_details)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No status details found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Submission ID</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                    <th>Status Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($status_details as $detail): ?>
                                    <tr>
                                        <td><?php echo $detail['submission_id']; ?></td>
                                        <td><?php echo htmlspecialchars($detail['program_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch($detail['status']) {
                                                    case 'on-track': echo 'success'; break;
                                                    case 'delayed': echo 'warning'; break;
                                                    case 'completed': echo 'primary'; break;
                                                    case 'not-started': echo 'secondary'; break;
                                                    default: echo 'info';
                                                }
                                            ?>">
                                                <?php echo $detail['status'] ? htmlspecialchars($detail['status']) : 'NULL'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $detail['status_date'] ? date('M j, Y', strtotime($detail['status_date'])) : 'Not set'; ?></td>
                                        <td>
                                            <a href="../views/agency/program_details.php?id=<?php echo $detail['program_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Debug SQL Queries -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-code me-2"></i>SQL Debug</h5>
            </div>
            <div class="card-body">
                <h6>SQL Queries Used:</h6>
                <pre>-- Status Distribution Query:
<?php echo $status_query; ?>

-- Status Details Query:
<?php echo $status_details_query; ?></pre>
                
                <div class="alert alert-warning mt-3">
                    <strong>Note:</strong> Status values should be one of: 'on-track', 'delayed', 'completed', 'not-started'.
                    If other values appear, there might be a data integrity issue.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chart initialization script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create status chart if data exists
            <?php if (!empty($status_counts)): ?>
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: [
                        <?php 
                        foreach ($status_counts as $status) {
                            echo "'" . ($status['status'] ? addslashes($status['status']) : 'NULL') . "', ";
                        }
                        ?>
                    ],
                    datasets: [{
                        data: [
                            <?php 
                            foreach ($status_counts as $status) {
                                echo $status['count'] . ", ";
                            }
                            ?>
                        ],
                        backgroundColor: [
                            '#28a745', // on-track (green)
                            '#ffc107', // delayed (yellow)
                            '#17a2b8', // completed (blue)
                            '#6c757d', // not-started (gray)
                            '#fd7e14'  // others (orange)
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            <?php endif; ?>
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>
