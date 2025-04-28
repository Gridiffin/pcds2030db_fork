<?php
/**
 * Core Admin Functions
 * 
 * Contains basic admin authentication and permission functions
 */

require_once dirname(__DIR__) . '/utilities.php';

/**
 * Check if current user is admin
 * @return boolean
 */
function is_admin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'admin';
}

/**
 * Check admin permission
 * @return array|null Error message if not an admin
 */
function check_admin_permission() {
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    return null;
}

/**
 * Generate report for a specific period
 * @param int $period_id The reporting period ID
 * @return array Report info including paths to generated files
 */
function generate_report($period_id) {
    global $conn;
    
    // Ensure database connection is available
    if (!$conn || $conn->connect_error) {
        require_once dirname(dirname(__DIR__)) . '/includes/db_connect.php';
    }
    
    // Only allow admins to generate reports
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Validate period exists
    $period = get_reporting_period($period_id);
    if (!$period) {
        return ['error' => 'Invalid reporting period'];
    }
    
    // Create report name
    $report_name = "Q{$period['quarter']}-{$period['year']} Report";
    $description = "Performance report for Q{$period['quarter']}-{$period['year']} reporting period";
    
    // Define file paths
    $timestamp = time();
    $pptx_filename = "report_q{$period['quarter']}_{$period['year']}_{$timestamp}.pptx";
    $pptx_path = "pptx/" . $pptx_filename;
    $full_pptx_path = dirname(dirname(__DIR__)) . "/reports/" . $pptx_path;
    
    try {
        // Generate PowerPoint Report
        $pptx_result = generate_pptx_report($period_id, $full_pptx_path);
        
        if (!$pptx_result) {
            return ['error' => 'Failed to generate PowerPoint report'];
        }
        
        // Store report info in database
        $admin_id = $_SESSION['user_id'];
        
        // Check if reports table exists, if not create it
        $create_table_query = "CREATE TABLE IF NOT EXISTS `reports` (
            `report_id` int(11) NOT NULL AUTO_INCREMENT,
            `period_id` int(11) NOT NULL,
            `report_name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `pptx_path` varchar(255) NOT NULL,
            `pdf_path` varchar(255) DEFAULT NULL,
            `generated_by` int(11) NOT NULL,
            `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `is_public` tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`report_id`),
            KEY `period_id` (`period_id`),
            KEY `generated_by` (`generated_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $conn->query($create_table_query);
        
        $stmt = $conn->prepare("INSERT INTO reports (period_id, report_name, description, pptx_path, generated_by) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $period_id, $report_name, $description, $pptx_path, $admin_id);
        $stmt->execute();
        
        if ($stmt->affected_rows <= 0) {
            return ['error' => 'Failed to save report information'];
        }
        
        return [
            'success' => true,
            'message' => 'Report generated successfully',
            'pptx_path' => $pptx_path
        ];
    } catch (Exception $e) {
        error_log("Report generation error: " . $e->getMessage());
        return ['error' => 'An error occurred during report generation: ' . $e->getMessage()];
    }
}

/**
 * Generate PowerPoint report for a specific period
 * @param int $period_id The reporting period ID
 * @param string $filepath Full file path for the report
 * @return bool Success status
 */
function generate_pptx_report($period_id, $filepath) {
    global $conn;
    
    // Add debug logging
    error_log("Starting PowerPoint generation for period ID: $period_id");
    error_log("Target filepath: $filepath");
    
    try {
        // Ensure required libraries are loaded
        $vendorDir = dirname(dirname(__DIR__)) . '/vendor/';
        $autoloadPath = $vendorDir . 'autoload.php';
        
        error_log("Checking vendor directory: $vendorDir");
        if (!is_dir($vendorDir)) {
            error_log("ERROR: Vendor directory not found at: $vendorDir");
            return false;
        }
        
        error_log("Checking autoload.php: $autoloadPath");
        if (!file_exists($autoloadPath)) {
            error_log("ERROR: Autoload file not found at: $autoloadPath");
            return false;
        }
        
        // Load Composer autoloader
        error_log("Including autoload.php");
        require_once $autoloadPath;
        
        // Check if PhpPresentation class exists after loading autoloader
        error_log("Checking for PhpPresentation class...");
        if (!class_exists('PhpOffice\\PhpPresentation\\PhpPresentation')) {
            error_log("PhpPresentation class still not found. Attempting to load directly...");
            
            // Try direct inclusion of PhpPresentation files
            $phpPresentationDir = $vendorDir . 'phpoffice/phppresentation/src/PhpPresentation/';
            error_log("Checking for PhpPresentation directory: $phpPresentationDir");
            
            if (!is_dir($phpPresentationDir)) {
                error_log("ERROR: PhpPresentation directory not found. Please run: composer require phpoffice/phppresentation");
                return false;
            }
            
            // Check for autoload or bootstrap file in the package directly
            $bootstrapFile = $vendorDir . 'phpoffice/phppresentation/bootstrap.php';
            if (file_exists($bootstrapFile)) {
                error_log("Loading bootstrap file directly");
                require_once $bootstrapFile;
            }
        }
        
        // Final check for PhpPresentation class
        if (!class_exists('PhpOffice\\PhpPresentation\\PhpPresentation')) {
            error_log("ERROR: PhpPresentation class still not available. Try reinstalling the package with: composer update");
            return false;
        }
        
        error_log("PhpPresentation class successfully loaded");
        
        // Increase memory limit
        ini_set('memory_limit', '256M');
        
        // Get period information
        error_log("Retrieving period information for ID: $period_id");
        $period = get_reporting_period($period_id);
        if (!$period) {
            error_log("Period not found for ID: $period_id");
            return false;
        }
        
        // Create new presentation
        error_log("Creating new presentation object");
        $presentation = new \PhpOffice\PhpPresentation\PhpPresentation();
        
        // Set document properties
        $properties = $presentation->getDocumentProperties();
        $properties->setCreator('PCDS 2030 Dashboard')
                ->setLastModifiedBy('PCDS 2030 Dashboard')
                ->setTitle("Q{$period['quarter']}-{$period['year']} Performance Report")
                ->setSubject("Q{$period['quarter']}-{$period['year']} Performance Report")
                ->setDescription("Quarterly sector performance report")
                ->setCategory('Reports');
        
        // Get all sectors
        error_log("Retrieving sectors from database");
        $sectors_query = "SELECT sector_id, sector_name FROM sectors ORDER BY sector_name";
        $sectors_result = $conn->query($sectors_query);
        
        if (!$sectors_result) {
            error_log("Error retrieving sectors: " . $conn->error);
            return false;
        }
        
        $sector_count = $sectors_result->num_rows;
        error_log("Found $sector_count sectors to process");
        
        if ($sector_count == 0) {
            error_log("No sectors found. Creating empty presentation.");
            // Create a blank slide
            $slide = $presentation->createSlide();
            $shape = $slide->createRichTextShape()
                ->setHeight(300)
                ->setWidth(600)
                ->setOffsetX(100)
                ->setOffsetY(100);
            $shape->createTextRun("No sectors found in the database")
                ->getFont()->setSize(24);
        } else {
            while ($sector = $sectors_result->fetch_assoc()) {
                try {
                    error_log("Generating slide for sector ID: {$sector['sector_id']}, Name: {$sector['sector_name']}");
                    // Generate a dashboard slide for each sector
                    generateSectorDashboardSlide($presentation, $sector['sector_id'], $period_id);
                } catch (Exception $e) {
                    error_log("Error generating slide for sector {$sector['sector_name']}: " . $e->getMessage());
                    // Continue with other sectors
                }
            }
        }
        
        // Create writer
        error_log("Creating PowerPoint writer");
        $writer = \PhpOffice\PhpPresentation\IOFactory::createWriter($presentation, 'PowerPoint2007');
        
        // Ensure directory exists
        $dir = dirname($filepath);
        error_log("Checking if directory exists: $dir");
        if (!file_exists($dir)) {
            error_log("Directory doesn't exist, creating: $dir");
            mkdir($dir, 0777, true);
        }
        
        // Verify directory is writable
        if (!is_writable($dir)) {
            error_log("ERROR: Directory is not writable: $dir");
            // Try to make it writable
            chmod($dir, 0777);
            if (!is_writable($dir)) {
                error_log("ERROR: Failed to make directory writable: $dir");
                return false;
            }
        }
        
        // Save to file
        error_log("Saving presentation to: $filepath");
        $writer->save($filepath);
        
        // Verify file was created
        if (file_exists($filepath)) {
            error_log("Successfully created PowerPoint file: $filepath");
            return true;
        } else {
            error_log("ERROR: File was not created at: $filepath");
            return false;
        }
    } catch (Exception $e) {
        error_log("PowerPoint generation error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        return false;
    }
}

/**
 * Generate a sector dashboard slide (forestry-style) using data from the database
 * 
 * @param PhpOffice\PhpPresentation\PhpPresentation $presentation The presentation object
 * @param int $sector_id The sector ID to generate the slide for
 * @param int $period_id The reporting period ID
 * @return PhpOffice\PhpPresentation\Slide The created slide
 */
function generateSectorDashboardSlide($presentation, $sector_id, $period_id) {
    global $conn;
    
    // Get sector information
    $sector_query = "SELECT sector_name FROM sectors WHERE sector_id = ?";
    $stmt = $conn->prepare($sector_query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $sector_result = $stmt->get_result();
    $sector = $sector_result->fetch_assoc();
    
    if (!$sector) {
        throw new Exception("Sector not found");
    }
    
    // Get period information
    $period_query = "SELECT quarter, year, start_date, end_date FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $period_result = $stmt->get_result();
    $period = $period_result->fetch_assoc();
    
    if (!$period) {
        throw new Exception("Period not found");
    }
    
    // Get sector leadership
    $leadership_query = "SELECT user_id, full_name, position FROM users 
                        WHERE sector_id = ? AND role = 'agency' 
                        ORDER BY position DESC LIMIT 3";
    $stmt = $conn->prepare($leadership_query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $leadership_result = $stmt->get_result();
    
    $leadership_text = "";
    while ($leader = $leadership_result->fetch_assoc()) {
        $leadership_text .= ($leadership_text ? ", " : "") . $leader['full_name'] . " " . $leader['position'];
    }
    
    // Get program data for this sector and period
    $programs_query = "SELECT p.program_id, p.program_name, 
                        ps.status, ps.content_json,
                        u.agency_name
                      FROM programs p
                      JOIN program_submissions ps ON p.program_id = ps.program_id
                      JOIN users u ON p.owner_agency_id = u.user_id
                      WHERE p.sector_id = ? AND ps.period_id = ? AND ps.is_draft = 0
                      ORDER BY p.program_name";
    $stmt = $conn->prepare($programs_query);
    $stmt->bind_param("ii", $sector_id, $period_id);
    $stmt->execute();
    $programs_result = $stmt->get_result();
    
    $programs = [];
    while ($program = $programs_result->fetch_assoc()) {
        // Parse content_json to extract targets and other fields
        $content = [];
        
        // Fix JSON parsing - properly handle the content_json field
        if (!empty($program['content_json'])) {
            try {
                // Trim any whitespace or BOM characters that might be present
                $json_string = trim($program['content_json']);
                
                // Check if JSON format appears valid before parsing
                if (substr($json_string, 0, 1) === '{' && substr($json_string, -1) === '}') {
                    $content = json_decode($json_string, true);
                    
                    // If json_decode failed, log the error
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log("JSON decode error for program ID {$program['program_id']}: " . json_last_error_msg());
                        error_log("JSON content: " . substr($json_string, 0, 100) . "...");
                        $content = [];
                    }
                } else {
                    error_log("Invalid JSON format for program ID {$program['program_id']}: does not begin with { and end with }");
                    error_log("JSON content: " . substr($json_string, 0, 100) . "...");
                }
            } catch (Exception $e) {
                error_log("Exception parsing JSON for program ID {$program['program_id']}: " . $e->getMessage());
                $content = [];
            }
        }
        
        // Extract targets as bullet points
        $targets = [];
        if (isset($content['target'])) {
            // Split target by newline if it contains multiple lines
            $target_lines = explode("\n", $content['target']);
            foreach ($target_lines as $line) {
                if (trim($line)) {
                    $targets[] = trim($line);
                }
            }
        }
        
        // If no targets found in target field, check for other fields
        if (empty($targets) && isset($content['targets'])) {
            // Some systems might store targets as an array
            if (is_array($content['targets'])) {
                foreach ($content['targets'] as $target) {
                    $targets[] = $target;
                }
            } else {
                $targets[] = $content['targets'];
            }
        }
        
        // If still no targets, add a placeholder
        if (empty($targets)) {
            $targets[] = "No specific targets defined";
        }
        
        // Extract status details
        $status_items = [];
        
        // Add achievement if available
        if (!empty($content['achievement'])) {
            $status_items[] = $content['achievement'];
        }
        
        // Add status text if available
        if (!empty($content['status_text'])) {
            $status_lines = explode("\n", $content['status_text']);
            foreach ($status_lines as $line) {
                if (trim($line)) {
                    $status_items[] = trim($line);
                }
            }
        }
        
        // Add remarks if available
        if (!empty($content['remarks'])) {
            $status_items[] = $content['remarks'];
        }
        
        // If no status items found, add a placeholder
        if (empty($status_items)) {
            $status_items[] = "No status details provided";
        }
        
        // Map database status values to color coding
        $rating = 'green'; // Default to green (on track)
        switch ($program['status']) {
            case 'on-track-yearly':
            case 'on-track':
                $rating = 'green';
                break;
            case 'severe-delay':
            case 'delayed':
                $rating = 'red';
                break;
            case 'not-started':
            case 'minor-delay':
                $rating = 'yellow';
                break;
            case 'target-achieved':
            case 'completed':
                $rating = 'green';
                break;
        }
        
        $programs[] = [
            'name' => $program['program_name'],
            'targets' => $targets,
            'status' => $status_items,
            'rating' => $rating,
            'agency' => $program['agency_name']
        ];
    }
    
    // Create a new slide
    $slide = $presentation->createSlide();
    
    // Define standard colors
    $colorBlack = new \PhpOffice\PhpPresentation\Style\Color('FF000000');
    $colorWhite = new \PhpOffice\PhpPresentation\Style\Color('FFFFFFFF');
    $colorGreen = new \PhpOffice\PhpPresentation\Style\Color('FF92D050');
    $colorYellow = new \PhpOffice\PhpPresentation\Style\Color('FFFFFF00');
    $colorRed = new \PhpOffice\PhpPresentation\Style\Color('FFFF0000');
    $colorGray = new \PhpOffice\PhpPresentation\Style\Color('FFD9D9D9');
    
    // ===== HEADER SECTION =====
    
    // Title on left (SECTOR NAME)
    $titleShape = $slide->createRichTextShape()
        ->setHeight(60)
        ->setWidth(300)
        ->setOffsetX(50)
        ->setOffsetY(20);
    
    $titleText = $titleShape->createTextRun(strtoupper($sector['sector_name']));
    $titleText->getFont()
        ->setBold(true)
        ->setSize(24)
        ->setColor($colorBlack);
    
    // Add sector leadership info if available
    if ($leadership_text) {
        $leadershipShape = $slide->createRichTextShape()
            ->setHeight(40)
            ->setWidth(720)
            ->setOffsetX(400)
            ->setOffsetY(30);
        
        $leaderText = $leadershipShape->createTextRun($leadership_text);
        $leaderText->getFont()
            ->setSize(10)
            ->setColor($colorBlack);
    }
    
    // Quarter box on right
    $quarterShape = $slide->createRichTextShape()
        ->setHeight(60)
        ->setWidth(150)
        ->setOffsetX(1180)
        ->setOffsetY(20);
    
    // Set quarter box background to yellow
    $quarterShapeFill = $quarterShape->getFill();
    $quarterShapeFill->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
        ->setStartColor($colorYellow);
    
    // Set quarter text
    $quarterShape->getActiveParagraph()->getAlignment()
        ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpPresentation\Style\Alignment::VERTICAL_CENTER);
    
    $quarterText = $quarterShape->createTextRun("Q{$period['quarter']} {$period['year']}");
    $quarterText->getFont()
        ->setBold(true)
        ->setSize(26)
        ->setColor($colorBlack);
    
    // ===== TABLE HEADER ROW =====
    
    // Create header cells
    $headerColumns = ['Project/Programme', 'Q' . $period['quarter'] . ' ' . $period['year'] . ' Target', 'Rating', 'Q' . $period['quarter'] . ' ' . $period['year'] . ' Status'];
    $headerWidths = [310, 310, 70, 610];
    $headerX = [50, 360, 670, 740];
    $headerY = 100;
    
    foreach ($headerColumns as $index => $headerText) {
        $headerCell = $slide->createRichTextShape()
            ->setHeight(30)
            ->setWidth($headerWidths[$index])
            ->setOffsetX($headerX[$index])
            ->setOffsetY($headerY);
        
        $headerCell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
            ->setStartColor($colorGray);
        
        // Set all cell borders correctly
        $headerCell->getBorder()->setLineStyle(\PhpOffice\PhpPresentation\Style\Border::LINE_SINGLE)
                                ->setLineWidth(1)
                                ->setColor($colorBlack);
        
        $headerCell->getActiveParagraph()->getAlignment()
            ->setVertical(\PhpOffice\PhpPresentation\Style\Alignment::VERTICAL_CENTER);
            
        $text = $headerCell->createTextRun($headerText);
        $text->getFont()
            ->setBold(true)
            ->setSize(11)
            ->setColor($colorBlack);
    }
    
    // ===== PROGRAM ROWS =====
    $rowY = 130;
    $rowHeight = 90;
    
    foreach ($programs as $index => $program) {
        // Project/Programme column
        $projectCell = $slide->createRichTextShape()
            ->setHeight($rowHeight)
            ->setWidth($headerWidths[0])
            ->setOffsetX($headerX[0])
            ->setOffsetY($rowY);
        
        $projectText = $projectCell->createTextRun($program['name']);
        $projectText->getFont()
            ->setBold(true)
            ->setSize(10)
            ->setColor($colorBlack);
            
        // Target column
        $targetCell = $slide->createRichTextShape()
            ->setHeight($rowHeight)
            ->setWidth($headerWidths[1])
            ->setOffsetX($headerX[1])
            ->setOffsetY($rowY);
            
        foreach ($program['targets'] as $target) {
            $targetText = $targetCell->createTextRun('• ' . $target);
            $targetText->getFont()
                ->setSize(9)
                ->setColor($colorBlack);
            $targetCell->createBreak();
        }
        
        // Rating column (colored box)
        $ratingCell = $slide->createRichTextShape()
            ->setHeight($rowHeight)
            ->setWidth($headerWidths[2])
            ->setOffsetX($headerX[2])
            ->setOffsetY($rowY);
            
        // Set background color based on rating
        $ratingColor = $colorGreen; // Default green
        
        if ($program['rating'] === 'yellow') {
            $ratingColor = $colorYellow;
        } elseif ($program['rating'] === 'red') {
            $ratingColor = $colorRed;
        }
        
        $ratingCell->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
            ->setStartColor($ratingColor);
            
        // Status column
        $statusCell = $slide->createRichTextShape()
            ->setHeight($rowHeight)
            ->setWidth($headerWidths[3])
            ->setOffsetX($headerX[3])
            ->setOffsetY($rowY);
            
        foreach ($program['status'] as $status) {
            $statusText = $statusCell->createTextRun('• ' . $status);
            $statusText->getFont()
                ->setSize(9)
                ->setColor($colorBlack);
            $statusCell->createBreak();
        }
        
        // Move to next row
        $rowY += $rowHeight;
    }
    
    // ===== LEGEND =====
    $legendY = 700;
    
    // Legend title
    $legendShape = $slide->createRichTextShape()
        ->setHeight(20)
        ->setWidth(80)
        ->setOffsetX(80)
        ->setOffsetY($legendY);
    
    $legendText = $legendShape->createTextRun("Legend:");
    $legendText->getFont()
        ->setBold(true)
        ->setItalic(false)
        ->setSize(10)
        ->setColor($colorBlack);
    
    // Legend items with colored boxes
    $legendItems = [
        ['text' => 'Monthly target achieved, Project on track', 'color' => $colorGreen],
        ['text' => 'Miss in target but still on track for ' . $period['year'], 'color' => $colorYellow],
        ['text' => 'Severe delays', 'color' => $colorRed]
    ];
    
    $legendX = 165;
    $legendBoxWidth = 20;
    $legendTextWidth = 200;
    
    foreach ($legendItems as $item) {
        // Colored square
        $boxShape = $slide->createRichTextShape()
            ->setHeight(15)
            ->setWidth($legendBoxWidth)
            ->setOffsetX($legendX)
            ->setOffsetY($legendY);
        
        $boxShape->getFill()->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
            ->setStartColor($item['color']);
            
        // Legend text
        $textShape = $slide->createRichTextShape()
            ->setHeight(20)
            ->setWidth($legendTextWidth)
            ->setOffsetX($legendX + $legendBoxWidth + 5)
            ->setOffsetY($legendY - 2);
            
        $text = $textShape->createTextRun($item['text']);
        $text->getFont()
            ->setSize(9)
            ->setColor($colorBlack);
            
        $legendX += $legendBoxWidth + $legendTextWidth + 30;
    }
    
    // Add draft timestamp
    $timestampShape = $slide->createRichTextShape()
        ->setHeight(20)
        ->setWidth(180)
        ->setOffsetX(1050)
        ->setOffsetY($legendY);
    
    $timestampShape->getActiveParagraph()->getAlignment()
        ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_RIGHT);
    
    $timestampText = $timestampShape->createTextRun("DRAFT " . date("j M Y"));
    $timestampText->getFont()
        ->setBold(true)
        ->setSize(9)
        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FFFF0000'));
    
    return $slide;
}
?>