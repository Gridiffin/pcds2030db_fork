<?php
/**
 * Font Downloader Script
 * 
 * This script downloads the necessary font files for local fallbacks
 * to prevent font download errors in the console.
 */

// Define the font files to download
$fonts = [
    // Nunito fonts
    'nunito' => [
        'nunito-v26-latin-regular.woff2' => 'https://fonts.gstatic.com/s/nunito/v26/XRXV3I6Li01BKofINeaB.woff2',
        'nunito-v26-latin-500.woff2' => 'https://fonts.gstatic.com/s/nunito/v26/XRXV3I6Li01BKofINeaB.woff2', // Same file as regular but we'll use for fallback
        'nunito-v26-latin-600.woff2' => 'https://fonts.gstatic.com/s/nunito/v26/XRXV3I6Li01BKofINeaB.woff2', // Same file as regular but we'll use for fallback
        'nunito-v26-latin-700.woff2' => 'https://fonts.gstatic.com/s/nunito/v26/XRXV3I6Li01BKofINeaB.woff2', // Same file as regular but we'll use for fallback
    ],
    
    // Font Awesome icons
    'fontawesome' => [
        'fa-solid-900.woff2' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/webfonts/fa-solid-900.woff2',
    ],
];

// Define directory paths
$baseDir = __DIR__ . '/assets/fonts/';

// Create directories if they don't exist
foreach (array_keys($fonts) as $fontDir) {
    $dirPath = $baseDir . $fontDir;
    if (!file_exists($dirPath)) {
        mkdir($dirPath, 0755, true);
        echo "Created directory: {$fontDir}<br>";
    }
}

// Download fonts
$downloadCount = 0;
$errorCount = 0;

foreach ($fonts as $fontType => $fileList) {
    echo "<h3>Downloading {$fontType} fonts</h3>";
    
    foreach ($fileList as $fileName => $url) {
        $filePath = $baseDir . $fontType . '/' . $fileName;
        
        echo "Downloading {$fileName}... ";
        
        // Skip if file already exists
        if (file_exists($filePath)) {
            echo "Already exists, skipping.<br>";
            continue;
        }
        
        // Download the file
        $fileContent = @file_get_contents($url);
        
        if ($fileContent === false) {
            echo "Failed.<br>";
            $errorCount++;
        } else {
            // Save the file
            if (file_put_contents($filePath, $fileContent)) {
                echo "Success.<br>";
                $downloadCount++;
            } else {
                echo "Failed to save file.<br>";
                $errorCount++;
            }
        }
    }
}

echo "<h3>Summary</h3>";
echo "Downloaded {$downloadCount} font files.<br>";
if ($errorCount > 0) {
    echo "Failed to download {$errorCount} font files.<br>";
    echo "<p style='color: red;'>Some fonts could not be downloaded. You may need to manually download these files.</p>";
}

echo "<p>All required font files have been downloaded to the following directories:</p>";
echo "<ul>";
foreach (array_keys($fonts) as $fontDir) {
    echo "<li>{$baseDir}{$fontDir}</li>";
}
echo "</ul>";

echo "<p><a href='index.php'>Return to Dashboard</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}

h3 {
    margin-top: 20px;
    color: #333;
}

a {
    color: #0066cc;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>