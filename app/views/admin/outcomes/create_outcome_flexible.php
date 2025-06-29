<?php
/**
 * Create Outcome for Admin
 * 
 * Unified outcome creation with custom table structures
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Initialize variables
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_name = trim($_POST['table_name'] ?? '');
    $structure_type = 'custom'; // Always use custom structure
    $row_config_json = $_POST['row_config'] ?? '';
    $column_config_json = $_POST['column_config'] ?? '';
    $data_json = $_POST['data_json'] ?? '';
    $sector_id = intval($_POST['sector_id'] ?? 0);

    if ($table_name === '' || $data_json === '' || $sector_id === 0) {
        $message = 'Table name, sector, and data are required.';
        $message_type = 'danger';
    } else {
        // Get max metric_id for this sector
        $max_metric_id = 0;
        $query = "SELECT MAX(metric_id) AS max_metric_id FROM sector_outcomes_data WHERE sector_id = ?";
        $stmt_max = $conn->prepare($query);
        $stmt_max->bind_param("i", $sector_id);
        $stmt_max->execute();
        $result_max = $stmt_max->get_result();
        if ($row = $result_max->fetch_assoc()) {
            $max_metric_id = intval($row['max_metric_id']);
        }
        $metric_id = $max_metric_id + 1;

        try {
            $conn->begin_transaction();

            // Insert the new outcome with custom structure
            $query = "INSERT INTO sector_outcomes_data 
                         (metric_id, sector_id, table_name, data_json, table_structure_type, row_config, column_config, submitted_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $submitted_by = $_SESSION['username'] ?? 'admin';
            $stmt->bind_param(
                "iissssss",
                $metric_id,
                $sector_id,
                $table_name,
                $data_json,
                $structure_type,
                $row_config_json,
                $column_config_json,
                $submitted_by
            );

            if ($stmt->execute()) {
                $outcome_id = $conn->insert_id;
                
                // Log the creation
                log_audit_action(
                    'outcome_created',
                    "Created outcome '{$table_name}' (ID: {$outcome_id}) with structure type '{$structure_type}' for sector {$sector_id}",
                    'success',
                    $_SESSION['user_id']
                );

                $conn->commit();
                $message = "Outcome '{$table_name}' created successfully!";
                $message_type = 'success';
                
                // Clear form data on success
                $_POST = [];
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            
            // Log the error
            log_audit_action(
                'outcome_creation_failed',
                "Failed to create outcome '{$table_name}' for sector {$sector_id}: " . $e->getMessage(),
                'error',
                $_SESSION['user_id']
            );
            
            error_log("Outcome creation error: " . $e->getMessage());
            $message = 'Error creating outcome: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Get list of sectors for admin to choose from
$sectors = [];
$query = "SELECT sector_id, name FROM sectors ORDER BY name";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $sectors[] = $row;
}

// Define the page header configuration
$page_config = [
    'title' => 'Create New Outcome',
    'subtitle' => 'Design custom data tables with flexible rows and columns to track your outcomes',
    'back_link' => ['url' => 'manage_outcomes.php', 'text' => 'Back to Manage Outcomes'],
    'current_step' => 1,
    'total_steps' => 1
];

// Include the admin layout header
include ROOT_PATH . 'app/views/layouts/admin_header.php';
?>

<style>
    .preview-container {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .table-structure-preview table {
        font-size: 0.9rem;
    }
    
    .table-structure-preview th {
        background-color: #e9ecef;
        font-weight: 600;
    }
    
    .table-structure-preview td {
        background-color: white;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .btn:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .invalid-feedback {
        display: block;
    }
    
    .required-field::after {
        content: ' *';
        color: #dc3545;
    }
    
    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .structure-info {
        background-color: #e7f3ff;
        border: 1px solid #b8daff;
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .structure-info h6 {
        color: #004085;
        margin-bottom: 0.5rem;
    }
    
    .structure-info p {
        color: #004085;
        margin-bottom: 0;
    }
</style>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= $page_config['title'] ?></h1>
            <p class="mb-0 text-muted"><?= $page_config['subtitle'] ?></p>
        </div>
        <a href="<?= $page_config['back_link']['url'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> <?= $page_config['back_link']['text'] ?>
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Instructions Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="card-title m-0">
                <i class="fas fa-lightbulb me-2"></i>How to Use the Flexible Table Designer
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-cogs me-2 text-primary"></i>1. Design Structure</h6>
                        <p class="small mb-0">Create your own custom table structure with complete flexibility.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-list me-2 text-success"></i>2. Add Rows</h6>
                        <p class="small mb-0">Add row labels for your data categories (metrics, time periods, etc.).</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-columns me-2 text-warning"></i>3. Define Columns</h6>
                        <p class="small mb-0">Create columns with specific data types (number, currency, percentage, text).</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-edit me-2 text-info"></i>4. Enter Data</h6>
                        <p class="small mb-0">Fill in your data and save your outcome.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>Design Your Outcome Table
                    </h6>
                </div>
                <div class="card-body">
                    <div class="structure-info">
                        <h6><i class="fas fa-info-circle me-1"></i>Custom Structure</h6>
                        <p class="small mb-0">For custom structure, add your own row labels and types.</p>
                    </div>
                    
                    <!-- Table Structure Designer Container -->
                    <div id="tableStructureDesigner"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs me-2"></i>Outcome Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form id="createFlexibleOutcomeForm" method="POST">
                        <div class="mb-3">
                            <label for="table_name" class="form-label required-field">Outcome Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="table_name" 
                                   name="table_name" 
                                   placeholder="Enter outcome name"
                                   value="<?= htmlspecialchars($_POST['table_name'] ?? '') ?>"
                                   required>
                            <div class="help-text">
                                Give your outcome a descriptive name (e.g., "Monthly Health Metrics", "Quarterly Sales Report")
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sector_id" class="form-label required-field">Sector</label>
                            <select class="form-select" id="sector_id" name="sector_id" required>
                                <option value="">Select Sector</option>
                                <?php foreach ($sectors as $sector): ?>
                                    <option value="<?= $sector['sector_id'] ?>" 
                                            <?= (isset($_POST['sector_id']) && $_POST['sector_id'] == $sector['sector_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sector['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="help-text">
                                Select the sector this outcome belongs to
                            </div>
                        </div>

                        <!-- Hidden fields for structure data -->
                        <input type="hidden" name="structure_type" id="structure_type" value="custom">
                        <input type="hidden" name="row_config" id="row_config">
                        <input type="hidden" name="column_config" id="column_config">
                        <input type="hidden" name="data_json" id="data_json">

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Create Outcome
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include necessary JavaScript files -->
<script src="<?= APP_URL ?>/assets/js/table-structure-designer.js"></script>

<script>
    // Initialize the table structure designer
    const designer = new TableStructureDesigner({
        container: '#tableStructureDesigner',
        structureType: 'custom',
        showPreview: true,
        allowStructureChange: false, // Structure type is fixed to custom
        mode: 'create'
    });

    // Handle form submission
    document.getElementById('createFlexibleOutcomeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get the structure data from the designer
        const structureData = designer.getStructureData();
        
        if (!structureData.isValid) {
            alert('Please complete the table structure before creating the outcome.');
            return;
        }
        
        // Update hidden fields (structure type is always custom)
        document.getElementById('row_config').value = JSON.stringify(structureData.rows);
        document.getElementById('column_config').value = JSON.stringify(structureData.columns);
        document.getElementById('data_json').value = JSON.stringify(structureData.data);
        
        // Structure type is always custom, no need to update
        
        // Submit the form
        this.submit();
    });

    // Auto-save functionality (optional)
    let autoSaveTimer;
    // Bind form changes for real-time updates
    document.getElementById('table_name').addEventListener('input', updatePreview);
    document.getElementById('sector_id').addEventListener('change', updatePreview);

    // Preview update function
    function updatePreview() {
        const structureData = designer.getStructureData();
        if (structureData.isValid) {
            const previewHTML = `
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Metric</th>
                                ${structureData.columns.map(col => `<th>${col.label}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${structureData.rows.filter(row => row.type !== 'separator').map(row => `
                                <tr>
                                    <td><strong>${row.label}</strong></td>
                                    ${structureData.columns.map(col => `<td>-</td>`).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            
            // Update preview if container exists
            const previewContainer = document.getElementById('tablePreview');
            if (previewContainer) {
                previewContainer.innerHTML = previewHTML;
            }
        }
    }

    // Initialize with default structure
    designer.initializeStructure({
        type: 'custom',
        structure_type: 'custom', // Always custom structure
        rows: [],
        columns: []
    });
</script>

<style>
.structure-type-card input[type="radio"]:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.structure-type-card input[type="radio"]:checked ~ .card-body {
    background-color: #f8f9ff;
}

.structure-type-card:has(input[type="radio"]:checked) {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.structure-type-card:has(input[type="radio"]:checked) .card-body {
    background-color: #f8f9ff;
}
</style>

<?php include ROOT_PATH . 'app/views/layouts/admin_footer.php'; ?>
