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
        // Define common font for consistency
        const defaultFont = ReportStyler.getDefaultFont();
        
        // Extract sector name from report data
        const sectorName = data.reportTitle ? data.reportTitle.split(' ')[0] : 'Forestry';
        
        // Add slide sections
        addTopSection(slide, data, pptx, themeColors, defaultFont, sectorName);
        addMiddleSection(slide, data, pptx, themeColors, defaultFont);
        addProjectsSection(slide, data, pptx, themeColors, defaultFont);
        addChartsSection(slide, data, pptx, themeColors, defaultFont);
        addKPISection(slide, data, pptx, themeColors, defaultFont);
        addFooterSection(slide, data, pptx, themeColors, defaultFont);
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
            x: 3.28, y: 0.08, w: 9.83, h: 0.63, // 8.33cm, 0.2cm, 24.95cm, 1.6cm converted to inches
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
            x: 3.35, y: 0.05, w: 3.56, h: 0.64, // 8.51cm, 0.12cm, 9.05cm, 1.62cm converted to inches
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
            x: 6.87, y: 0.06, w: 3.56, h: 0.64, // 17.45cm, 0.15cm, 9.05cm, 1.62cm converted to inches
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
            x: 10.58, y: 0.08, w: 1.87, h: 0.63, // 26.88cm, 0.2cm, 4.74cm, 1.6cm converted to inches
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add yellow square next to the quarter box with no gap
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 12.45, y: 0.08, w: 0.66, h: 0.63, // Positioned precisely next to quarter box
            fill: { color: 'FFFF00' }, // Yellow color as requested
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add quarter information in the box
        slide.addText(data.quarter || 'Q1 2025', { 
            x: 10.58, y: 0.08, w: 1.87, h: 0.63,
            fontSize: 14, bold: true, 
            fontFace: defaultFont,
            color: themeColors.text, // Black color
            align: 'center',
            valign: 'middle'
        });
    }

    /**
     * Add the middle section of the slide (Programs/Projects header)
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addMiddleSection(slide, data, pptx, themeColors, defaultFont) {
        // Add Programs/Projects section header box
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 0.5, y: 1.5, w: 7.5, h: 0.4,
            fill: { color: themeColors.headerBg },
            line: { color: themeColors.primary, width: 1 }
        });

        // Add Programs/Projects title text
        slide.addText('Programs / Projects', { 
            x: 0.7, y: 1.55, w: 7.0, h: 0.3, 
            fontSize: 14, bold: true,
            fontFace: defaultFont,
            color: themeColors.primary
        });
    }

    /**
     * Add the projects section of the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addProjectsSection(slide, data, pptx, themeColors, defaultFont) {
        // Starting Y position for projects
        let yPos = 2.0; 
        
        if (data.projects && Array.isArray(data.projects)) {
            data.projects.forEach((proj, index) => {
                // Add alternating row background
                const rowBgColor = index % 2 === 0 ? 'FFFFFF' : 'F5F5F5';
                
                slide.addShape(pptx.shapes.RECTANGLE, {
                    x: 0.5, y: yPos, w: 7.5, h: 0.45,
                    fill: { color: rowBgColor },
                    line: { color: 'EEEEEE', width: 0.5 }
                });
                
                // Status color indicator
                let statusColor = '';
                switch (proj.rating) {
                    case 'green': statusColor = themeColors.greenStatus; break;
                    case 'yellow': statusColor = themeColors.yellowStatus; break;
                    case 'red': statusColor = themeColors.redStatus; break;
                    default: statusColor = themeColors.greyStatus;
                }
                
                // Add status indicator (colored box)
                slide.addShape(pptx.shapes.RECTANGLE, { 
                    x: 6.0, y: yPos + 0.075, w: 0.3, h: 0.3, 
                    fill: { color: statusColor },
                    line: { color: 'FFFFFF', width: 1 }
                });
                
                // Add project name
                slide.addText(proj.name || '[Project Name]', { 
                    x: 0.7, y: yPos + 0.05, w: 5.0, h: 0.35, 
                    fontSize: 12, bold: true, 
                    fontFace: defaultFont,
                    color: themeColors.text,
                    valign: 'middle'
                });
                
                // Add target text if available
                if (proj.target) {
                    slide.addText(proj.target, { 
                        x: 6.4, y: yPos + 0.125, w: 1.5, h: 0.2, 
                        fontSize: 10, 
                        fontFace: defaultFont,
                        color: themeColors.lightText
                    });
                }
                
                // Move Y position down for next project
                yPos += 0.5;
            });
        }
    }

    /**
     * Add charts section to the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addChartsSection(slide, data, pptx, themeColors, defaultFont) {
        if (data.charts && data.charts.statusChart) {
            // Add status distribution pie chart
            slide.addChart(pptx.ChartType.PIE, data.charts.statusChart, { 
                x: 8.2, y: 1.7, w: 4.0, h: 2.8,
                showTitle: true, 
                title: 'Project Status Distribution',
                titleFontSize: 12,
                titleColor: themeColors.primary,
                showLegend: true,
                legendPos: 'b',
                legendFontSize: 9,
                dataLabelColor: 'FFFFFF',
                dataLabelFontSize: 9,
                shadow: { type: 'subtle' }
            });
        }
    }

    /**
     * Add KPI metrics section to the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     */
    function addKPISection(slide, data, pptx, themeColors, defaultFont) {
        if (data.kpis) {
            let kpiYPos = 5.2;
            
            // Loop through KPIs and add them to the slide
            Object.entries(data.kpis).forEach(([key, kpi], index) => {
                if (kpi.title && kpi.value) {
                    // Create KPI box
                    slide.addShape(pptx.shapes.RECTANGLE, {
                        x: 8.0, y: kpiYPos, w: 4.5, h: 0.7,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 0.75 }
                    });
                    
                    // Add KPI title
                    slide.addText(kpi.title, { 
                        x: 8.2, y: kpiYPos + 0.1, w: 4.1, h: 0.25, 
                        fontSize: 11, bold: true,
                        fontFace: defaultFont,
                        color: themeColors.primary
                    });
                    
                    // Add KPI value
                    slide.addText(kpi.value, { 
                        x: 8.2, y: kpiYPos + 0.35, w: 4.1, h: 0.25, 
                        fontSize: 10,
                        fontFace: defaultFont,
                        color: themeColors.text
                    });
                    
                    kpiYPos += 0.8; // Move position for next KPI
                }
            });
        }
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
        // Add "Legend:" text with exact dimensions specified (cm converted to inches)
        // H = 0.64cm (0.25"), W= 1.76cm (0.69")
        // horizontal pos = 0.52cm (0.2"), vertical pos = 18.14cm (7.14") from top left corner
        slide.addText('Legend:', {
            x: 0.2, y: 7.14, w: 0.69, h: 0.25,
            fontSize: 9, italic: true,
            fontFace: 'Calibri', // Explicitly using Calibri as specified
            color: themeColors.lightText,
            align: 'left', // Left align as specified
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
            x: 1.000, // 2.54cm converted to inches
            y: 7.118, // 18.08cm converted to inches
            w: 0.31, h: 0.31, // Original square dimensions maintained
            fill: { color: legendItems[0].color },
            line: { color: '000000', width: 0.5 }
        });
        
        // Text 1
        slide.addText(legendItems[0].label, {
            x: 1.323, // 3.36cm converted to inches
            y: 7.059, // 17.93cm converted to inches
            w: 1.5748, // Width of 4cm converted to inches
            h: 0.4055, // Height of 1.03cm converted to inches
            fontSize: 9,
            italic: true,
            fontFace: 'Calibri',
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Square 2 (Yellow)
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 2.953, // 7.5cm converted to inches
            y: 7.118, // 18.08cm converted to inches
            w: 0.31, h: 0.31,
            fill: { color: legendItems[1].color },
            line: { color: '000000', width: 0.5 }
        });
        
        // Text 2
        slide.addText(legendItems[1].label, {
            x: 3.272, // 8.31cm converted to inches
            y: 7.059, // 17.93cm converted to inches
            w: 1.5748,
            h: 0.4055,
            fontSize: 9,
            italic: true,
            fontFace: 'Calibri',
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Square 3 (Red)
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 4.776, // 12.13cm converted to inches
            y: 7.087, // 18.00cm converted to inches
            w: 0.31, h: 0.31,
            fill: { color: legendItems[2].color },
            line: { color: '000000', width: 0.5 }
        });
        
        // Text 3
        slide.addText(legendItems[2].label, {
            x: 5.094, // 12.94cm converted to inches
            y: 7.102, // 18.04cm converted to inches
            w: 1.5748,
            h: 0.4055,
            fontSize: 9,
            italic: true,
            fontFace: 'Calibri',
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Square 4 (Grey)
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: 6.146, // 15.61cm converted to inches
            y: 7.087, // 18.00cm converted to inches
            w: 0.31, h: 0.31,
            fill: { color: legendItems[3].color },
            line: { color: '000000', width: 0.5 }
        });
        
        // Text 4
        slide.addText(legendItems[3].label, {
            x: 6.520, // 16.56cm converted to inches
            y: 7.110, // 18.06cm converted to inches
            w: 1.5748,
            h: 0.4055,
            fontSize: 9,
            italic: true,
            fontFace: 'Calibri',
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
        
        // Add year indicator circles and text
        // Circle 1 - Previous Year (orange)
        slide.addShape(pptx.shapes.OVAL, {
            x: 7.657, // 19.45cm converted to inches
            y: 7.193, // 18.27cm converted to inches
            w: 0.118, // 0.3cm converted to inches
            h: 0.118, // 0.3cm converted to inches
            fill: { color: 'ED7D31' }, // Orange color
            line: { color: 'ED7D31', width: 0.5 }
        });
        
        // Text 1 - Previous Year (removed "previous year" text)
        slide.addText(`${previousYear}`, {
            x: 7.689, // 19.53cm converted to inches
            y: 7.126, // 18.1cm converted to inches
            w: 0.642, // 1.63cm converted to inches
            h: 0.252, // 0.64cm converted to inches
            fontSize: 8,
            fontFace: 'Calibri',
            color: '000000', // Black text
            align: 'left',
            valign: 'middle'
        });
        
        // Circle 2 - Current Year (blue)
        slide.addShape(pptx.shapes.OVAL, {
            x: 8.193, // 20.81cm converted to inches
            y: 7.193, // 18.27cm converted to inches
            w: 0.118, // 0.3cm converted to inches
            h: 0.118, // 0.3cm converted to inches
            fill: { color: '0070C0' }, // Blue color
            line: { color: '0070C0', width: 0.5 }
        });
        
        // Text 2 - Current Year (removed "current year" text)
        slide.addText(`${currentYear}`, {
            x: 8.244, // 20.94cm converted to inches
            y: 7.126, // 18.1cm converted to inches
            w: 0.642, // 1.63cm converted to inches
            h: 0.252, // 0.64cm converted to inches
            fontSize: 8,
            fontFace: 'Calibri',
            color: '000000', // Black text
            align: 'left',
            valign: 'middle'
        });
        
        // Format current date for the draft text (e.g., "DRAFT 7 May 2025")
        const today = new Date();
        const draftDateString = `DRAFT ${today.getDate()} ${today.toLocaleString('en-US', { month: 'long' })} ${today.getFullYear()}`;
        
        // Add Draft text box with red text, bold, Calibri 14
        slide.addText(draftDateString, {
            x: 9.197, // 23.36cm converted to inches
            y: 7.098, // 18.03cm converted to inches
            w: 2.150, // 5.46cm converted to inches
            h: 0.339, // 0.86cm converted to inches
            fontSize: 14, 
            bold: true,
            fontFace: 'Calibri',
            color: 'FF0000', // Red color
            align: 'left',
            valign: 'middle'
        });
        
        // Old draft date text removed as requested
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
                
                // Set slide size to match the template (widescreen 16:9)
                pptx.layout = 'LAYOUT_WIDE';
                
                // Define our master slide with styling
                const themeColors = ReportStyler.defineReportMaster(pptx);
                
                // Create a slide using the master
                const slide = pptx.addSlide({ masterName: 'REPORT_MASTER_SLIDE' });
                
                // Populate slide with data
                populateSlide(slide, data, pptx, themeColors);
                
                // Get PPTX as blob
                if (statusMessage) statusMessage.textContent = 'Finalizing presentation...';
                
                pptx.write('blob')
                    .then(blob => {
                        resolve(blob);
                    })
                    .catch(error => {
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