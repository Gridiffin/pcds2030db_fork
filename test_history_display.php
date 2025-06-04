<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/agencies/programs.php';

// Test program ID
$program_id = 168;

// Get program edit history
$program_history = get_program_edit_history($program_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Display Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/components/program-history.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Program History Display Test</h2>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Debug Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Program ID:</strong> <?php echo $program_id; ?></p>
                <p><strong>History Data Available:</strong> <?php echo isset($program_history['submissions']) ? 'Yes' : 'No'; ?></p>
                <?php if (isset($program_history['submissions'])): ?>
                    <p><strong>Submissions Count:</strong> <?php echo count($program_history['submissions']); ?></p>
                    <p><strong>Show History Condition (count > 1):</strong> <?php echo count($program_history['submissions']) > 1 ? 'TRUE' : 'FALSE'; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>History Display Test</h5>
            </div>
            <div class="card-body">
                <!-- Simulate the program name field with history -->
                <div class="mb-3">
                    <label for="program_name" class="form-label">Program Name</label>
                    <input type="text" class="form-control" id="program_name" name="program_name" value="Test Program">
                    
                    <!-- This is the exact code from update_program.php -->
                    <?php if (isset($program_history['submissions']) && count($program_history['submissions']) > 1): ?>
                        <?php
                        // Get complete history of program name changes
                        $name_history = get_field_edit_history($program_history['submissions'], 'program_name');
                        
                        if (!empty($name_history)):
                        ?>
                            <div class="d-flex align-items-center mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary field-history-toggle" 
                                        data-history-target="programNameHistory">
                                    <i class="fas fa-history"></i> Show Name History
                                </button>
                            </div>
                            <div id="programNameHistory" class="history-complete" style="display: none;">
                                <h6 class="small text-muted mb-2">Program Name History</h6>
                                <ul class="history-list">
                                    <?php foreach($name_history as $idx => $item): ?>
                                    <li class="history-list-item">
                                        <div class="history-list-value">
                                            <?php echo htmlspecialchars($item['value']); ?>
                                        </div>
                                        <div class="history-list-meta">
                                            <?php echo $item['timestamp']; ?>
                                            <?php if (isset($item['submission_id']) && $item['submission_id'] > 0): ?>
                                                <span class="<?php echo ($item['is_draft'] ?? 0) ? 'history-draft-badge' : 'history-final-badge'; ?>">
                                                    <?php echo ($item['is_draft'] ?? 0) ? 'Draft' : 'Final'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mt-2">No history data available for program name.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-warning mt-2">History condition not met (need more than 1 submission)</p>
                    <?php endif; ?>
                </div>
                
                <!-- Raw history data for debugging -->
                <div class="mt-4">
                    <h6>Raw History Data:</h6>
                    <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;">
<?php 
if (isset($program_history['submissions'])) {
    foreach ($program_history['submissions'] as $i => $sub) {
        echo "Submission " . ($i + 1) . ":\n";
        echo "  ID: " . ($sub['submission_id'] ?? 'N/A') . "\n";
        echo "  Period: " . ($sub['period_display'] ?? 'N/A') . "\n";
        echo "  Is Draft: " . (isset($sub['is_draft']) ? ($sub['is_draft'] ? 'Yes' : 'No') : 'N/A') . "\n";
        if (isset($sub['content_json'])) {
            $content = json_decode($sub['content_json'], true);
            if ($content && isset($content['program_name'])) {
                echo "  Program Name in JSON: " . $content['program_name'] . "\n";
            } else {
                echo "  Program Name in JSON: Not found\n";
            }
        }
        echo "\n";
    }
} else {
    echo "No submissions data found\n";
}
?>
                    </pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        // History toggle functionality (copied from update_program.php)
        document.querySelectorAll('.field-history-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-history-target');
                const targetPanel = document.getElementById(targetId);
                
                if (targetPanel) {
                    const isVisible = targetPanel.style.display !== 'none';
                    targetPanel.style.display = isVisible ? 'none' : 'block';
                    
                    // Update button text
                    if (isVisible) {
                        this.innerHTML = '<i class="fas fa-history"></i> Show Name History';
                    } else {
                        this.innerHTML = '<i class="fas fa-times"></i> Hide Name History';
                    }
                }
            });
        });
    </script>
</body>
</html>
