<?php
/**
 * Create Outcome for Agency
 * 
 * Unified outcome creation with custom table structures
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agency_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an agency user
if (!is_agency()) {
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
    $is_draft = isset($_POST['is_draft']) ? intval($_POST['is_draft']) : 0;

    if ($table_name === '' || $data_json === '') {
        $message = 'Table name and data are required.';
        $message_type = 'danger';
    } else {
        $sector_id = $_SESSION['sector_id'] ?? 0;

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

        // Decode JSON data
        $data_array = json_decode($data_json, true);
        $row_config_array = json_decode($row_config_json, true);
        $column_config_array = json_decode($column_config_json, true);
        
        if ($data_array === null) {
            $message = 'Invalid JSON data.';
            $message_type = 'danger';
        } else {
            try {
                // Insert new record into sector_outcomes_data with flexible structure
                $query = "INSERT INTO sector_outcomes_data 
                         (metric_id, sector_id, table_name, data_json, table_structure_type, row_config, column_config, is_draft, submitted_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iisssssii", 
                    $metric_id, 
                    $sector_id, 
                    $table_name, 
                    $data_json,
                    $structure_type,
                    $row_config_json,
                    $column_config_json,
                    $is_draft, 
                    $_SESSION['user_id']
                );

                if ($stmt->execute()) {
                    $outcome_id = $conn->insert_id;
                    
                    // Log successful outcome creation
                    log_audit_action(
                        'outcome_created',
                        "Created flexible outcome '{$table_name}' (ID: {$outcome_id}) with structure type '{$structure_type}' for sector {$sector_id}",
                        'success',
                        $_SESSION['user_id']
                    );

                    if ($is_draft) {
                        $message = "Outcome '{$table_name}' saved as draft successfully!";
                        $message_type = 'warning';
                    } else {
                        $message = "Outcome '{$table_name}' created successfully!";
                        $message_type = 'success';
                    }
                    
                    // Redirect to avoid resubmission
                    $_SESSION['success_message'] = $message;
                    header('Location: submit_outcomes.php');
                    exit;
                } else {
                    throw new Exception($conn->error);
                }
            } catch (Exception $e) {
                $message = 'Error creating outcome: ' . $e->getMessage();
                $message_type = 'danger';
                
                // Log outcome creation failure
                log_audit_action(
                    'outcome_creation_failed',
                    "Failed to create flexible outcome '{$table_name}' for sector {$sector_id}: " . $e->getMessage(),
                    'failure',
                    $_SESSION['user_id']
                );
            }
        }
    }
}

// Add CSS and JS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];
$additionalScripts = [
    APP_URL . '/assets/js/table-calculation-engine.js',
    APP_URL . '/assets/js/table-structure-designer.js'
];

// Include header and agency navigation
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Create New Outcome',
    'subtitle' => 'Design custom data tables with flexible rows and columns to track your outcomes',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'submit_outcomes.php',
            'text' => 'Back to Submit Outcomes',
            'icon' => 'fa-arrow-left',
            'class' => 'btn-outline-primary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4">
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
                        <h6><i class="fas fa-table me-2 text-primary"></i>1. Choose Structure</h6>
                        <p class="small mb-0">Select your table structure: Monthly, Quarterly, Yearly, or Custom.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-list me-2 text-success"></i>2. Define Rows</h6>
                        <p class="small mb-0">For custom structure, add your own row labels and types.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-columns me-2 text-warning"></i>3. Add Columns</h6>
                        <p class="small mb-0">Define columns with data types (number, currency, percentage, text) and units.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-database me-2 text-info"></i>4. Enter Data</h6>
                        <p class="small mb-0">Fill in your data and save as draft or final submission.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="createFlexibleOutcomeForm" method="POST">
        <!-- Basic Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title m-0">
                    <i class="fas fa-info-circle me-2"></i>Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="table_name" class="form-label">Outcome Table Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="table_name" name="table_name" 
                                   placeholder="Enter a descriptive name for your outcome table" required>
                            <div class="form-text">Give your outcome table a clear, descriptive name that identifies what data it contains.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Structure Designer -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title m-0">
                    <i class="fas fa-cogs me-2"></i>Table Structure Designer
                </h5>
            </div>
            <div class="card-body">
<div id="table-designer-container" class="table-designer-container" style="position: relative; z-index: 10000; background-color: #f0f8ff !important;">
    <!-- Table structure designer will be rendered here -->
</div>
            </div>
        </div>

        <!-- Table Preview -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title m-0">
                    <i class="fas fa-eye me-2"></i>Table Preview
                </h5>
            </div>
            <div class="card-body">
                <div id="table-preview-container" class="table-preview-container">
                    <!-- Table preview will be rendered here -->
                </div>
            </div>
        </div>

        <!-- Data Entry Section -->
        <div class="card shadow-sm mb-4" id="data-entry-section" style="display: none;">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title m-0">
                    <i class="fas fa-edit me-2"></i>Data Entry
                </h5>
            </div>
            <div class="card-body">
                <div id="data-entry-container">
                    <!-- Data entry table will be rendered here -->
                </div>
            </div>
        </div>

        <!-- Hidden Inputs -->
        <input type="hidden" name="structure_type" id="structure_type" value="custom">
        <input type="hidden" name="row_config" id="row_config" value="">
        <input type="hidden" name="column_config" id="column_config" value="">
        <input type="hidden" name="data_json" id="data_json" value="">

        <!-- Action Buttons -->
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <input type="hidden" name="is_draft" id="isDraftInput" value="0">
                <button type="submit" class="btn btn-success btn-lg me-3" id="saveBtn" 
                        onclick="document.getElementById('isDraftInput').value='0';">
                    <i class="fas fa-save me-2"></i>Save Outcome
                </button>
                <button type="submit" class="btn btn-warning btn-lg me-3" id="saveDraftBtn" 
                        onclick="document.getElementById('isDraftInput').value='1';">
                    <i class="fas fa-file-alt me-2"></i>Save as Draft
                </button>
                <a href="submit_outcomes.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the table structure designer
    console.log('Initializing Table Structure Designer...');
    const designer = initTableStructureDesigner({
        container: '#table-designer-container',
        previewContainer: '#table-preview-container',
        structureType: 'custom',
        onStructureChange: function(structureData) {
            console.log('Structure changed:', structureData);
            updateHiddenFields(structureData);
            renderDataEntryTable(structureData);
        }
    });

    // Handle form submission
    document.getElementById('createFlexibleOutcomeForm').addEventListener('submit', function(e) {
        const structureData = designer.getStructureData();
        
        // Validate that we have columns defined
        if (structureData.columns.length === 0) {
            e.preventDefault();
            alert('Please add at least one column to your table.');
            return;
        }
        
        // Collect all data
        const tableData = collectTableData(structureData);
        
        // Update hidden fields (structure type is always custom)
        document.getElementById('row_config').value = JSON.stringify(structureData.rowConfig);
        document.getElementById('column_config').value = JSON.stringify(structureData.columnConfig);
        document.getElementById('data_json').value = JSON.stringify(tableData);
    });

    function updateHiddenFields(structureData) {
        // Structure type is always custom, no need to update
        document.getElementById('row_config').value = JSON.stringify(structureData.rowConfig);
        document.getElementById('column_config').value = JSON.stringify(structureData.columnConfig);
    }

    function renderDataEntryTable(structureData) {
        const container = document.getElementById('data-entry-container');
        const section = document.getElementById('data-entry-section');
        
        if (structureData.columns.length === 0) {
            section.style.display = 'none';
            return;
        }
        
        section.style.display = 'block';
        
        const tableHTML = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover data-entry-table">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 150px;">Row</th>
                            ${structureData.columns.map(col => `
                                <th class="text-center">
                                    <div>${col.label}</div>
                                    ${col.unit ? `<small class="text-muted">(${col.unit})</small>` : ''}
                                </th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${structureData.rows.filter(row => row.type !== 'separator').map(row => `
                            <tr>
                                <td>
                                    <span class="row-badge ${row.type === 'calculated' ? 'calculated' : ''}">${row.label}</span>
                                </td>
                                ${structureData.columns.map(col => `
                                    <td>
                                        ${row.type === 'calculated' ? 
                                            `<span class="calculated-value" data-row="${row.id}" data-column="${col.id}">0</span>` :
                                            `<input type="text" class="form-control data-cell" 
                                                    data-row="${row.id}" 
                                                    data-column="${col.id}"
                                                    data-type="${col.type}"
                                                    placeholder="${getPlaceholderForType(col.type)}">`
                                        }
                                    </td>
                                `).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        container.innerHTML = tableHTML;
        
        // Add event listeners for data validation
        container.querySelectorAll('.data-cell').forEach(cell => {
            cell.addEventListener('input', function() {
                validateCellInput(this);
            });
        });
    }

    function getPlaceholderForType(type) {
        switch (type) {
            case 'currency': return '0.00';
            case 'percentage': return '0.0';
            case 'number': return '0';
            case 'text': return 'Enter text';
            default: return '';
        }
    }

    function validateCellInput(cell) {
        const type = cell.dataset.type;
        const value = cell.value;
        
        cell.classList.remove('is-invalid');
        
        if (value && type === 'number' || type === 'currency' || type === 'percentage') {
            if (isNaN(parseFloat(value))) {
                cell.classList.add('is-invalid');
            }
        }
    }

    function collectTableData(structureData) {
        const data = {
            structure_type: 'custom', // Always custom structure
            columns: structureData.columns.map(col => col.id),
            data: {}
        };
        
        // Collect data from input fields
        structureData.rows.forEach(row => {
            if (row.type !== 'separator') {
                data.data[row.id] = {};
                structureData.columns.forEach(col => {
                    const cell = document.querySelector(`[data-row="${row.id}"][data-column="${col.id}"]`);
                    let value = 0;
                    if (cell) {
                        if (cell.tagName === 'INPUT') {
                            const rawValue = cell.value.trim();
                            if (rawValue !== '') {
                                value = (col.type === 'text') ? rawValue : (parseFloat(rawValue) || 0);
                            }
                        } else {
                            value = parseFloat(cell.textContent) || 0;
                        }
                    }
                    data.data[row.id][col.id] = value;
                });
            }
        });
        
        return data;
    }
});
</script>

<style>
/* Temporary CSS to force visibility of row action buttons */
.row-actions {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    z-index: 10000 !important;
}

.row-actions .btn {
    display: inline-flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    z-index: 10000 !important;
}

/* Highlight the radio button circle when checked */
.structure-type-card input[type="radio"]:checked {
    outline: none;
    box-shadow: 0 0 8px 3px var(--bs-primary);
    border-radius: 50%;
}

/* Optional: enhance the label color when selected */
.structure-type-card:has(input[type="radio"]:checked) .card-body {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

/* Highlight the auto-calculation rows radio buttons when checked */
input.calculation-type-radio:checked {
    outline: none;
    box-shadow: 0 0 8px 3px var(--bs-primary);
    border-radius: 50%;
}

/* Optional: enhance the container color when selected */
input.calculation-type-radio:checked + label {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    border-radius: 8px;
    transition: background-color 0.3s ease;
    cursor: pointer;
}

/* Style for Add Row button text color and hover */
#add-row-btn {
    color: white !important;
    background-color: var(--bs-primary) !important;
    transition: background-color 0.3s ease;
}

#add-row-btn:hover {
    background-color: #0056b3 !important; /* lighter blue for hover */
    color: white !important;
}

/* Style for Add Column button text color and hover */
#add-column-btn {
    color: white !important;
    background-color: var(--bs-primary) !important;
    transition: background-color 0.3s ease;
}

#add-column-btn:hover {
    background-color: #0056b3 !important; /* lighter blue for hover */
    color: white !important;
}
</style>
<?php
// Include footer
require_once '../../layouts/footer.php';
?>
