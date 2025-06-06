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
            const sectorName = data.reportTitle ? data.reportTitle.split(' ')[0] : 'Forestry';
            
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
            
            // Add KPI boxes with metrics_details data
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
        console.log("Adding KPI boxes with outcomes_details data");        if (data && data.metrics_details && data.metrics_details.length > 0) {
            console.log("Using outcomes_details data for KPIs:", data.metrics_details);

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
            console.warn("No KPI data available in outcomes_details. Falling back to legacy or default KPIs if defined.");
            // Fallback to legacy kpi1, kpi2, kpi3 if outcomes_details is empty
            // This part can be adjusted or removed if strict adherence to selected KPIs is required
            // and no fallback to old kpi objects is desired when outcomes_details is empty.
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
     * Add program data table to the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addProgramDataTable(slide, data, pptx, themeColors, defaultFont) {
        // Initialize programs array
        let programs = [];
        let currentQuarter = '';
        
        // Try to get the current quarter from the data
        if (data && data.quarter) {
            currentQuarter = data.quarter;
        }
        
        if (data && data.programs && Array.isArray(data.programs)) {
            // If programs data is already provided in the expected format, use it directly
            programs = data.programs;        } else if (data && data.program_submissions && Array.isArray(data.program_submissions)) {
            // Extract from program_submissions if available
            programs = data.program_submissions.map(submission => {
                // Extract target from content_json if available
                let target = 'Not specified';
                let status = 'Not available';
                
                if (submission.content_json) {
                    try {
                        const content = typeof submission.content_json === 'string' 
                            ? JSON.parse(submission.content_json) 
                            : submission.content_json;
                        
                        console.log('Processing content_json for program:', submission.program_name, content);
                          // Get the first target or combine multiple targets
                        if (content.targets && content.targets.length > 0) {
                            // Process multiple targets and combine them with newlines
                            const targetTexts = [];
                            const statusTexts = [];
                            
                            content.targets.forEach(t => {
                                // Get target text with fallbacks
                                const targetText = t.target_text || t.text || 'No target specified';
                                targetTexts.push(targetText);
                                
                                // Get status with fallbacks
                                const statusDesc = t.status_description || 'Status not available';
                                statusTexts.push(statusDesc);
                            });
                            
                            // Join with newlines to create bullet points
                            target = targetTexts.join('\n');
                            console.log('Combined targets:', targetTexts.length, target);
                            
                            // Join status descriptions with newlines
                            status = statusTexts.join('\n');
                            console.log('Combined status descriptions:', statusTexts.length, status);
                        } else if (content.target) {
                            // Old format with direct target property
                            target = content.target;
                            status = content.status_text || status;
                        }
                    } catch (e) {
                        console.error('Error parsing content_json:', e, submission.content_json);
                    }
                }
                  return {
                    name: submission.program_name || 'Unnamed Program',
                    target: target,
                    rating: submission.rating || 'not-started',
                    status: status,
                    text_metrics: submission.text_metrics || {
                        // Create basic text metrics if not provided
                        name_length: (submission.program_name || 'Unnamed Program').length,
                        target_bullet_count: target.split('\n').length,
                        target_max_chars: Math.max(...target.split('\n').map(line => line.length), 0),
                        status_bullet_count: status.split('\n').length,
                        status_max_chars: Math.max(...status.split('\n').map(line => line.length), 0)
                    }
                };
            });        } else if (data && data.projects && Array.isArray(data.projects)) {
            // Extract from projects data structure (new API format)
            programs = data.projects.map(project => {
                const targetText = project.target || 'Not specified';
                const statusText = project.status || 'Not available';
                return {
                    name: project.name || 'Unnamed Program',
                    target: targetText,
                    rating: project.rating || 'not-started',
                    status: statusText,
                    text_metrics: project.text_metrics || {
                        // Create basic text metrics if not provided
                        name_length: (project.name || 'Unnamed Program').length,
                        target_bullet_count: targetText.split('\n').length,
                        target_max_chars: Math.max(...targetText.split('\n').map(line => line.length), 0),
                        status_bullet_count: statusText.split('\n').length,
                        status_max_chars: Math.max(...statusText.split('\n').map(line => line.length), 0)
                    }
                };
            });
        } else if (data && data.sector_programs && Array.isArray(data.sector_programs)) {
            // Extract from sector_programs data structure if available
            programs = data.sector_programs.map(program => {
                const targetText = program.target || 'Not specified';
                const statusText = program.status_description || 'Not available';
                return {
                    name: program.program_name || 'Unnamed Program',
                    target: targetText,
                    rating: program.rating || 'not-started',
                    status: statusText,
                    text_metrics: program.text_metrics || {
                        // Create basic text metrics if not provided
                        name_length: (program.program_name || 'Unnamed Program').length,
                        target_bullet_count: targetText.split('\n').length,
                        target_max_chars: Math.max(...targetText.split('\n').map(line => line.length), 0),
                        status_bullet_count: statusText.split('\n').length,
                        status_max_chars: Math.max(...statusText.split('\n').map(line => line.length), 0)
                    }
                };
            });
        } else {
            // Fallback to extract any program-related data we can find
            try {
                // Try to find programs in various data structures
                const possibleProgramArrays = [
                    data.programs,
                    data.programsList,
                    data.sector_data && data.sector_data.programs
                ];
                
                for (const programArray of possibleProgramArrays) {
                    if (programArray && Array.isArray(programArray) && programArray.length > 0) {                        programs = programArray.map(prog => {
                            const targetText = prog.target || 'Not specified';
                            const statusText = prog.status || prog.status_description || 'Not available';
                            const programName = prog.program_name || prog.name || 'Unnamed Program';
                            
                            return {
                                name: programName,
                                target: targetText,
                                rating: prog.rating || 'not-started',
                                status: statusText,
                                text_metrics: prog.text_metrics || {
                                    // Create basic text metrics if not provided
                                    name_length: programName.length,
                                    target_bullet_count: targetText.split('\n').length,
                                    target_max_chars: Math.max(...targetText.split('\n').map(line => line.length), 0),
                                    status_bullet_count: statusText.split('\n').length,
                                    status_max_chars: Math.max(...statusText.split('\n').map(line => line.length), 0)
                                }
                            };
                        });
                        break;
                    }
                }
                  // If we still don't have programs data, create a fallback sample
                if (programs.length === 0) {
                    console.warn('No program data found in report data. Using placeholder data.');
                    console.warn('Data received from API:', JSON.stringify(data));
                    console.warn('Data.programs:', data.programs);
                    console.warn('Data.program_submissions:', data.program_submissions);
                    console.warn('Data.projects:', data.projects);
                      programs = [
                        {
                            name: 'Forest Conservation',
                            target: '50,000 ha protected',
                            rating: 'on-track',
                            status: 'Target achieved',
                            text_metrics: {
                                name_length: 'Forest Conservation'.length,
                                target_bullet_count: 1,
                                target_max_chars: '50,000 ha protected'.length,
                                status_bullet_count: 1,
                                status_max_chars: 'Target achieved'.length
                            }
                        },
                        {
                            name: 'Timber Export Growth',
                            target: 'RM 5.2 bil annual export',
                            rating: 'minor-delays',
                            status: 'Slightly below target',
                            text_metrics: {
                                name_length: 'Timber Export Growth'.length,
                                target_bullet_count: 1,
                                target_max_chars: 'RM 5.2 bil annual export'.length,
                                status_bullet_count: 1,
                                status_max_chars: 'Slightly below target'.length
                            }
                        }
                    ];
                }
            } catch (e) {
                console.error('Error extracting program data:', e);
            }
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
     */    function generatePresentation(data, statusMessage) {
        return new Promise((resolve, reject) => {
            try {
                console.log('Starting presentation generation with data:', data);
                console.log('Data contains programs array?', Array.isArray(data.programs), 'Length:', data.programs ? data.programs.length : 0);
                console.log('Data contains projects array?', Array.isArray(data.projects), 'Length:', data.projects ? data.projects.length : 0);
                
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
    }    // Expose public methods
    return {
        populateSlide,
        generatePresentation
    };
})();

} // End ReportPopulator guard