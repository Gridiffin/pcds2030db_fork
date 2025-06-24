<?php
/**
 * Date Value Test - Admin Edit Program
 * 
 * Quick test to verify that date field value assignment is working correctly.
 */

// Include necessary files
require_once 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';

// Test with a specific program that has dates
$program_id = 165; // Forest Conservation Initiative (from our DB query)

$query = "SELECT program_id, program_name, start_date, end_date 
          FROM programs 
          WHERE program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $program_id);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Date Value Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .old-logic { background: #ffebee; }
        .new-logic { background: #e8f5e8; }
        input { padding: 8px; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>Date Value Assignment Test</h1>
    
    <?php if ($program): ?>
        <h2>Program: <?php echo htmlspecialchars($program['program_name']); ?></h2>
        <p><strong>Database Values:</strong></p>
        <ul>
            <li>Start Date: "<?php echo htmlspecialchars($program['start_date'] ?? 'NULL'); ?>"</li>
            <li>End Date: "<?php echo htmlspecialchars($program['end_date'] ?? 'NULL'); ?>"</li>
        </ul>
        
        <div class="test-section old-logic">
            <h3>OLD Logic (Complex Conditional)</h3>
            <label>Start Date:</label>
            <input type="date" value="<?php echo (!empty($program['start_date']) && $program['start_date'] !== '0000-00-00') ? $program['start_date'] : ''; ?>">
            <br>
            <label>End Date:</label>
            <input type="date" value="<?php echo (!empty($program['end_date']) && $program['end_date'] !== '0000-00-00') ? $program['end_date'] : ''; ?>">
        </div>
        
        <div class="test-section new-logic">
            <h3>NEW Logic (Simplified)</h3>
            <label>Start Date:</label>
            <input type="date" value="<?php echo htmlspecialchars($program['start_date'] ?? ''); ?>">
            <br>
            <label>End Date:</label>
            <input type="date" value="<?php echo htmlspecialchars($program['end_date'] ?? ''); ?>">
        </div>
        
        <div class="test-section">
            <h3>Debug Info</h3>
            <p><strong>Start Date Analysis:</strong></p>
            <ul>
                <li>Raw value: <?php var_dump($program['start_date']); ?></li>
                <li>is_null: <?php echo is_null($program['start_date']) ? 'true' : 'false'; ?></li>
                <li>empty: <?php echo empty($program['start_date']) ? 'true' : 'false'; ?></li>
                <li>== '0000-00-00': <?php echo ($program['start_date'] == '0000-00-00') ? 'true' : 'false'; ?></li>
            </ul>
            
            <p><strong>End Date Analysis:</strong></p>
            <ul>
                <li>Raw value: <?php var_dump($program['end_date']); ?></li>
                <li>is_null: <?php echo is_null($program['end_date']) ? 'true' : 'false'; ?></li>
                <li>empty: <?php echo empty($program['end_date']) ? 'true' : 'false'; ?></li>
                <li>== '0000-00-00': <?php echo ($program['end_date'] == '0000-00-00') ? 'true' : 'false'; ?></li>
            </ul>
        </div>
        
    <?php else: ?>
        <p style="color: red;">Program not found!</p>
    <?php endif; ?>
</body>
</html>
