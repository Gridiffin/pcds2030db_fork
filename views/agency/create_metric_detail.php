<?php
ob_start();
session_start();
require_once '../../includes/db_connect.php';
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
    }

    // Convert items to the original format
    $valueString = implode(';', array_column($items, 'value'));
    $descriptionString = implode(';', array_column($items, 'description'));

    // Prepare data for insertion or update
    $detail_name = $title;
    $detail_json = json_encode([
        'value' => $valueString,
        'description' => $descriptionString
    ]);

    if ($detail_id === null) {
        // Insert new metric detail
        $stmt = $conn->prepare("INSERT INTO metrics_details (detail_name, detail_json, is_draft) VALUES (?, ?, 0)");
        if ($stmt) {
            $stmt->bind_param('ss', $detail_name, $detail_json);
            if ($stmt->execute()) {
                header('Content-Type: application/json');
                ob_end_clean();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Metric detail created successfully.',
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
    } else {
        // Update existing metric detail
        $stmt = $conn->prepare("UPDATE metrics_details SET detail_name = ?, detail_json = ? WHERE detail_id = ?");
        if ($stmt) {
            $stmt->bind_param('ssi', $detail_name, $detail_json, $detail_id);
            if ($stmt->execute()) {
                header('Content-Type: application/json');
                ob_end_clean();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Metric detail updated successfully.',
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
        $detailsArray[] = [
            'id' => $row['detail_id'],
            'title' => $row['detail_name'],
            'value' => $jsonData['value'] ?? '',
            'description' => $jsonData['description'] ?? ''
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Metric Detail</title>
    <link href="<?php echo APP_URL; ?>/assets/css/main.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/components/forms.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/components/buttons.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/layout/navigation.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/layout/dashboard.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/custom/agency.css" rel="stylesheet">
    <style>
        .delete-btn[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .item-container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            position: relative;
        }
        .remove-item {
            position: absolute;
            top: 5px;
            right: 10px;
            color: #dc3545;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .add-item-btn {
            margin-bottom: auto;
        }
        #metricDetailsContainer ul {
            list-style-type: none;
            padding: 0;
        }
        #metricDetailsContainer li {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Create Metric Detail</h1>
        <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>
        <div id="successContainer" class="alert alert-success" style="display: none;"></div>
        
        <form id="metricDetailForm" method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
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
        </form>

        <div class="mt-5">
            <h2>Created Metric Details</h2>
            <div id="metricDetailsContainer">
                <?php if (empty($detailsArray)): ?>
                    <p>No metric details found.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($detailsArray as $detail): ?>
                            <li>
                                <div style="display: flex; gap: 15px; align-items: center;">
                                    <div style="flex: 1;">
                                        <h3 style="font-weight: bold; margin: 0;"><?= htmlspecialchars($detail['title']) ?></h3>
                                    </div>
                                    <div style="flex: 3;">
                                        <?php
                                        $values = explode(';', $detail['value']);
                                        $descriptions = explode(';', $detail['description']);
                                        
                                        if (count($values) === 1): ?>
                                            <div style="display: flex; align-items: center; gap: 15px;">
                                                <div style="color: #007bff; font-weight: bold; font-size: 2rem;"><?= htmlspecialchars($values[0]) ?></div>
                                                <div style="color: #000; font-size: 1rem;"><?= htmlspecialchars($descriptions[0] ?? '') ?></div>
                                            </div>
                                        <?php else: ?>
                                            <div style="display: grid; grid-template-columns: repeat(<?= count($values) ?>, 1fr); grid-template-rows: auto auto; gap: 10px;">
                                                <?php foreach ($values as $val): ?>
                                                    <div style="color: #007bff; font-weight: bold; font-size: 2rem;"><?= htmlspecialchars($val) ?></div>
                                                <?php endforeach; ?>
                                                <?php foreach ($descriptions as $desc): ?>
                                                    <div style="color: #000; font-size: 1rem;"><?= htmlspecialchars($desc) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
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
        }

        // Function to delete metric detail
        function deleteMetricDetail(id) {
            if (!confirm('Are you sure you want to delete this metric detail? This action cannot be undone.')) {
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

            // Clear existing items
            const container = document.getElementById('itemsContainer');
            container.innerHTML = '';

            // Parse values and descriptions
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
                        <input type="text" class="form-control" id="value_${i}" name="value_${i}" required value="${values[i]}">
                    </div>
                    <div class="mb-3">
                        <label for="description_${i}" class="form-label">Description</label>
                        <textarea class="form-control" id="description_${i}" name="description_${i}" rows="3" required>${descriptions[i] || ''}</textarea>
                    </div>
                `;
                container.appendChild(newItem);
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
        // Reload the page after create or update action
        window.location.reload();
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