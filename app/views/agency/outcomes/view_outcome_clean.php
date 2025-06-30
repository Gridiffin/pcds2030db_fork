<?php
/**
 * New View Outcome Page - Clean implementation
 * Displays outcome data in read-only format using the shared renderer
 */

session_start();
require_once __DIR__ . '/../../../lib/db_connect.php';
require_once __DIR__ . '/../../../lib/session.php';
require_once __DIR__ . '/../../../lib/functions.php';
require_once __DIR__ . '/../../../lib/outcome_table_renderer.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'agency') {
    header("Location: ../../../login.php");
    exit();
}

// Get outcome ID from URL
$outcomeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$outcomeId) {
    die("Invalid outcome ID");
}

// Fetch outcome data
try {
    $stmt = $pdo->prepare("
        SELECT o.*, p.program_name, i.initiative_name 
        FROM outcomes o
        LEFT JOIN programs p ON o.program_id = p.program_id
        LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
        WHERE o.outcome_id = ? AND o.agency_id = ?
    ");
    $stmt->execute([$outcomeId, $_SESSION['agency_id']]);
    $outcome = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$outcome) {
        die("Outcome not found or access denied");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Initialize the table renderer
$renderer = new OutcomeTableRenderer('view');
$renderer->setData($outcome['outcome_data'], $outcomeId);

// Get page title
$pageTitle = "View Outcome: " . htmlspecialchars($outcome['outcome_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - PCDS2030 Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../../../assets/css/outcomes/outcome-tables.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                    <div>
                        <h1 class="h3 mb-0"><?php echo $pageTitle; ?></h1>
                        <?php if ($outcome['program_name']): ?>
                            <p class="text-muted mb-0">
                                Program: <?php echo htmlspecialchars($outcome['program_name']); ?>
                                <?php if ($outcome['initiative_name']): ?>
                                    | Initiative: <?php echo htmlspecialchars($outcome['initiative_name']); ?>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="edit_outcome_clean.php?id=<?php echo $outcomeId; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Outcome
                        </a>
                        <a href="../agency_dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Outcome Details -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line"></i> Outcome Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Outcome Name:</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($outcome['outcome_name']); ?></dd>
                                    
                                    <dt class="col-sm-4">Type:</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($outcome['outcome_type'] ?? 'N/A'); ?></dd>
                                    
                                    <dt class="col-sm-4">Status:</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-<?php echo $outcome['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($outcome['status'] ?? 'draft'); ?>
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Created:</dt>
                                    <dd class="col-sm-8"><?php echo date('M j, Y g:i A', strtotime($outcome['created_at'])); ?></dd>
                                    
                                    <dt class="col-sm-4">Last Updated:</dt>
                                    <dd class="col-sm-8"><?php echo date('M j, Y g:i A', strtotime($outcome['updated_at'])); ?></dd>
                                    
                                    <?php if ($outcome['description']): ?>
                                    <dt class="col-sm-4">Description:</dt>
                                    <dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($outcome['description'])); ?></dd>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Outcome Data Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table"></i> Outcome Data
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        if ($outcome['outcome_data']) {
                            echo $renderer->render(); 
                        } else {
                            echo '<div class="empty-state">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Data Available</h5>
                                    <p class="text-muted">This outcome has not been configured with data yet.</p>
                                    <a href="edit_outcome_clean.php?id=' . $outcomeId . '" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Data
                                    </a>
                                  </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Actions -->
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs"></i> Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="edit_outcome_clean.php?id=<?php echo $outcomeId; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit This Outcome
                            </a>
                            <button type="button" class="btn btn-info" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportData()">
                                <i class="fas fa-download"></i> Export Data
                            </button>
                        </div>
                        
                        <div class="btn-group float-end" role="group">
                            <a href="../agency_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Export data functionality
        function exportData() {
            const outcomeData = <?php echo json_encode($outcome); ?>;
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(outcomeData, null, 2));
            const downloadAnchorNode = document.createElement('a');
            downloadAnchorNode.setAttribute("href", dataStr);
            downloadAnchorNode.setAttribute("download", "outcome_" + outcomeData.outcome_id + "_data.json");
            document.body.appendChild(downloadAnchorNode);
            downloadAnchorNode.click();
            downloadAnchorNode.remove();
        }
        
        // Print styles
        window.addEventListener('beforeprint', function() {
            document.body.classList.add('printing');
        });
        
        window.addEventListener('afterprint', function() {
            document.body.classList.remove('printing');
        });
    </script>
    
    <style>
        @media print {
            .btn, .card-header, .no-print {
                display: none !important;
            }
            
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            
            .outcome-table-container {
                box-shadow: none !important;
            }
        }
    </style>
</body>
</html>
