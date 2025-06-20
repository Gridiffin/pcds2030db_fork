<?php
// Debug script to check attachment data
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/agencies/index.php';
require_once 'app/lib/agencies/program_attachments.php';

echo "<h1>Debug Attachment Data</h1>";

// Get a sample program ID with attachments
$result = $conn->query("SELECT DISTINCT program_id FROM program_attachments WHERE is_active = 1 LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $program_id = $row['program_id'];
      echo "<h2>Program ID: $program_id</h2>";
    
    // Get attachments directly from database (bypass access check for debugging)
    $stmt = $conn->prepare("
        SELECT pa.*, u.username as uploaded_by_name
        FROM program_attachments pa
        LEFT JOIN users u ON pa.uploaded_by = u.user_id
        WHERE pa.program_id = ? AND pa.is_active = 1
        ORDER BY pa.upload_date DESC
    ");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $attachments = [];
    while ($row = $result->fetch_assoc()) {
        $attachments[] = [
            'attachment_id' => $row['attachment_id'],
            'original_filename' => $row['original_filename'],
            'file_size' => $row['file_size'],
            'mime_type' => $row['mime_type'], // Use mime_type column directly
            'file_type' => $row['file_type'],
            'description' => $row['description'],
            'upload_date' => $row['upload_date'],
            'uploaded_by' => $row['uploaded_by_name']
        ];
    }
    
    echo "<h3>Attachment Data Structure:</h3>";
    echo "<pre>";
    print_r($attachments);
    echo "</pre>";
    
    if (!empty($attachments)) {
        echo "<h3>Rendered Attachment Display:</h3>";
        foreach ($attachments as $attachment) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<strong>Filename:</strong> " . htmlspecialchars($attachment['original_filename']) . "<br>";
            echo "<strong>MIME Type:</strong> " . ($attachment['mime_type'] ?? 'N/A') . "<br>";
            echo "<strong>File Size:</strong> " . format_file_size($attachment['file_size']) . "<br>";
            echo "<strong>Upload Date:</strong> " . date('M j, Y g:i A', strtotime($attachment['upload_date'])) . "<br>";
            echo "<strong>Icon:</strong> <i class='fas " . get_file_icon($attachment['mime_type'] ?? '') . "'></i><br>";
            echo "</div>";
        }
    } else {
        echo "<p>No attachments found for this program.</p>";
    }
} else {
    echo "<p>No programs found in database.</p>";
}
?>
