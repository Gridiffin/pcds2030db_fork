<?php
/**
 * New Edit Outcome Page - Clean implementation
 * Allows editing of outcome data using the shared renderer and utilities
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

// Initialize the table renderer in edit mode
$renderer = new OutcomeTableRenderer('edit');
$renderer->setData($outcome['outcome_data'], $outcomeId);

// Get page title
$pageTitle = "Edit Outcome: " . htmlspecialchars($outcome['outcome_name']);
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
                        <a href="view_outcome_clean.php?id=<?php echo $outcomeId; ?>" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> View Only
                        </a>
                        <a href="../agency_dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Outcome Info -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Outcome Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($outcome['outcome_name']); ?></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($outcome['outcome_type'] ?? 'N/A'); ?></p>
                                <?php if ($outcome['description']): ?>
                                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($outcome['description'])); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb"></i>
                                    <strong>Editing Tips:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Use + buttons to add rows/columns</li>
                                        <li>Use × buttons to remove rows/columns</li>
                                        <li>Click row/column labels to rename them</li>
                                        <li>Enter numeric values in data cells</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Outcome Data -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit"></i> Edit Outcome Data
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="outcome-editor">
                            <?php 
                            if ($outcome['outcome_data']) {
                                echo $renderer->render(); 
                            } else {
                                // Render empty table for new data
                                $renderer->setData('{"rows":["Row 1"],"columns":["Column 1"],"data":{"Row 1":{"Column 1":""}}}', $outcomeId);
                                echo $renderer->render();
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Help Section -->
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-question-circle"></i> Help & Instructions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-plus-circle text-success"></i> Adding Elements</h6>
                                <ul>
                                    <li><strong>Add Row:</strong> Click the "Add Row" button below the table</li>
                                    <li><strong>Add Column:</strong> Click the green "+" button in the header</li>
                                </ul>
                                
                                <h6 class="mt-3"><i class="fas fa-minus-circle text-danger"></i> Removing Elements</h6>
                                <ul>
                                    <li><strong>Remove Row:</strong> Click the red "×" button next to the row label</li>
                                    <li><strong>Remove Column:</strong> Click the red "×" button next to the column label</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-edit text-primary"></i> Editing Data</h6>
                                <ul>
                                    <li><strong>Row/Column Labels:</strong> Click in the text field and type new name</li>
                                    <li><strong>Data Values:</strong> Click in any data cell and enter numbers</li>
                                    <li><strong>Save Changes:</strong> Click "Save Changes" when done</li>
                                </ul>
                                
                                <h6 class="mt-3"><i class="fas fa-exclamation-triangle text-warning"></i> Important Notes</h6>
                                <ul>
                                    <li>All row and column labels must be unique</li>
                                    <li>Only numeric values are allowed in data cells</li>
                                    <li>Changes are not saved until you click "Save Changes"</li>
                                </ul>
                            </div>
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
    
    <!-- Shared Outcome Utilities -->
    <script src="../../../../assets/js/shared/outcome-utils.js"></script>
    
    <!-- Page-specific JavaScript -->
    <script>
        $(document).ready(function() {
            // Initialize outcome utilities
            OutcomeUtils.init();
            
            // Override the save function to add validation
            const originalSave = OutcomeUtils.saveOutcome;
            OutcomeUtils.saveOutcome = function(outcomeId) {
                if (OutcomeUtils.validateTableData()) {
                    originalSave.call(this, outcomeId);
                }
            };
            
            // Auto-save functionality (optional)
            let autoSaveTimer = null;
            let hasUnsavedChanges = false;
            
            // Track changes
            $(document).on('input', '.cell-input, .row-label, .column-label', function() {
                hasUnsavedChanges = true;
                
                // Clear existing timer
                if (autoSaveTimer) {
                    clearTimeout(autoSaveTimer);
                }
                
                // Set new timer for auto-save (optional - can be disabled)
                // autoSaveTimer = setTimeout(function() {
                //     if (hasUnsavedChanges) {
                //         OutcomeUtils.saveOutcome(<?php echo $outcomeId; ?>);
                //         hasUnsavedChanges = false;
                //     }
                // }, 5000); // Auto-save after 5 seconds of inactivity
            });
            
            // Warn about unsaved changes
            window.addEventListener('beforeunload', function(e) {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return e.returnValue;
                }
            });
            
            // Clear unsaved flag when saved
            $(document).on('click', '.save-outcome', function() {
                hasUnsavedChanges = false;
            });
        });
        
        // Custom validation
        function validateOutcome() {
            const rows = OutcomeUtils.getCurrentRows();
            const columns = OutcomeUtils.getCurrentColumns();
            
            if (rows.length === 0) {
                alert('At least one row is required.');
                return false;
            }
            
            if (columns.length === 0) {
                alert('At least one column is required.');
                return false;
            }
            
            return OutcomeUtils.validateTableData();
        }
    </script>
    
    <!-- Custom styles for edit mode -->
    <style>
        .outcome-table.edit-mode .form-control {
            transition: all 0.3s ease;
        }
        
        .alert-info {
            border-left: 4px solid #17a2b8;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        /* Highlight unsaved changes */
        .has-changes {
            background-color: #fff3cd !important;
            border-color: #ffeeba !important;
        }
        
        /* Loading state */
        .saving {
            position: relative;
            pointer-events: none;
        }
        
        .saving::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 1000;
        }
    </style>
</body>
</html>
