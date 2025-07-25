<?php
/**
 * Fix Admin Programs Path Issues
 * This script fixes all the incorrect path references in admin programs files
 */

$basePath = 'c:\laragon\www\pcds2030_dashboard_fork\app\views\admin\programs\\';
$fixes = [];

// List of files that need fixing
$problemFiles = [
    'bulk_assign_initiatives.php',
    'edit_program_2.0.php', 
    'edit_program_backup.php',
    'delete_program.php',
    'reopen_program.php',
    'resubmit.php',
    'unsubmit.php',
    'view_program.php',
    'programsOLD.php'
];

foreach ($problemFiles as $file) {
    $filePath = $basePath . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Fix ROOT_PATH references
        $content = str_replace('ROOT_PATH . \'app/', 'PROJECT_ROOT_PATH . \'app/', $content);
        $content = str_replace('ROOT_PATH.\'app/', 'PROJECT_ROOT_PATH . \'app/', $content);
        
        // Fix relative config include
        $content = str_replace('require_once \'../../../config/config.php\';', 'require_once PROJECT_ROOT_PATH . \'app/config/config.php\';', $content);
        
        // Add PROJECT_ROOT_PATH definition if missing
        if (!strpos($content, 'PROJECT_ROOT_PATH')) {
            $defineCode = "<?php\n/**\n * " . ucfirst(str_replace(['_', '.php'], [' ', ''], $file)) . "\n */\n\n// Define the project root path\nif (!defined('PROJECT_ROOT_PATH')) {\n    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);\n}\n\n";
            $content = preg_replace('/^<\?php\s*/', $defineCode, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $fixes[] = $file . ' - Fixed path references';
        }
    }
}

echo "Path fixes completed:\n";
foreach ($fixes as $fix) {
    echo "✓ " . $fix . "\n";
}
echo "\nTotal files fixed: " . count($fixes) . "\n";
?>
