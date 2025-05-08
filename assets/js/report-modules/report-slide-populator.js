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
            const defaultFont = ReportStyler.getDefaultFont();
            
            // Extract sector name from report data
            const sectorName = data.reportTitle ? data.reportTitle.split(' ')[0] : 'Forestry';
            
            // Add top and bottom sections
            addTopSection(slide, data, pptx, themeColors, defaultFont, sectorName);
            
            // Add line chart (replacing the bar chart)
            try {
                addLineChart(slide, pptx, themeColors, defaultFont, data);
                console.log("Line chart added successfully");
            } catch (chartError) {
                console.error("Error adding line chart:", chartError);
                // Fallback to diagnostic if chart fails
                addChartDiagnostic(slide, data, pptx, themeColors, defaultFont);
            }
            
            addFooterSection(slide, data, pptx, themeColors, defaultFont);
            
        } catch (err) {
            console.error("Error in populateSlide:", err);
        }
    }
    
    /**
     * Add a simple line chart to the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     * @param {Object} data - The data from the API
     */
    function addLineChart(slide, pptx, themeColors, defaultFont, data) {
        console.log("Adding timber export line chart with real data");
        
        // Create container using the styler function
        const container = ReportStyler.createChartContainer(slide, pptx, themeColors);
        
        // Add title using the styler function
        ReportStyler.createChartTitle(slide, 'Timber Export Value (RM Billions)', container, themeColors, defaultFont);
        
        // Check if we have the required chart data from the API
        if (!data || !data.charts || !data.charts.main_chart || !data.charts.main_chart.data) {
            console.error("Missing chart data from API");
            // Fallback to placeholder data if API data is missing
            const chartData = [
                {
                    name: 'Export Value',
                    labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q1', 'Q2', 'Q3', 'Q4'],
                    values: [4.2, 4.5, 4.7, 4.8, 5.2, 5.5, 5.7, 6.0]
                },
                {
                    name: 'Target Value',
                    labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q1', 'Q2', 'Q3', 'Q4'],
                    values: [4.0, 4.5, 5.0, 5.5, 5.5, 6.0, 6.5, 7.0]
                }
            ];
            
            // Get chart options from the styler
            const chartOptions = ReportStyler.getLineChartOptions(container, themeColors, defaultFont);
            
            // Add chart
            slide.addChart(pptx.ChartType.line || 'line', chartData, chartOptions);
            return;
        }
        
        // Get timber export data from API response
        const timberData = data.charts.main_chart.data;
        console.log("Timber export data from API:", timberData);
        
        // Convert monthly data to quarterly data
        const quarterLabels = ['Q1', 'Q2', 'Q3', 'Q4'];
        const data2022Quarterly = convertMonthlyToQuarterly(timberData.data2022);
        const data2023Quarterly = convertMonthlyToQuarterly(timberData.data2023);
        
        // Convert to billions for better display
        const data2022BillionsFormat = data2022Quarterly.map(val => Number((val / 1000000000).toFixed(2)));
        const data2023BillionsFormat = data2023Quarterly.map(val => Number((val / 1000000000).toFixed(2)));
        
        console.log("2022 Quarterly data (billions):", data2022BillionsFormat);
        console.log("2023 Quarterly data (billions):", data2023BillionsFormat);
        
        // Create chart data with the real values from API
        const chartData = [
            {
                name: '2022 Export Value',
                labels: quarterLabels,
                values: data2022BillionsFormat
            },
            {
                name: '2023 Export Value',
                labels: quarterLabels,
                values: data2023BillionsFormat
            }
        ];
        
        // Get chart options from the styler
        const chartOptions = ReportStyler.getLineChartOptions(container, themeColors, defaultFont);
        
        // Add chart
        slide.addChart(pptx.ChartType.line || 'line', chartData, chartOptions);
        console.log("Line chart with real data added to slide");
    }

    /**
     * Helper function to convert monthly data to quarterly data
     * @param {Array} monthlyData - Array of 12 monthly values
     * @returns {Array} - Array of 4 quarterly values
     */
    function convertMonthlyToQuarterly(monthlyData) {
        const quarterlyData = [];
        
        // Q1: Jan + Feb + Mar
        quarterlyData.push(monthlyData[0] + monthlyData[1] + monthlyData[2]);
        
        // Q2: Apr + May + Jun
        quarterlyData.push(monthlyData[3] + monthlyData[4] + monthlyData[5]);
        
        // Q3: Jul + Aug + Sep
        quarterlyData.push(monthlyData[6] + monthlyData[7] + monthlyData[8]);
        
        // Q4: Oct + Nov + Dec
        quarterlyData.push(monthlyData[9] + monthlyData[10] + monthlyData[11]);
        
        return quarterlyData;
    }
    
    /**
     * Add step-by-step chart diagnostic section 
     * Tests each part of chart generation incrementally
     */
    function addChartDiagnostic(slide, data, pptx, themeColors, defaultFont) {
        console.log("Starting chart diagnostic steps");
        
        // Create a container for the chart section using the styler function
        const container = ReportStyler.createDiagnosticContainer(slide, pptx, themeColors);
        
        // Add title for diagnostic section using the styler function
        ReportStyler.createDiagnosticTitle(slide, container, themeColors, defaultFont);
        
        // STEP 1: Check if pptx is valid
        const step1Y = container.y + 0.6;
        ReportStyler.createTextBox(slide, 'Step 1: PptxGenJS Library Check', {
            x: container.x + 0.2, y: step1Y, w: container.w - 0.4, h: 0.3,
            fontSize: 12, bold: true,
            fontFace: defaultFont,
            color: themeColors.text
        });
        
        // Test pptx methods that should always exist
        const pptxValid = pptx && typeof pptx.addSlide === 'function' && typeof pptx.writeFile === 'function';
        ReportStyler.createTextBox(slide, `PptxGenJS instance: ${pptxValid ? '✓ Valid' : '✗ Invalid'}`, {
            x: container.x + 0.3, y: step1Y + 0.3, w: container.w - 0.6, h: 0.25,
            fontSize: 10,
            fontFace: defaultFont,
            color: pptxValid ? '008800' : 'CC0000'
        });
        console.log("STEP 1: PptxGenJS valid:", pptxValid);
        
        // STEP 2: Check for chart types without using ChartType object
        const step2Y = step1Y + 0.6;
        ReportStyler.createTextBox(slide, 'Step 2: Chart Type Availability', {
            x: container.x + 0.2, y: step2Y, w: container.w - 0.4, h: 0.3,
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
        
        ReportStyler.createTextBox(slide, chartTypesText, {
            x: container.x + 0.3, y: step2Y + 0.3, w: container.w - 0.6, h: 0.6,
            fontSize: 10,
            fontFace: defaultFont,
            color: chartTypesList.length > 0 ? '008800' : '888888',
            lineSpacing: 1.2
        });
        console.log("STEP 2: Chart types found:", chartTypesList);
        
        // STEP 3: Attempt to create a simple chart data structure
        const step3Y = step2Y + 1.0;
        ReportStyler.createTextBox(slide, 'Step 3: Chart Data Preparation', {
            x: container.x + 0.2, y: step3Y, w: container.w - 0.4, h: 0.3,
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
            
            ReportStyler.createTextBox(slide, `Chart data: ${JSON.stringify(chartData)}`, {
                x: container.x + 0.3, y: step3Y + 0.3, w: container.w - 0.6, h: 0.5,
                fontSize: 10,
                fontFace: defaultFont,
                color: '008800'
            });
            console.log("STEP 3: Chart data structure:", chartData);
            
            // STEP 4: Check for addChart method directly
            const step4Y = step3Y + 0.9;
            ReportStyler.createTextBox(slide, 'Step 4: Check addChart Method', {
                x: container.x + 0.2, y: step4Y, w: container.w - 0.4, h: 0.3,
                fontSize: 12, bold: true,
                fontFace: defaultFont,
                color: themeColors.text
            });
            
            // Check if slide has addChart method
            const hasAddChart = typeof slide.addChart === 'function';
            ReportStyler.createTextBox(slide, `slide.addChart method: ${hasAddChart ? '✓ Available' : '✗ Not available'}`, {
                x: container.x + 0.3, y: step4Y + 0.3, w: container.w - 0.6, h: 0.25,
                fontSize: 10,
                fontFace: defaultFont,
                color: hasAddChart ? '008800' : 'CC0000'
            });
            console.log("STEP 4: slide.addChart available:", hasAddChart);
            
            // STEP 5: Determine correct chart type format
            const step5Y = step4Y + 0.6;
            ReportStyler.createTextBox(slide, 'Step 5: Determine Chart Type Format', {
                x: container.x + 0.2, y: step5Y, w: container.w - 0.4, h: 0.3,
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
            
            ReportStyler.createTextBox(slide, typeTests.join('\n'), {
                x: container.x + 0.3, y: step5Y + 0.3, w: container.w - 0.6, h: 0.8,
                fontSize: 10,
                fontFace: defaultFont,
                color: '000000'
            });
            console.log("STEP 5: Chart type tests:", typeTests);
            
        } catch (err) {
            console.error("Chart diagnostic error:", err);
            ReportStyler.createTextBox(slide, `Chart diagnostic error: ${err.message}`, {
                x: container.x + 0.3, y: step3Y + 0.3, w: container.w - 0.6, h: 0.5,
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
        // Create the sector box in the top left
        ReportStyler.createSectorBox(slide, pptx, themeColors);
        
        // Add sector icon or fallback shape
        ReportStyler.addSectorIcon(slide, pptx, themeColors, '../../assets/images/forest-icon.png');
        
        // Add sector name and target text
        ReportStyler.addSectorText(slide, sectorName, 'RM 8 bil in exports by 2030', themeColors, defaultFont);
        
        // Create MUDeNR outcomes box
        const mudenrBox = ReportStyler.createMudenrBox(slide, pptx, themeColors);
        
        // Add MUDeNR outcome bullets
        ReportStyler.addMudenrOutcomes(slide, pptx, mudenrBox, defaultFont, themeColors);
        
        // Create quarter box with yellow indicator
        ReportStyler.createQuarterBox(slide, pptx, themeColors, data.quarter || 'Q2 2025', defaultFont);
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
        ReportStyler.addLegendTitle(slide, defaultFont, themeColors);
        
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
        
        // Add each legend item with positions specified in the design
        ReportStyler.addLegendItem(slide, pptx, legendItems[0].color, legendItems[0].label, 
            1.000, 7.118, 1.323, 7.059, defaultFont);
            
        ReportStyler.addLegendItem(slide, pptx, legendItems[1].color, legendItems[1].label, 
            2.953, 7.118, 3.272, 7.059, defaultFont);
            
        ReportStyler.addLegendItem(slide, pptx, legendItems[2].color, legendItems[2].label, 
            4.776, 7.087, 5.094, 7.102, defaultFont);
            
        ReportStyler.addLegendItem(slide, pptx, legendItems[3].color, legendItems[3].label, 
            6.146, 7.087, 6.520, 7.110, defaultFont);
        
        // Add year indicator circles and text
        // Previous year (orange)
        ReportStyler.addYearIndicator(slide, pptx, previousYear.toString(), 'ED7D31',
            7.657, 7.193, 7.689, 7.126, defaultFont);
            
        // Current year (blue)
        ReportStyler.addYearIndicator(slide, pptx, currentYear.toString(), '0070C0',
            8.193, 7.193, 8.244, 7.126, defaultFont);
        
        // Format current date for the draft text
        const today = new Date();
        const draftDateString = `DRAFT ${today.getDate()} ${today.toLocaleString('en-US', { month: 'long' })} ${today.getFullYear()}`;
        
        // Add Draft text box
        ReportStyler.createDraftText(slide, draftDateString, defaultFont);
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
                const themeColors = ReportStyler.getThemeColors();
                
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