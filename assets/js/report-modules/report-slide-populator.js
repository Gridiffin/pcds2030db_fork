/**
 * Report Slide Populator Module 
 * 
 * Handles populating slides with content from the report data
 */

const ReportPopulator = (function() {
    /**
     * Populate the slide with data from the API
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     */
    function populateSlide(slide, data, pptx, themeColors) {
        try {
            // Define common font for consistency
            const defaultFont = 'Calibri';
            
            // Extract sector name from report data
            const sectorName = data.reportTitle ? data.reportTitle.split(' ')[0] : 'Forestry';
            
            // Add top and bottom sections
            addTopSection(slide, data, pptx, themeColors, defaultFont, sectorName);
            
            // Add simple bar chart (using lowercase 'bar' as confirmed by diagnostics)
            try {
                addSimpleBarChart(slide, pptx, themeColors, defaultFont);
                console.log("Bar chart added successfully");
            } catch (chartError) {
                console.error("Error adding bar chart:", chartError);
                // Fallback to diagnostic if chart fails
                addChartDiagnostic(slide, data, pptx, themeColors, defaultFont);
            }
            
            addFooterSection(slide, data, pptx, themeColors, defaultFont);
            
        } catch (err) {
            console.error("Error in populateSlide:", err);
        }
    }
    
    /**
     * Add a simple bar chart to the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addSimpleBarChart(slide, pptx, themeColors, defaultFont) {
        console.log("Adding simple bar chart with correct format");
        
        // Create container for the chart (optional)
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 2.0, y: 1.5, w: 9.0, h: 5.0,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add title above chart
        slide.addText('Timber Export Values (RM Billions)', {
            x: 2.0, y: 1.7, w: 9.0, h: 0.4,
            fontSize: 16, bold: true,
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'center'
        });
        
        // Create chart data in the correct format
        // Format confirmed by diagnostic: array of objects with name, labels, values
        const chartData = [
            {
                name: 'Export Value',
                labels: ['2020', '2021', '2022', '2023', '2024', '2025'],
                values: [4.2, 4.8, 5.5, 6.3, 7.1, 7.8]
            }
        ];
        
        console.log("Chart data ready:", chartData);
        
        // Define minimal chart options
        const chartOptions = {
            x: 2.5, y: 2.2, w: 8.0, h: 4.0,
            barDir: 'col',                  // Column chart (vertical)
            showTitle: false,               // Title already added separately
            showValue: true,                // Show data values
            dataLabelFormatCode: '#0.0',    // Format with one decimal
            chartColors: ['375623'],        // Dark green
            chartColorsOpacity: 80,         // 80% opacity
            barGapWidthPct: 50,             // Gap between bars
            dataBorder: { pt: 1, color: '1F4E79' }, // Border around bars
            valAxisMaxVal: 10,              // Y-axis max
            valAxisMinVal: 0,               // Y-axis min
            valAxisMajorUnit: 2,            // Y-axis interval
            catAxisLabelFontSize: 10,       // X-axis label size
            valAxisLabelFontSize: 10,       // Y-axis label size
            valAxisLabelFontFace: defaultFont,
            catAxisLabelFontFace: defaultFont
        };
        
        console.log("Chart options ready:", chartOptions);
        
        // Add chart using the correct format (lowercase 'bar')
        slide.addChart(pptx.ChartType.bar, chartData, chartOptions);
        console.log("Chart added to slide");
    }
    
    /**
     * Add step-by-step chart diagnostic section 
     * Tests each part of chart generation incrementally
     */
    function addChartDiagnostic(slide, data, pptx, themeColors, defaultFont) {
        console.log("Starting chart diagnostic steps");
        
        // Create a container for the chart section
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 2.0, y: 1.5, w: 9.0, h: 5.0,
            fill: { color: 'F9F9F9' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add title for diagnostic section
        slide.addText('Chart Diagnostic Steps', {
            x: 2.1, y: 1.6, w: 8.8, h: 0.4,
            fontSize: 16, bold: true,
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'center'
        });
        
        // STEP 1: Check if pptx is valid
        const step1Y = 2.1;
        slide.addText('Step 1: PptxGenJS Library Check', {
            x: 2.2, y: step1Y, w: 8.6, h: 0.3,
            fontSize: 12, bold: true,
            fontFace: defaultFont,
            color: themeColors.text
        });
        
        // Test pptx methods that should always exist
        const pptxValid = pptx && typeof pptx.addSlide === 'function' && typeof pptx.writeFile === 'function';
        slide.addText(`PptxGenJS instance: ${pptxValid ? '✓ Valid' : '✗ Invalid'}`, {
            x: 2.3, y: step1Y + 0.3, w: 8.4, h: 0.25,
            fontSize: 10,
            fontFace: defaultFont,
            color: pptxValid ? '008800' : 'CC0000'
        });
        console.log("STEP 1: PptxGenJS valid:", pptxValid);
        
        // STEP 2: Check for chart types without using ChartType object
        const step2Y = step1Y + 0.6;
        slide.addText('Step 2: Chart Type Availability', {
            x: 2.2, y: step2Y, w: 8.6, h: 0.3,
            fontSize: 12, bold: true,
            fontFace: defaultFont, 
            color: themeColors.text
        });
        
        // Examine pptx directly instead of using ChartType
        let chartTypesText = '';
        let chartTypesList = [];
        
        try {
            // Look for any properties that might be chart types
            for (const prop in pptx) {
                if (prop.toLowerCase().includes('chart')) {
                    chartTypesList.push(`${prop}`);
                }
            }
            
            chartTypesText = chartTypesList.length > 0 ? 
                `Found ${chartTypesList.length} chart-related properties: ${chartTypesList.join(', ')}` : 
                'No chart-related properties found directly on pptx object';
            
        } catch (e) {
            chartTypesText = `Error examining chart types: ${e.message}`;
        }
        
        slide.addText(chartTypesText, {
            x: 2.3, y: step2Y + 0.3, w: 8.4, h: 0.6,
            fontSize: 10,
            fontFace: defaultFont,
            color: chartTypesList.length > 0 ? '008800' : '888888',
            lineSpacing: 1.2
        });
        console.log("STEP 2: Chart types found:", chartTypesList);
        
        // STEP 3: Attempt to create a simple chart data structure
        const step3Y = step2Y + 1.0;
        slide.addText('Step 3: Chart Data Preparation', {
            x: 2.2, y: step3Y, w: 8.6, h: 0.3,
            fontSize: 12, bold: true,
            fontFace: defaultFont,
            color: themeColors.text
        });
        
        try {
            // Create a very simple data structure for chart
            const chartData = [
                {
                    name: 'Sample Data',
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                    values: [10, 20, 30, 20, 15]
                }
            ];
            
            slide.addText(`Chart data: ${JSON.stringify(chartData)}`, {
                x: 2.3, y: step3Y + 0.3, w: 8.4, h: 0.5,
                fontSize: 10,
                fontFace: defaultFont,
                color: '008800'
            });
            console.log("STEP 3: Chart data structure:", chartData);
            
            // STEP 4: Check for addChart method directly
            const step4Y = step3Y + 0.9;
            slide.addText('Step 4: Check addChart Method', {
                x: 2.2, y: step4Y, w: 8.6, h: 0.3,
                fontSize: 12, bold: true,
                fontFace: defaultFont,
                color: themeColors.text
            });
            
            // Check if slide has addChart method
            const hasAddChart = typeof slide.addChart === 'function';
            slide.addText(`slide.addChart method: ${hasAddChart ? '✓ Available' : '✗ Not available'}`, {
                x: 2.3, y: step4Y + 0.3, w: 8.4, h: 0.25,
                fontSize: 10,
                fontFace: defaultFont,
                color: hasAddChart ? '008800' : 'CC0000'
            });
            console.log("STEP 4: slide.addChart available:", hasAddChart);
            
            // STEP 5: Determine correct chart type format
            const step5Y = step4Y + 0.6;
            slide.addText('Step 5: Determine Chart Type Format', {
                x: 2.2, y: step5Y, w: 8.6, h: 0.3,
                fontSize: 12, bold: true,
                fontFace: defaultFont,
                color: themeColors.text
            });
            
            // Test different possible chart type formats
            const typeTests = [];
            
            if (typeof pptx.ChartType === 'object') {
                typeTests.push("pptx.ChartType is an object");
                
                if (pptx.ChartType.BAR) typeTests.push("pptx.ChartType.BAR is available");
                if (pptx.ChartType.bar) typeTests.push("pptx.ChartType.bar is available");
            } else {
                typeTests.push("pptx.ChartType is not an object");
                
                // Look for direct string values that might work
                if (typeof pptx.charts === 'object') {
                    typeTests.push("pptx.charts object exists");
                }
                
                // Common chart types in various formats
                const possibleTypes = ['bar', 'column', 'pie', 'line', 'area', 'scatter'];
                typeTests.push(`Will try direct string values: ${possibleTypes.join(', ')}`);
            }
            
            slide.addText(typeTests.join('\n'), {
                x: 2.3, y: step5Y + 0.3, w: 8.4, h: 0.8,
                fontSize: 10,
                fontFace: defaultFont,
                color: '000000'
            });
            console.log("STEP 5: Chart type tests:", typeTests);
            
        } catch (err) {
            console.error("Chart diagnostic error:", err);
            slide.addText(`Chart diagnostic error: ${err.message}`, {
                x: 2.3, y: step3Y + 0.3, w: 8.4, h: 0.5,
                fontSize: 10,
                fontFace: defaultFont,
                color: 'CC0000'
            });
        }
    }

    /**
     * Add the top section of the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     * @param {string} sectorName - The sector name
     */
    function addTopSection(slide, data, pptx, themeColors, defaultFont, sectorName) {
        // Add the sector box with border - left side top
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 0.22, y: 0.08, w: 3.06, h: 0.63,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        try {
            // Add forest icon
            slide.addImage({
                path: '../../assets/images/forest-icon.png', 
                x: 0.26, y: 0.13, w: 0.57, h: 0.57
            });
        } catch (e) {
            console.warn('Forest icon not found, skipping icon', e);
            
            // Fallback: Add a colored shape instead of the image
            slide.addShape(pptx.shapes.RECTANGLE, {
                x: 0.26, y: 0.13, w: 0.57, h: 0.57,
                fill: { color: themeColors.secondary },
                line: { color: themeColors.primary, width: 0.75 }
            });
        }
        
        // Add sector name text - centered, bold, and black
        slide.addText(sectorName, {
            x: 0.79, y: 0.11, w: 2.44, h: 0.41,
            fontSize: 18, bold: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center',
            valign: 'middle'
        });
        
        // Add export target text below sector name
        slide.addText('RM 8 bil in exports by 2030', {
            x: 0.78, y: 0.39, w: 2.51, h: 0.29,
            fontSize: 10.5, italic: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center'
        });
        
        // Add larger box for MUDeNR outcomes
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 3.28, y: 0.08, w: 9.83, h: 0.63,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add MUDeNR Outcomes text box 1
        slide.addText([
            { text: 'MUDeNR Outcome:', options: { bold: true, fontSize: 8, fontFace: defaultFont } },
            { text: '\nIncrease Timber & Non Wood Forest Products Exports Earnings', options: { 
                fontSize: 8, 
                fontFace: defaultFont,
                bullet: { type: 'number', numberFormat: '%d.' } 
            }},
            { text: 'Community-Based Ecotourism and conservation Totally Protected Area', options: { 
                fontSize: 8, 
                fontFace: defaultFont,
                bullet: { type: 'number', numberFormat: '%d.' } 
            }},
            { text: '\nCertify Long Term Forest License Area and Forest Plantation', options: { 
                fontSize: 8, 
                fontFace: defaultFont,
                bullet: { type: 'number', numberFormat: '%d.' } 
            }}
        ], {
            x: 3.35, y: 0.05, w: 3.56, h: 0.64,
            fontSize: 8,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'left',
            valign: 'top',
            paraSpaceBefore: 0,
            paraSpaceAfter: 0,
            lineSpacingMultiple: 0.9
        });
        
        // Add MUDeNR Outcomes text box 2
        slide.addText([
            { text: '200,000 ha degraded area (100%) planted/restored by 2030', options: { 
                fontSize: 8, 
                fontFace: defaultFont,
                bullet: { type: 'number', startAt: 4, numberFormat: '%d.' } 
            }},
            { text: '\nObtain world recognition for sustainable management practices and conservation effort', options: { 
                fontSize: 8, 
                fontFace: defaultFont,
                bullet: { type: 'number', startAt: 5, numberFormat: '%d.' } 
            }}
        ], {
            x: 6.87, y: 0.06, w: 3.56, h: 0.64,
            fontSize: 8,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'left',
            valign: 'top',
            paraSpaceBefore: 0,
            paraSpaceAfter: 0,
            lineSpacingMultiple: 0.9
        });
        
        // Add quarter section box
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 10.58, y: 0.08, w: 1.87, h: 0.63,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add yellow square next to the quarter box with no gap
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 12.45, y: 0.08, w: 0.66, h: 0.63,
            fill: { color: 'FFFF00' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add quarter information in the box
        slide.addText(data.quarter || 'Q2 2025', { 
            x: 10.58, y: 0.08, w: 1.87, h: 0.63,
            fontSize: 14, bold: true, 
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center',
            valign: 'middle'
        });
    }

    /**
     * Add footer section with legend to the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addFooterSection(slide, data, pptx, themeColors, defaultFont) {
        // Add "Legend:" text
        slide.addText('Legend:', {
            x: 0.2, y: 7.14, w: 0.69, h: 0.25,
            fontSize: 9, italic: true,
            fontFace: defaultFont,
            color: themeColors.lightText,
            align: 'left',
            valign: 'middle'
        });
        
        // Define the current year for the yellow box description
        const currentYear = new Date().getFullYear();
        const previousYear = currentYear - 1;
        
        // Define legend items with exact colors and text as specified
        const legendItems = [
            { color: '92D050', label: 'Monthly target achieved, Project on track' },
            { color: 'FFFF00', label: `Miss in target but still on track for ${currentYear}` },
            { color: 'FF0000', label: 'Severe delays' },
            { color: '757070', label: 'Not started' }
        ];
        
        // Add legend squares and text with specified positions
        // Square 1 (Green)
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 1.000,
            y: 7.118,
            w: 0.31, h: 0.31,
            fill: { color: legendItems[0].color },
            line: { color: '000000', width: 0.5 }
        });
        
        // Text 1
        slide.addText(legendItems[0].label, {
            x: 1.323,
            y: 7.059,
            w: 1.5748,
            h: 0.4055,
            fontSize: 9,
            italic: true,
            fontFace: defaultFont,
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Square 2 (Yellow)
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 2.953,
            y: 7.118,
            w: 0.31, h: 0.31,
            fill: { color: legendItems[1].color },
            line: { color: '000000', width: 0.5 }
        });
        
        // Text 2
        slide.addText(legendItems[1].label, {
            x: 3.272,
            y: 7.059,
            w: 1.5748,
            h: 0.4055,
            fontSize: 9,
            italic: true,
            fontFace: defaultFont,
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Square 3 (Red)
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 4.776,
            y: 7.087,
            w: 0.31, h: 0.31,
            fill: { color: legendItems[2].color },
            line: { color: '000000', width: 0.5 }
        });
        
        // Text 3
        slide.addText(legendItems[2].label, {
            x: 5.094,
            y: 7.102,
            w: 1.5748,
            h: 0.4055,
            fontSize: 9,
            italic: true,
            fontFace: defaultFont,
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Square 4 (Grey)
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 6.146,
            y: 7.087,
            w: 0.31, h: 0.31,
            fill: { color: legendItems[3].color },
            line: { color: '000000', width: 0.5 }
        });
        
        // Text 4
        slide.addText(legendItems[3].label, {
            x: 6.520,
            y: 7.110,
            w: 1.5748,
            h: 0.4055,
            fontSize: 9,
            italic: true,
            fontFace: defaultFont,
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Add year indicator circles and text
        // Circle 1 - Previous Year (orange)
        slide.addShape(pptx.shapes.OVAL, {
            x: 7.657,
            y: 7.193,
            w: 0.118,
            h: 0.118,
            fill: { color: 'ED7D31' },
            line: { color: 'ED7D31', width: 0.5 }
        });
        
        // Text 1 - Previous Year
        slide.addText(`${previousYear}`, {
            x: 7.689,
            y: 7.126,
            w: 0.642,
            h: 0.252,
            fontSize: 8,
            fontFace: defaultFont,
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Circle 2 - Current Year (blue)
        slide.addShape(pptx.shapes.OVAL, {
            x: 8.193,
            y: 7.193,
            w: 0.118,
            h: 0.118,
            fill: { color: '0070C0' },
            line: { color: '0070C0', width: 0.5 }
        });
        
        // Text 2 - Current Year
        slide.addText(`${currentYear}`, {
            x: 8.244,
            y: 7.126,
            w: 0.642,
            h: 0.252,
            fontSize: 8,
            fontFace: defaultFont,
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Format current date for the draft text
        const today = new Date();
        const draftDateString = `DRAFT ${today.getDate()} ${today.toLocaleString('en-US', { month: 'long' })} ${today.getFullYear()}`;
        
        // Add Draft text box with red text, bold
        slide.addText(draftDateString, {
            x: 9.197,
            y: 7.098,
            w: 2.150,
            h: 0.339,
            fontSize: 14, 
            bold: true,
            fontFace: defaultFont,
            color: 'FF0000',
            align: 'left',
            valign: 'middle'
        });
    }

    /**
     * Generate the PPTX presentation using PptxGenJS
     * @param {Object} data - The data from the API
     * @returns {Promise<Blob>} - A promise that resolves to a Blob containing the PPTX file
     */
    function generatePresentation(data, statusMessage) {
        return new Promise((resolve, reject) => {
            try {
                // Update status if available
                if (statusMessage) statusMessage.textContent = 'Creating presentation...';
                
                // Create a new presentation
                const pptx = new PptxGenJS();
                
                // Set slide size to widescreen 16:9
                pptx.layout = 'LAYOUT_WIDE';
                
                // Define theme colors
                const themeColors = {
                    primary: '1F4E79',     // Darker blue for primary elements
                    secondary: '375623',   // Dark green for secondary elements  
                    accent1: 'C55A11',     // Dark orange for accent
                    accent2: '2E75B6',     // Mid blue for accent
                    text: '000000',        // Black for main text
                    lightText: '444444',   // Dark grey for secondary text
                    headerBg: 'E9EDF1',    // Light blue-grey for section headers
                    greenStatus: '70AD47', // Green for on-track status
                    yellowStatus: 'FFC000', // Amber for minor issues
                    redStatus: 'C00000',   // Deep red for major issues
                    greyStatus: 'A5A5A5'   // Grey for no data
                };
                
                // Create a slide without using master slides
                const slide = pptx.addSlide();
                
                // Populate slide with top and bottom sections
                populateSlide(slide, data, pptx, themeColors);
                
                // Get PPTX as blob
                if (statusMessage) statusMessage.textContent = 'Finalizing presentation...';
                
                pptx.writeFile('forestry-report')
                    .then(() => {
                        // Return empty blob to avoid errors
                        const emptyBlob = new Blob(['success'], { type: 'application/octet-stream' });
                        resolve(emptyBlob);
                    })
                    .catch(error => {
                        console.error('Error in writeFile:', error);
                        reject(new Error('Error generating PPTX: ' + error.message));
                    });
                
            } catch (error) {
                console.error('Presentation generation error:', error);
                reject(new Error('Error in presentation generation: ' + error.message));
            }
        });
    }

    // Expose public methods
    return {
        populateSlide,
        generatePresentation
    };
})();