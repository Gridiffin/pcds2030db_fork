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
            
            // Add line chart
            try {
                addLineChart(slide, pptx, themeColors, defaultFont, data);
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
            
            // Add KPI boxes with metrics_details data
            addKpiBoxes(slide, data, pptx, themeColors, defaultFont);
            
            addFooterSection(slide, data, pptx, themeColors, defaultFont);
            
        } catch (err) {
            console.error("Error in populateSlide:", err);
        }
    }
      /**
     * Add KPI boxes with data from metrics_details
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addKpiBoxes(slide, data, pptx, themeColors, defaultFont) {
        console.log("Adding KPI boxes with metrics_details data");

        if (data && data.metrics_details && data.metrics_details.length > 0) {
            console.log("Using metrics_details data for KPIs:", data.metrics_details);

            data.metrics_details.forEach((kpi, index) => {
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
            console.warn("No KPI data available in metrics_details. Falling back to legacy or default KPIs if defined.");
            // Fallback to legacy kpi1, kpi2, kpi3 if metrics_details is empty
            // This part can be adjusted or removed if strict adherence to selected KPIs is required
            // and no fallback to old kpi objects is desired when metrics_details is empty.
            if (data.kpi1) {
                console.log("Fallback to legacy kpi1");
                // Assuming kpi1, kpi2, kpi3 are simple {name, value, description}
                // We might need a simplified layout or a specific layout_type for these.
                // For now, let's assume a default simple layout.
                ReportStyler.createKpiBox(slide, pptx, themeColors, defaultFont, data.kpi1.name, { layout_type: 'simple', items: [{ value: data.kpi1.value, description: data.kpi1.description }] }, 0);
            }
            if (data.kpi2) {
                console.log("Fallback to legacy kpi2");
                ReportStyler.createKpiBox(slide, pptx, themeColors, defaultFont, data.kpi2.name, { layout_type: 'simple', items: [{ value: data.kpi2.value, description: data.kpi2.description }] }, 1);
            }
            if (data.kpi3) {
                console.log("Fallback to legacy kpi3");
                ReportStyler.createKpiBox(slide, pptx, themeColors, defaultFont, data.kpi3.name, { layout_type: 'simple', items: [{ value: data.kpi3.value, description: data.kpi3.description }] }, 2);
            }
            if (!data.kpi1 && !data.kpi2 && !data.kpi3) {
                 console.warn("No KPI data available at all.");
            }
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
        
        // Get current date and extract current year and previous year
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const previousYear = currentYear - 1;
        
        // Add title using the styler function
        ReportStyler.createChartTitle(slide, 'Timber Export Value (RM)', container, themeColors, defaultFont);
        
        // Check if we have the required chart data from the API
        if (!data || !data.charts || !data.charts.main_chart || !data.charts.main_chart.data) {
            console.warn("No timber export data available, using placeholder");
            return;
        }
        
        // Get timber export data from API response
        const timberData = data.charts.main_chart.data;
        console.log("Timber export data from API:", timberData);
        
        // Get monthly labels directly from the data
        const monthLabels = timberData.labels || ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        
        // Dynamic property names based on current and previous year
        const currentYearProp = `data${currentYear}`;
        const previousYearProp = `data${previousYear}`;
        
        // Get data for current and previous year, with fallbacks to empty arrays
        const currentYearData = timberData[currentYearProp] || Array(12).fill(0);
        const previousYearData = timberData[previousYearProp] || Array(12).fill(0);
        
        console.log(`${previousYear} Monthly data:`, previousYearData);
        console.log(`${currentYear} Monthly data:`, currentYearData);
        
        // For current year, only include data up to the current month
        const currentMonth = currentDate.getMonth(); // 0-based (0 = January)
        
        // Create arrays with exactly 12 values each
        const fullPreviousYearData = [...previousYearData];
        while (fullPreviousYearData.length < 12) {
            fullPreviousYearData.push(0);
        }
        
        const fullCurrentYearData = [...currentYearData];
        while (fullCurrentYearData.length < 12) {
            fullCurrentYearData.push(0);
        }
        
        // Clip arrays to exactly 12 items
        const clippedPreviousYearData = fullPreviousYearData.slice(0, 12);
        const clippedCurrentYearData = fullCurrentYearData.slice(0, 12);
        
        // Calculate maximum value to help with scaling (using actual data)
        const maxMonthlyValue = Math.max(
            ...clippedPreviousYearData.filter(val => val !== undefined && val !== null),
            ...clippedCurrentYearData.filter(val => val !== undefined && val !== null),
            1 // Ensure we always have a positive value for scaling
        );
        console.log("Maximum monthly value:", maxMonthlyValue);          // Calculate total values for each year
        const previousYearTotal = clippedPreviousYearData.reduce((sum, val) => sum + (val || 0), 0);
        const currentYearTotal = clippedCurrentYearData.reduce((sum, val) => sum + (val || 0), 0);        // For large export values, reduce precision to conserve space while maintaining readability
        // Large values (over 1 million) use fewer decimal places to prevent overflow
        const formatNumberWithSmartPrecision = (num) => {
            if (num >= 10000000) { // Over 10 million
                return num.toLocaleString('en-US', {maximumFractionDigits: 0}); // No decimals
            } else if (num >= 1000000) { // 1-10 million
                return num.toLocaleString('en-US', {maximumFractionDigits: 1}); // One decimal
            } else {
                return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); // Two decimals
            }
        };
        
        // Format the totals with adaptive precision and add RM prefix
        const formattedPreviousYearTotal = `RM ${formatNumberWithSmartPrecision(previousYearTotal)}`;
        const formattedCurrentYearTotal = `RM ${formatNumberWithSmartPrecision(currentYearTotal)}`;
          // Format years as requested - abbreviated with apostrophe (e.g., '24 Total)
        // Ensure the apostrophe is visible by using the correct character
        const currentYearAbbr = "'" + currentYear.toString().substring(2) + " Total";
        const previousYearAbbr = "'" + previousYear.toString().substring(2) + " Total";
          // Position total boxes side by side at the bottom-left of chart container
        // Make sure they're aligned properly with small separation between boxes
          // SWAPPED ORDER: Current year first (on the left), then previous year
        // Add total box for current year at the bottom-left
        ReportStyler.createTotalValueBox(
            slide, 
            pptx, 
            themeColors, 
            currentYearAbbr, 
            formattedCurrentYearTotal, 
            container.x + 0.10, // Small margin from left edge
            container.y + container.h - 0.38, // Positioned slightly higher to prevent overlap with container bottom
            defaultFont
        );
        
        // Add total box for previous year next to the current year box
        ReportStyler.createTotalValueBox(
            slide, 
            pptx, 
            themeColors, 
            previousYearAbbr, 
            formattedPreviousYearTotal, 
            container.x + 2.15, // Slightly more spacing between boxes for large numbers
            container.y + container.h - 0.38, // Same vertical position as current year
            defaultFont
        );
          // Create chart data with the real values from API
        const chartData = [
            {
                name: `${previousYear} Export Value`,
                labels: monthLabels,
                values: clippedPreviousYearData
            },
            {
                name: `${currentYear} Export Value`,
                labels: monthLabels,
                values: clippedCurrentYearData
            }
        ];
          // Get chart options from the styler
        const chartOptions = ReportStyler.getLineChartOptions(container, themeColors, defaultFont);
        
        // Add chart (no need to set axis titles as they've been removed)
        slide.addChart(pptx.ChartType.line || 'line', chartData, chartOptions);
        console.log("Line chart with real data added to slide");
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