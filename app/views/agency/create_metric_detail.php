<?php
/**
 * Create Metric Detail
 * 
 * Interface for agency users to create metric details.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

ob_start();
session_start();
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once '../layouts/header.php';
require_once '../layouts/agency_nav.php';

// Add cache control headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$additionalScripts = [
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/dashboard.js'
];

// Handle JSON POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? '');
    $items = $input['items'] ?? [];
    $detail_id = isset($input['detail_id']) ? (int)$input['detail_id'] : null;

    $errors = [];

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    
    if (empty($items)) {
        $errors[] = 'At least one value-description pair is required.';
    } else {
        foreach ($items as $index => $item) {
            $value = trim($item['value'] ?? '');
            $description = trim($item['description'] ?? '');
            
            if ($value === '') {
                $errors[] = "Value #" . ($index + 1) . " is required.";
            }
            if ($description === '') {
                $errors[] = "Description #" . ($index + 1) . " is required.";
            }
        }
    }

    if (!empty($errors)) {
        header('Content-Type: application/json');
        ob_end_clean();
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }    // Get the selected layout type
    $layout_type = trim($input['layout_type'] ?? 'simple');
    
    // Prepare data for insertion or update using the new format
    $detail_name = $title;
    $detail_json = json_encode([
        'layout_type' => $layout_type,
        'items' => $items
    ]);

    if ($detail_id === null) {        // Insert new outcome detail
        // First check if a metric detail with the same name already exists
        $check_stmt = $conn->prepare("SELECT detail_id FROM metrics_details WHERE detail_name = ?");
        if (!$check_stmt) {
            $errors[] = 'Database error: ' . $conn->error;
        } else {
            $check_stmt->bind_param('s', $detail_name);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = 'An outcome detail with this name already exists. Please use a different name.';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // No duplicates found, proceed with insertion
                $stmt = $conn->prepare("INSERT INTO metrics_details (detail_name, detail_json, is_draft) VALUES (?, ?, 0)");
                if ($stmt) {
                    $stmt->bind_param('ss', $detail_name, $detail_json);
                    if ($stmt->execute()) {
                        header('Content-Type: application/json');
                        ob_end_clean();
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Outcome detail created successfully.',
                            'action' => 'create',
                            'new_id' => $stmt->insert_id,
                            'title' => $detail_name,
                            'items' => $items
                        ]);
                        exit;
                    } else {
                        $errors[] = 'Database error: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $errors[] = 'Database error: ' . $conn->error;
                }
            }
        }
    } else {        // Update existing outcome detail
        $stmt = $conn->prepare("UPDATE metrics_details SET detail_name = ?, detail_json = ? WHERE detail_id = ?");
        if ($stmt) {
            $stmt->bind_param('ssi', $detail_name, $detail_json, $detail_id);
            if ($stmt->execute()) {
                header('Content-Type: application/json');
                ob_end_clean();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Outcome detail updated successfully.',
                    'action' => 'update',
                    'updated_id' => $detail_id,
                    'title' => $detail_name,
                    'items' => $items
                ]);
                exit;
            } else {
                $errors[] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }

    header('Content-Type: application/json');
    ob_end_clean();
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Fetch existing metric details for display
$result = $conn->query("SELECT detail_id, detail_name, detail_json FROM metrics_details WHERE is_draft = 0 ORDER BY created_at DESC");
$detailsArray = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $jsonData = json_decode($row['detail_json'], true);
        
        // Handle both new and legacy format
        $items = [];
        $layout_type = 'simple'; // Default
        
        if (isset($jsonData['layout_type']) && isset($jsonData['items'])) {
            // New format
            $layout_type = $jsonData['layout_type'];
            $items = $jsonData['items'];
        } elseif (isset($jsonData['value']) && isset($jsonData['description'])) {
            // Legacy format - convert to new format
            $values = explode(';', $jsonData['value']);
            $descriptions = explode(';', $jsonData['description']);
            
            for ($i = 0; $i < count($values); $i++) {
                $items[] = [
                    'value' => $values[$i],
                    'description' => $descriptions[$i] ?? ''
                ];
            }
        }
        
        $detailsArray[] = [
            'id' => $row['detail_id'],
            'title' => $row['detail_name'],
            'layout_type' => $layout_type,
            'items' => $items,
            // Keep these fields for backward compatibility in the UI
            'value' => isset($jsonData['value']) ? $jsonData['value'] : implode(';', array_column($items, 'value')),
            'description' => isset($jsonData['description']) ? $jsonData['description'] : implode(';', array_column($items, 'description'))
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <title>Create Outcome Detail</title>
    <link href="<?php echo APP_URL; ?>/assets/css/main.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/components/forms.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/components/buttons.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/layout/navigation.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/layout/dashboard.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/custom/agency.css" rel="stylesheet"></head>
<body>    <div class="container mt-5">
        <h1>Create Outcome Detail</h1>
        <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>
        <div id="successContainer" class="alert alert-success" style="display: none;"></div>
          <form id="metricDetailForm" method="post" action="<?php echo view_url('$ViewType', '$currentFileName'); ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            
            <div class="mb-3">
                <label for="layout_type" class="form-label">Layout Type</label>
                <select class="form-control" id="layout_type" name="layout_type" required>
                    <option value="simple">Simple (Default)</option>
                    <option value="detailed_list">Detailed List</option>
                    <option value="comparison">Comparison</option>
                </select>
                <small class="text-muted">Select the visual presentation style for this KPI.</small>
            </div>
            
            <div class="mb-3">
                <h4>KPI Values and Descriptions</h4>
                <p class="text-muted small">Add the values and descriptions for this KPI. For simple layout, only the first item will be used. For detailed list, each item represents a row. For comparison layout, items are shown side by side.</p>
            </div>
            
            <div id="itemsContainer">
                <div class="item-container" data-index="0">
                    <span class="remove-item" onclick="removeItem(this)">×</span>
                    <div class="mb-3">
                        <label for="value_0" class="form-label">Value</label>
                        <input type="text" class="form-control" id="value_0" name="value_0" required>
                    </div>
                    <div class="mb-3">
                        <label for="description_0" class="form-label">Description</label>
                        <textarea class="form-control" id="description_0" name="description_0" rows="3" required></textarea>
                    </div>
                </div>
            </div>
            
            <button type="button" class="btn btn-secondary add-item-btn" onclick="addItem()">+ Add Another Value</button>
            
            <button type="submit" class="btn btn-primary" id="submitBtn">Create</button>
            <a href="<?php echo APP_URL; ?>/views/agency/create_metric_detail.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>        <div class="mt-5">
            <h2>Created Outcome Details</h2>            <div id="metricDetailsContainer">
                <?php if (empty($detailsArray)): ?>
                    <p>No outcome details found.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($detailsArray as $detail): ?>                            <li>
                                <div class="details-list">
                                    <div class="title">
                                        <h3><?= htmlspecialchars($detail['title']) ?></h3>
                                    </div>
                                    <div class="content">
                                        <?php
                                        $values = explode(';', $detail['value']);
                                        $descriptions = explode(';', $detail['description']);
                                        
                                        if (count($values) === 1): ?>
                                            <div class="value-display">
                                                <div class="value"><?= htmlspecialchars($values[0]) ?></div>
                                                <div class="description"><?= htmlspecialchars($descriptions[0] ?? '') ?></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="values-grid" style="grid-template-columns: repeat(<?= count($values) ?>, 1fr);">
                                                <?php foreach ($values as $val): ?>
                                                    <div class="value"><?= htmlspecialchars($val) ?></div>
                                                <?php endforeach; ?>
                                                <?php foreach ($descriptions as $desc): ?>
                                                    <div class="description"><?= htmlspecialchars($desc) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editMetricDetail(<?= $detail['id'] ?>)">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" onclick="deleteMetricDetail(<?= $detail['id'] ?>)">Delete</button>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Embed detailsArray as JS object for edit lookup
        const metricDetails = <?= json_encode($detailsArray) ?>;
        let editingDetailId = null;

        // Function to add a new item
        function addItem() {
            const container = document.getElementById('itemsContainer');
            const itemCount = container.children.length;
            const newIndex = itemCount;
            
            const newItem = document.createElement('div');
            newItem.className = 'item-container';
            newItem.dataset.index = newIndex;
            
            newItem.innerHTML = `
                <span class="remove-item" onclick="removeItem(this)">×</span>
                <div class="mb-3">
                    <label for="value_${newIndex}" class="form-label">Value</label>
                    <input type="text" class="form-control" id="value_${newIndex}" name="value_${newIndex}" required>
                </div>
                <div class="mb-3">
                    <label for="description_${newIndex}" class="form-label">Description</label>
                    <textarea class="form-control" id="description_${newIndex}" name="description_${newIndex}" rows="3" required></textarea>
                </div>
            `;
            
            container.appendChild(newItem);
        }
        
        // Function to remove an item
        function removeItem(element) {
            const container = document.getElementById('itemsContainer');
            if (container.children.length > 1) {
                element.parentElement.remove();
                // Reindex remaining items
                Array.from(container.children).forEach((child, index) => {
                    child.dataset.index = index;
                    const valueInput = child.querySelector('input[type="text"]');
                    const descriptionInput = child.querySelector('textarea');
                    valueInput.id = `value_${index}`;
                    valueInput.name = `value_${index}`;
                    descriptionInput.id = `description_${index}`;
                    descriptionInput.name = `description_${index}`;
                });
            } else {
                alert('You need at least one value-description pair.');
            }
        }        // Function to delete metric detail
        function deleteMetricDetail(id) {
            if (!confirm('Are you sure you want to delete this outcome detail? This action cannot be undone.')) {
                return;
            }
            
            const deleteBtn = document.querySelector(`button[onclick="deleteMetricDetail(${id})"]`);
            const originalText = deleteBtn.textContent;
            deleteBtn.textContent = 'Deleting...';
            deleteBtn.disabled = true;
            
            fetch(`delete_metric_detail.php?detail_id=${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove the deleted item from the UI
                    const itemToRemove = document.querySelector(`li button[onclick="deleteMetricDetail(${id})"]`).closest('li');
                    if (itemToRemove) {
                        itemToRemove.remove();
                    }
                    // Show success message
                    showAlert('Metric detail deleted successfully.', 'success');
                    // If no items left, show message
                    if (document.querySelectorAll('#metricDetailsContainer li').length === 0) {
                        document.getElementById('metricDetailsContainer').innerHTML = '<p>No metric details found.</p>';
                    }
                } else {
                    showAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showAlert('Failed to delete metric detail. Please try again.', 'error');
            })
            .finally(() => {
                deleteBtn.textContent = originalText;
                deleteBtn.disabled = false;
            });
        }

        // Function to load metric detail into form for editing
        function editMetricDetail(id) {
            const detail = metricDetails.find(d => d.id == id);
            if (!detail) {
                showAlert('Metric detail not found.', 'error');
                return;
            }
            editingDetailId = id;

            // Set title
            document.getElementById('title').value = detail.title;
            
            // Set layout type
            const layoutSelect = document.getElementById('layout_type');
            if (layoutSelect) {
                layoutSelect.value = detail.layout_type || 'simple';
            }

            // Clear existing items
            const container = document.getElementById('itemsContainer');
            container.innerHTML = '';

            // Handle both legacy format and new format
            if (detail.items && Array.isArray(detail.items)) {
                // New format with items array
                detail.items.forEach((item, i) => {
                    const newItem = document.createElement('div');
                    newItem.className = 'item-container';
                    newItem.dataset.index = i;
                    newItem.innerHTML = `
                        <span class="remove-item" onclick="removeItem(this)">×</span>
                        <div class="mb-3">
                            <label for="value_${i}" class="form-label">Value</label>
                            <input type="text" class="form-control" id="value_${i}" name="value_${i}" required value="${escapeHtml(item.value)}">
                        </div>
                        <div class="mb-3">
                            <label for="description_${i}" class="form-label">Description</label>
                            <textarea class="form-control" id="description_${i}" name="description_${i}" rows="3" required>${escapeHtml(item.description)}</textarea>
                        </div>
                    `;
                    container.appendChild(newItem);
                });
            } else {
                // Legacy format with value and description as semicolon-separated strings
                const values = detail.value.split(';');
                const descriptions = detail.description.split(';');

                for (let i = 0; i < values.length; i++) {
                    const newItem = document.createElement('div');
                    newItem.className = 'item-container';
                    newItem.dataset.index = i;
                    newItem.innerHTML = `
                        <span class="remove-item" onclick="removeItem(this)">×</span>
                        <div class="mb-3">
                            <label for="value_${i}" class="form-label">Value</label>
                            <input type="text" class="form-control" id="value_${i}" name="value_${i}" required value="${escapeHtml(values[i])}">
                        </div>
                        <div class="mb-3">
                            <label for="description_${i}" class="form-label">Description</label>
                            <textarea class="form-control" id="description_${i}" name="description_${i}" rows="3" required>${escapeHtml(descriptions[i] || '')}</textarea>
                        </div>
                    `;
                    container.appendChild(newItem);
                }
            }

            // Change submit button text to Update
            document.getElementById('submitBtn').textContent = 'Update';
            
            // Scroll to the form
            document.getElementById('metricDetailForm').scrollIntoView({ behavior: 'smooth' });
        }

        // Function to update metric detail in UI without reloading
        function updateMetricDetailInUI(id, title, items) {
            // Find the existing item in the UI
            const itemElement = document.querySelector(`li button[onclick="editMetricDetail(${id})"]`)?.closest('li');
            
            if (!itemElement) return;
            
            // Update the title
            const titleElement = itemElement.querySelector('h3');
            if (titleElement) titleElement.textContent = title;
            
            // Update values and descriptions
            const valuesContainer = itemElement.querySelector('div[style*="grid-template-columns"]') || 
                                  itemElement.querySelector('div[style*="align-items: center"]');
            
            if (items.length === 1) {
                valuesContainer.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="color: #007bff; font-weight: bold; font-size: 2rem;">${items[0].value}</div>
                        <div style="color: #000; font-size: 1rem;">${items[0].description}</div>
                    </div>
                `;
            } else {
                let valuesHTML = '';
                let descsHTML = '';
                
                items.forEach(item => {
                    valuesHTML += `<div style="color: #007bff; font-weight: bold; font-size: 2rem;">${item.value}</div>`;
                    descsHTML += `<div style="color: #000; font-size: 1rem;">${item.description}</div>`;
                });
                
                valuesContainer.innerHTML = `
                    <div style="display: grid; grid-template-columns: repeat(${items.length}, 1fr); grid-template-rows: auto auto; gap: 10px;">
                        ${valuesHTML}
                        ${descsHTML}
                    </div>
                `;
            }
            
            // Also update the metricDetails array for future edits
            const detailIndex = metricDetails.findIndex(d => d.id == id);
            if (detailIndex !== -1) {
                metricDetails[detailIndex] = {
                    id: id,
                    title: title,
                    value: items.map(i => i.value).join(';'),
                    description: items.map(i => i.description).join(';')
                };
            }
        }

        // Function to show alert messages
        function showAlert(message, type) {
            const container = document.getElementById(`${type}Container`);
            container.textContent = message;
            container.style.display = 'block';
            setTimeout(() => {
                container.style.display = 'none';
            }, 5000);
        }

        // Handle form submission
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('metricDetailForm');
            const errorContainer = document.getElementById('errorContainer');
            const successContainer = document.getElementById('successContainer');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                errorContainer.style.display = 'none';
                successContainer.style.display = 'none';

                const title = form.title.value.trim();
                const items = [];
                
                // Collect all items
                const itemContainers = document.querySelectorAll('.item-container');
                itemContainers.forEach(container => {
                    const index = container.dataset.index;
                    const value = document.getElementById(`value_${index}`).value.trim();
                    const description = document.getElementById(`description_${index}`).value.trim();
                    
                    items.push({
                        value: value,
                        description: description
                    });
                });

                const data = {
                    title: title,
                    items: items
                };

                // Only include detail_id if we're editing
                if (editingDetailId !== null) {
                    data.detail_id = editingDetailId;
                }

                // Disable the submit button to prevent duplicate submissions
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
.then(result => {
    if (result.success) {
        showAlert(result.message, 'success');
        
        // Reset editingDetailId before reload
        editingDetailId = null;

        // Reload the page after create or update action
        window.location.href = window.location.href;
    } else {
        if (result.errors && result.errors.length > 0) {
            const ul = document.createElement('ul');
            result.errors.forEach(function (error) {
                const li = document.createElement('li');
                li.textContent = error;
                ul.appendChild(li);
            });
            errorContainer.innerHTML = '';
            errorContainer.appendChild(ul);
            errorContainer.style.display = 'block';
        } else {
            showAlert('An unknown error occurred.', 'error');
        }
    }
})
                .catch((error) => {
                    console.error('Fetch error:', error);
                    showAlert('Failed to submit data. Please try again.', 'error');
                })
                .finally(() => {
                    // Re-enable the submit button
                    submitBtn.disabled = false;
                    submitBtn.textContent = editingDetailId ? 'Update' : 'Create';
                });
            });
        });
    </script>
<?php require_once '../layouts/footer.php'; ?>



