/**
 * Report Generator Service using PptxGenJS
 * 
 * This microservice handles PowerPoint report generation for the PCDS2030 Dashboard.
 */
const express = require('express');
const cors = require('cors');
const morgan = require('morgan');
const multer = require('multer');
const fs = require('fs');
const path = require('path');
const pptxgen = require('pptxgenjs');

// Load environment variables from .env file in development
try {
    if (fs.existsSync(path.join(__dirname, '.env'))) {
        require('dotenv').config();
    }
} catch (err) {
    console.log('No .env file found, using default environment variables');
}

// Initialize Express app
const app = express();
const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0'; // Use 0.0.0.0 to listen on all network interfaces
const NODE_ENV = process.env.NODE_ENV || 'development';

// Configure middleware
app.use(cors());
app.use(morgan(NODE_ENV === 'production' ? 'combined' : 'dev'));
app.use(express.json({ limit: '50mb' })); // Increase limit for large presentations
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Setup uploads and temp directories based on environment
let uploadsDir, tempDir;

if (NODE_ENV === 'production' && process.env.STORAGE_PATH) {
    // In production, use the storage path provided by the hosting service
    const storagePath = process.env.STORAGE_PATH;
    uploadsDir = path.join(storagePath, 'uploads');
    tempDir = path.join(storagePath, 'temp');
} else {
    // For development or if no storage path specified
    uploadsDir = path.join(__dirname, 'uploads');
    tempDir = path.join(__dirname, 'temp');
}

// Create directories if they don't exist
for (const dir of [uploadsDir, tempDir]) {
    if (!fs.existsSync(dir)) {
        try {
            fs.mkdirSync(dir, { recursive: true });
            console.log(`Created directory: ${dir}`);
        } catch (err) {
            console.error(`Error creating directory ${dir}:`, err);
        }
    }
}

// Configure multer for file uploads
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, uploadsDir);
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + '-' + file.originalname);
    }
});
const upload = multer({ 
    storage: storage,
    limits: { fileSize: 25 * 1024 * 1024 } // 25MB file size limit
});

// Add basic health check endpoint
app.get('/health', (req, res) => {
    res.status(200).json({
        status: 'up',
        timestamp: new Date().toISOString(),
        environment: NODE_ENV
    });
});

/**
 * Generate report endpoint
 * 
 * Accepts period data and generates a PowerPoint report
 */
app.post('/generate-report', async (req, res) => {
    try {
        console.log('Received request to generate report');
        
        const { periodId, period, sectors } = req.body;
        
        if (!periodId || !period || !sectors) {
            return res.status(400).json({ 
                success: false, 
                error: 'Missing required data: periodId, period, and sectors are required' 
            });
        }
        
        console.log(`Generating report for period ID: ${periodId}, Quarter: ${period.quarter}, Year: ${period.year}`);
        
        // Generate the PPTX report
        const pptxPath = await generatePptxReport(periodId, period, sectors);
        
        // Send the path back to the client
        res.json({
            success: true,
            message: 'Report generated successfully',
            pptxPath: path.basename(pptxPath)
        });
    } catch (error) {
        console.error('Error generating report:', error);
        res.status(500).json({
            success: false,
            error: 'Failed to generate report: ' + error.message
        });
    }
});

/**
 * Download report endpoint
 * 
 * Returns the generated PowerPoint file
 */
app.get('/download/:filename', (req, res) => {
    const filename = req.params.filename;
    const filePath = path.join(tempDir, filename);
    
    if (!fs.existsSync(filePath)) {
        return res.status(404).json({ 
            success: false, 
            error: 'File not found' 
        });
    }
    
    res.download(filePath, filename, (err) => {
        if (err) {
            console.error(`Error downloading file ${filename}:`, err);
            // Don't send another response if headers already sent
            if (!res.headersSent) {
                res.status(500).json({
                    success: false,
                    error: 'Error downloading file'
                });
            }
        }
        
        // Optionally clean up old files (files older than 24 hours)
        cleanupOldFiles();
    });
});

/**
 * Template upload endpoint
 * 
 * Allows uploading a template PowerPoint file
 */
app.post('/upload-template', upload.single('template'), (req, res) => {
    if (!req.file) {
        return res.status(400).json({ 
            success: false, 
            error: 'No file uploaded' 
        });
    }
    
    res.json({
        success: true,
        message: 'Template uploaded successfully',
        filename: req.file.filename
    });
});

/**
 * Generate a PowerPoint report using PptxGenJS
 * 
 * @param {number} periodId - The reporting period ID
 * @param {object} period - The period info (quarter, year, dates)
 * @param {array} sectors - Array of sector data with programs
 * @returns {string} - Path to the generated file
 */
async function generatePptxReport(periodId, period, sectors) {
    // Create a new presentation
    const pptx = new pptxgen();
    
    // Set document properties
    pptx.author = 'PCDS 2030 Dashboard';
    pptx.company = 'PCDS 2030';
    pptx.revision = '1';
    pptx.subject = `Q${period.quarter}-${period.year} Performance Report`;
    pptx.title = `Q${period.quarter}-${period.year} Performance Report`;
    
    // Define the sector dashboard master slide
    pptx.defineSlideMaster({
        title: 'SECTOR_DASHBOARD',
        background: { color: 'FFFFFF' },
        margin: [0.5, 0.5, 0.5, 0.5],
        objects: [
            // Quarter indicator (top right)
            { 
                shape: pptx.ShapeType.rectangle,
                x: 10.7, y: 0.2, w: 1.5, h: 0.6,
                fill: { color: 'FFFF00' }
            },
            // Quarter text placeholder
            { 
                placeholder: {
                    name: 'quarterLabel',
                    type: 'title',
                    x: 10.7, y: 0.2, w: 1.5, h: 0.6,
                    align: 'center',
                    valign: 'middle'
                }
            },
            // Sector title placeholder
            { 
                placeholder: {
                    name: 'sectorTitle',
                    type: 'title', 
                    x: 0.5, y: 0.2, w: 5, h: 0.6,
                    fontFace: 'Arial',
                    fontSize: 24,
                    bold: true
                }
            },
            // Leadership text placeholder
            { 
                placeholder: {
                    name: 'leadership',
                    type: 'body',
                    x: 4, y: 0.3, w: 6, h: 0.4,
                    fontFace: 'Arial',
                    fontSize: 10
                }
            },
            // Legend
            { 
                text: 'Legend:',
                x: 0.8, y: 7, 
                fontFace: 'Arial',
                fontSize: 10,
                bold: true
            },
            // Green box
            { 
                shape: pptx.ShapeType.rectangle,
                x: 1.6, y: 7, w: 0.2, h: 0.15,
                fill: { color: '92D050' }
            },
            // Green text
            { 
                text: 'Monthly target achieved, Project on track',
                x: 1.9, y: 7,
                fontFace: 'Arial',
                fontSize: 9
            },
            // Yellow box
            { 
                shape: pptx.ShapeType.rectangle,
                x: 4.6, y: 7, w: 0.2, h: 0.15,
                fill: { color: 'FFFF00' }
            },
            // Yellow text
            { 
                text: 'Miss in target but still on track for the year',
                x: 4.9, y: 7,
                fontFace: 'Arial',
                fontSize: 9
            },
            // Red box
            { 
                shape: pptx.ShapeType.rectangle,
                x: 7.6, y: 7, w: 0.2, h: 0.15,
                fill: { color: 'FF0000' }
            },
            // Red text
            { 
                text: 'Severe delays',
                x: 7.9, y: 7,
                fontFace: 'Arial',
                fontSize: 9
            },
            // Draft timestamp
            { 
                placeholder: {
                    name: 'timestamp',
                    type: 'body',
                    x: 10.0, y: 7, w: 2, h: 0.2,
                    align: 'right',
                    fontFace: 'Arial',
                    fontSize: 9,
                    bold: true,
                    color: 'FF0000'
                }
            }
        ]
    });
    
    // Process each sector
    for (const sector of sectors) {
        try {
            // Create slide from master
            const slide = pptx.addSlide({ masterName: 'SECTOR_DASHBOARD' });
            
            // Add placeholders content
            slide.addText(`Q${period.quarter} ${period.year}`, { placeholder: 'quarterLabel', fontSize: 26, bold: true });
            slide.addText(sector.sector_name.toUpperCase(), { placeholder: 'sectorTitle' });
            
            // Add leadership info if available
            if (sector.leadership) {
                slide.addText(sector.leadership, { placeholder: 'leadership' });
            }
            
            // Add timestamp
            slide.addText(`DRAFT ${new Date().toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })}`, 
                { placeholder: 'timestamp', color: 'FF0000' });
            
            // Create table headers
            const headers = [
                `Project/Programme`,
                `Q${period.quarter} ${period.year} Target`,
                `Rating`,
                `Q${period.quarter} ${period.year} Status`
            ];
            
            // Table column widths (relative)
            const colW = [3.1, 3.1, 0.7, 5.1];
            
            // Calculate table data
            const tableData = [];
            tableData.push(headers);
            
            // Add programs to table
            if (sector.programs && sector.programs.length > 0) {
                for (const program of sector.programs) {
                    // Format targets as bullet points
                    let targetText = '';
                    if (program.targets && program.targets.length > 0) {
                        targetText = program.targets.map(t => `• ${t}`).join('\n');
                    } else {
                        targetText = '• No specific targets defined';
                    }
                    
                    // Format status as bullet points
                    let statusText = '';
                    if (program.status && program.status.length > 0) {
                        statusText = program.status.map(s => `• ${s}`).join('\n');
                    } else {
                        statusText = '• No status details provided';
                    }
                    
                    // Rating color (blank cell, will color it after)
                    const ratingCell = '';
                    
                    tableData.push([
                        program.name,
                        targetText,
                        ratingCell,
                        statusText
                    ]);
                }
            } else {
                tableData.push([
                    'No programs found for this sector',
                    'N/A',
                    '',
                    'N/A'
                ]);
            }
            
            // Add table to slide
            const table = slide.addTable(tableData, {
                x: 0.5,
                y: 1.0,
                w: 12,
                colW: colW,
                border: { type: 'solid', color: '000000', pt: 1 },
                autoPage: false
            });
            
            // Format header row
            table.rows[0].height = 0.3;
            table.rows[0].fill = { color: 'D9D9D9' };
            for (let i = 0; i < headers.length; i++) {
                table.rows[0].cells[i].fontFace = 'Arial';
                table.rows[0].cells[i].fontSize = 11;
                table.rows[0].cells[i].bold = true;
                table.rows[0].cells[i].valign = 'middle';
            }
            
            // Format data rows and add colors to rating cells
            if (sector.programs && sector.programs.length > 0) {
                for (let i = 0; i < sector.programs.length; i++) {
                    const rowIdx = i + 1;
                    const program = sector.programs[i];
                    
                    // Set row height
                    table.rows[rowIdx].height = 0.9;
                    
                    // Format program name
                    table.rows[rowIdx].cells[0].fontFace = 'Arial';
                    table.rows[rowIdx].cells[0].fontSize = 10;
                    table.rows[rowIdx].cells[0].bold = true;
                    
                    // Format targets cell
                    table.rows[rowIdx].cells[1].fontFace = 'Arial';
                    table.rows[rowIdx].cells[1].fontSize = 9;
                    
                    // Set rating color
                    let ratingColor = '92D050'; // Default green
                    if (program.rating === 'yellow') {
                        ratingColor = 'FFFF00';
                    } else if (program.rating === 'red') {
                        ratingColor = 'FF0000';
                    }
                    table.rows[rowIdx].cells[2].fill = { color: ratingColor };
                    
                    // Format status cell
                    table.rows[rowIdx].cells[3].fontFace = 'Arial';
                    table.rows[rowIdx].cells[3].fontSize = 9;
                }
            }
        } catch (error) {
            console.error(`Error processing sector ${sector.sector_name || 'unknown'}:`, error);
            // Continue with other sectors instead of failing the entire report
        }
    }
    
    // Calculate output filename and path
    const timestamp = Date.now();
    const filename = `report_q${period.quarter}_${period.year}_${timestamp}.pptx`;
    const outputPath = path.join(tempDir, filename);
    
    // Save the presentation
    await pptx.writeFile({ fileName: outputPath });
    console.log(`PowerPoint file created: ${outputPath}`);
    
    return outputPath;
}

/**
 * Clean up old files (files older than 24 hours)
 */
function cleanupOldFiles() {
    try {
        const MAX_AGE = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
        const now = Date.now();
        
        const cleanDir = (dirPath) => {
            if (!fs.existsSync(dirPath)) return;
            
            fs.readdir(dirPath, (err, files) => {
                if (err) {
                    console.error(`Error reading directory ${dirPath}:`, err);
                    return;
                }
                
                files.forEach(file => {
                    const filePath = path.join(dirPath, file);
                    fs.stat(filePath, (err, stats) => {
                        if (err) {
                            console.error(`Error getting stats for file ${filePath}:`, err);
                            return;
                        }
                        
                        if (stats.isFile() && now - stats.mtimeMs > MAX_AGE) {
                            fs.unlink(filePath, err => {
                                if (err) {
                                    console.error(`Error deleting file ${filePath}:`, err);
                                } else {
                                    console.log(`Deleted old file: ${filePath}`);
                                }
                            });
                        }
                    });
                });
            });
        };
        
        // Clean both directories
        cleanDir(tempDir);
        cleanDir(uploadsDir);
    } catch (error) {
        console.error('Error in cleanup routine:', error);
    }
}

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM received, shutting down gracefully');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('SIGINT received, shutting down gracefully');
    process.exit(0);
});

// Handle uncaught exceptions and unhandled rejections
process.on('uncaughtException', (error) => {
    console.error('Uncaught Exception:', error);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('Unhandled Rejection at:', promise, 'reason:', reason);
});

// Start the server
app.listen(PORT, HOST, () => {
    console.log(`Report generator service running on http://${HOST}:${PORT} in ${NODE_ENV} mode`);
    console.log(`Uploads directory: ${uploadsDir}`);
    console.log(`Temp directory: ${tempDir}`);
});