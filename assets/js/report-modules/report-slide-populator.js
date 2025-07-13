/**
 * Report Slide Populator Module 
 * 
 * Handles populating slides with content from the report data
 */

// Prevent multiple instantiations
if (typeof window.ReportPopulator !== 'undefined') {
    console.log('ReportPopulator module already loaded, skipping redeclaration');
} else {
    window.ReportPopulator = (function() {
    /**
     * Populate the slide with data from the API
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     */    function populateSlide(slide, data, pptx, themeColors) {
        try {
            // Define common font for consistency
            const defaultFont = ReportStyler.getDefaultFont();
            
            // Extract sector name from report data
            const sectorName = data.reportTitle ? String(data.reportTitle).split(' ')[0] : 'Forestry';
            console.log('Sector name being used:', sectorName, 'Type:', typeof sectorName);
            
            // Add top and bottom sections
            addTopSection(slide, data, pptx, themeColors, defaultFont, sectorName);
            
            // Add program data table on the left side
            addProgramDataTable(slide, data, pptx, themeColors, defaultFont);
            
            // Add line chart
            try {
                ReportStyler.addTimberExportChart(slide, pptx, themeColors, defaultFont, data);
            } catch (chartError) {
                // Add error text instead of chart
                const container = ReportStyler.createChartContainer(slide, pptx, themeColors);
                ReportStyler.createTextBox(slide, 'Error generating chart. Please check the data and try again.', {
                    x: container.x + 0.5, 
                    y: container.y + 2.0,
                    w: container.w - 1.0, 
                    h: 1.0,
                    fontSize: 14, 
                    fontFace: defaultFont,
                    color: 'CC0000',
                    align: 'center',
                    bold: true
                });
            }
            
            // Add KPI boxes with outcomes_details data
            addKpiBoxes(slide, data, pptx, themeColors, defaultFont);
            addFooterSection(slide, data, pptx, themeColors, defaultFont);
            
            // Add the new Total Degraded Area Chart if data is available
            if (data && data.charts && data.charts.degraded_area_chart) {
                console.log("Attempting to add Total Degraded Area chart.");
                
                // Position the Total Degraded Area chart with adjusted position for better placement in slide
                // Converting from cm to inches (1 inch = 2.54 cm)
                const degradedAreaChartPosition = {
                    x: 23.24 / 2.54, // 23.24cm from left (shifted more into the slide)
                    y: 12.59 / 2.54, // 12.59cm from top 
                    w: 4.2, // Width in inches - slightly increased
                    h: 2.4  // Height in inches - maintained for proper spacing
                };
                
                // Use the new ReportStyler function to add the chart with improved styling
                ReportStyler.addTotalDegradedAreaChart(slide, pptx, themeColors, defaultFont, data.charts.degraded_area_chart, degradedAreaChartPosition);
            } else {
                console.warn("No data found for Total Degraded Area chart.");
            }
            
        } catch (err) {
            console.error("Error in populateSlide:", err);
        }
    }    /**
     * Add KPI boxes with data from outcomes_details
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addKpiBoxes(slide, data, pptx, themeColors, defaultFont) {
        console.log("Adding KPI boxes with outcomes_details data");        if (data && data.outcomes_details && data.outcomes_details.length > 0) {
            console.log("Using outcomes_details data for KPIs:", data.outcomes_details);

            data.outcomes_details.forEach((kpi, index) => {
                if (index < 3) { // Ensure we only process up to 3 KPIs
                    try {
                        const detailJson = JSON.parse(kpi.detail_json);
                        
                        // Check if this is a legacy format (without layout_type)
                        // If so, adapt it to a format our new renderer can handle
                        if (!detailJson.layout_type) {
                            // Convert legacy format to new format
                            console.log(`Converting legacy format for KPI: ${kpi.name}`);
                            
                            // Determine if this contains multiple values (indicated by ; in value or description)
                            const hasMultipleValues = 
                                (detailJson.value && detailJson.value.includes(';')) || 
                                (detailJson.description && detailJson.description.includes(';'));
                            
                            if (hasMultipleValues) {
                                // Split values and descriptions at semicolons
                                const values = detailJson.value ? detailJson.value.split(';') : ['N/A'];
                                const descriptions = detailJson.description ? detailJson.description.split(';') : [''];
                                
                                // Create items array
                                const items = values.map((val, idx) => ({
                                    value: val.trim(),
                                    description: descriptions[idx] ? descriptions[idx].trim() : ''
                                }));
                                
                                // Use detailed_list layout for multiple items
                                detailJson.layout_type = 'detailed_list';
                                detailJson.items = items;
                            } else {
                                // Use simple layout for single value/description
                                detailJson.layout_type = 'simple';
                                detailJson.items = [{
                                    value: detailJson.value || 'N/A',
                                    description: detailJson.description || ''
                                }];
                            }
                        }
                        
                        // Call a generic KPI box creation function from ReportStyler
                        // The ReportStyler will handle layout based on detailJson.layout_type and detailJson.items
                        ReportStyler.createKpiBox(slide, pptx, themeColors, defaultFont, kpi.name, detailJson, index);
                        console.log(`Added KPI box ${index + 1} for:`, kpi.name);
                    } catch (e) {
                        console.error("Error parsing detail_json for KPI:", kpi.name, e);
                        // Optionally, add a placeholder or error message on the slide
                        ReportStyler.createErrorKpiBox(slide, pptx, themeColors, defaultFont, `Error: KPI ${index + 1} data invalid`, index);
                    }
                }
            });
        } else {
            console.warn("No KPI data available in outcomes_details. No fallback available.");
        }
    }

    /**
     * Add program data table to the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addProgramDataTable(slide, data, pptx, themeColors, defaultFont) {
        let currentQuarter = (data && data.quarter) ? data.quarter : '';
        // Always use data.programs as the source for the PPTX table
        let programs = [];
        if (data && data.programs && Array.isArray(data.programs)) {
            programs = data.programs.map(program => {
                let target = 'N/A';
                if (program.targets && program.targets.length > 0) {
                    target = program.targets.map(t => t.target_description || '').join('\n');
                }
                let statusText = 'N/A';
                if (program.targets && program.targets.length > 0) {
                    statusText = program.targets.map(t => t.status_description || '').join('\n');
                }
                return {
                    name: program.program_name || 'Unnamed Program',
                    target: target,
                    rating: program.rating || 'not-started',
                    status: statusText,
                    text_metrics: program.text_metrics || {
                        name_length: (program.program_name || 'Unnamed Program').length,
                        target_bullet_count: target.split('\n').length,
                        target_max_chars: Math.max(...target.split('\n').map(line => line.length), 0),
                        status_bullet_count: statusText.split('\n').length,
                        status_max_chars: Math.max(...statusText.split('\n').map(line => line.length), 0)
                    }
                };
            });
        }
        
        // Create the program data table
        ReportStyler.createProgramDataTable(slide, pptx, themeColors, defaultFont, programs, currentQuarter);
    }

        // Note: All chart-related functions have been moved to ReportStyler module
    // to maintain proper separation of concerns - addLineChart, addTotalDegradedAreaChart, etc.

    /**
     * Add the top section of the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     * @param {string} sectorName - The sector name
     */    function addTopSection(slide, data, pptx, themeColors, defaultFont, sectorName) {
        // Create the sector box in the top left
        ReportStyler.createSectorBox(slide, pptx, themeColors);
        
        // Add sector icon or fallback shape
        // Use absolute URL for PptxGenJS image loading
        const baseUrl = window.ReportGeneratorConfig?.appUrl || window.APP_URL || window.location.origin;
        const iconPath = `${baseUrl}/assets/images/forest-icon.png`;
        ReportStyler.addSectorIcon(slide, pptx, themeColors, iconPath);
        
        // Add sector name and target text
        const safeSectorName = String(sectorName || 'Forestry');
        console.log('Safe sector name:', safeSectorName, 'Type:', typeof safeSectorName);
        ReportStyler.addSectorText(slide, safeSectorName, 'RM 8 bil in exports by 2030', themeColors, defaultFont);
        
        // Create MUDeNR outcomes box
        const mudenrBox = ReportStyler.createMudenrBox(slide, pptx, themeColors);
        
        // Add MUDeNR outcome bullets
        ReportStyler.addMudenrOutcomes(slide, pptx, mudenrBox, defaultFont, themeColors);
        
        // Create quarter box with yellow indicator
        const quarterText = data.quarter || 'Q2 2025';
        console.log('Quarter text being used:', quarterText, 'Type:', typeof quarterText);
        ReportStyler.createQuarterBox(slide, pptx, themeColors, String(quarterText), defaultFont);
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
     */    function generatePresentation(data, statusMessage) {
        return new Promise((resolve, reject) => {
            try {
                console.log('Starting presentation generation with data:', data);
                console.log('Data contains programs array?', Array.isArray(data.programs), 'Length:', data.programs ? data.programs.length : 0);
                console.log('Data contains projects array?', Array.isArray(data.projects), 'Length:', data.projects ? data.projects.length : 0);
                console.log('Data.quarter:', data.quarter, 'Type:', typeof data.quarter);
                console.log('Data.reportTitle:', data.reportTitle, 'Type:', typeof data.reportTitle);
                console.log('Data.sectorLeads:', data.sectorLeads, 'Type:', typeof data.sectorLeads);
                console.log('Data.draftDate:', data.draftDate, 'Type:', typeof data.draftDate);
                
                // Update status if available
                if (statusMessage) statusMessage.textContent = 'Creating presentation...';
                
                // Create a new presentation
                const pptx = new PptxGenJS();
                
                // Set slide size to widescreen 16:9
                pptx.layout = 'LAYOUT_WIDE';
                
                // Define theme colors
                const themeColors = ReportStyler.getThemeColors();
                const defaultFont = ReportStyler.getDefaultFont(); // Assuming you have a way to get defaultFont

                // REMOVING THE TEMPORARY CODE FOR fit:shrink TEST
                // if (typeof ReportStyler.testFitShrink === 'function') { ... }
                
                // Create a slide without using master slides
                const slide = pptx.addSlide();
                
                // Populate slide with top and bottom sections
                populateSlide(slide, data, pptx, themeColors);
                  // Get PPTX as blob
                if (statusMessage) statusMessage.textContent = 'Finalizing presentation...';
                
                // Use write('blob') to get the actual PPTX content as a Blob for server upload
                pptx.write('blob')
                    .then(blob => {
                        console.log('PPTX generated successfully as blob, size:', blob.size, 'bytes');
                        resolve(blob);
                    })
                    .catch(error => {
                        console.error('Error generating PPTX blob:', error);
                        reject(new Error('Error generating PPTX: ' + error.message));
                    });
                
            } catch (error) {
                console.error('Presentation generation error:', error);
                reject(new Error('Error in presentation generation: ' + error.message));
            }
        });
    }    // Expose public methods
    return {
        populateSlide,
        generatePresentation
    };
})();

} // End ReportPopulator guard

// Defensive wrapper for chart rendering
function safeChartField(field, fallback = '') {
    return typeof field === 'string' ? field : (field !== undefined && field !== null ? String(field) : fallback);
}