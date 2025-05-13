/**
 * Report Slide Styler Module
 * 
 * Handles all styling-related functionality for the report generator
 */

const ReportStyler = (function() {
    /**
     * Get the theme colors for the presentation
     * @returns {Object} The theme colors
     */
    function getThemeColors() {
        return {
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
    }
    
    /**
     * Get the default font for the presentation
     * @returns {string} The default font name
     */
    function getDefaultFont() {
        return 'Calibri';
    }
    
    /**
     * Define the master slide layout with styling that matches your template
     * @param {Object} pptx - The PptxGenJS instance
     * @returns {Object} The theme colors used
     */
    function defineReportMaster(pptx) {
        // Get theme and style definitions
        const themeColors = getThemeColors();
        const defaultFont = getDefaultFont();
        
        // Create slide master with enhanced styling to match template
        pptx.defineSlideMaster({
            title: 'REPORT_MASTER_SLIDE',
            background: { color: 'FFFFFF' }, // White background
            margin: [0.5, 0.5, 0.5, 0.5],
            objects: getSlideObjects(themeColors, defaultFont),
        });
        
        return themeColors;
    }
    
    /**
     * Get slide objects for the master slide
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @returns {Array} Array of slide objects
     */
    function getSlideObjects(themeColors, defaultFont) {
        return [
            // Sector box with border - exact measurements from PowerPoint template (cm converted to inches)
            { 'sectorBox': { 
                type: 'rect', 
                options: { 
                    x: 0.22, y: 0.08, w: 3.06, h: 0.63, 
                    fill: { color: 'FFFFFF' },
                    line: { color: themeColors.primary, width: 1 }
                } 
            }},
            
            // Logo space in footer
            { 'logo': { 
                type: 'placeholder',
                options: { 
                    x: 11.0, y: 7.3, w: 1.5, h: 0.4
                } 
            }},
            
            // Legend text box - updated with exact dimensions from PowerPoint
            { 'legendText': { 
                options: { 
                    x: 0.2, y: 7.14, w: 0.69, h: 0.25, // 0.52cm, 18.14cm, 1.76cm, 0.64cm converted to inches
                    fontSize: 9, italic: true,
                    fontFace: defaultFont,
                    color: themeColors.lightText,
                    align: 'center',
                    valign: 'middle',
                    text: 'Legend:'
                } 
            }}
        ];
    }

    /**
     * Create a color indicator shape
     * @param {Object} slide - The slide to add the shape to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {string} color - The color code (hex without #)
     * @param {number} x - The x position
     * @param {number} y - The y position
     * @param {number} w - The width
     * @param {number} h - The height
     */
    function createColorIndicator(slide, pptx, color, x, y, w, h) {
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: x, y: y, w: w, h: h,
            fill: { color: color },
            line: { color: 'FFFFFF', width: 0.5 }
        });
    }

    /**
     * Create a standard text box
     * @param {Object} slide - The slide to add the text to
     * @param {string} text - The text content
     * @param {Object} options - The formatting options
     */
    function createTextBox(slide, text, options) {
        slide.addText(text, options);
    }

    /**
     * Create a chart container with border
     * @param {Object} slide - The slide to add the container to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @returns {Object} The container dimensions {x, y, w, h}
     */
    function createChartContainer(slide, pptx, themeColors) {
        const container = {
            x: 9.17, // Position from left (23.3cm)
            y: 0.58, // Position from top (1.47cm)
            w: 4.05, // Width in inches (10.28cm)
            h: 2.41  // Height in inches (6.13cm)
        };
        
        return container;
    }
      /**
     * Create a total value box with specific styling for chart totals.
     * @param {Object} slide - The slide to add the text box to.
     * @param {Object} pptx - The PptxGenJS instance.
     * @param {Object} themeColors - The theme colors for styling.
     * @param {string} titleText - The title text (e.g. '23 Total).
     * @param {string} valueText - The formatted value text.
     * @param {number} x - X position of the box.
     * @param {number} y - Y position of the box.
     * @param {string} defaultFont - The default font.
     */
    function createTotalValueBox(slide, pptx, themeColors, titleText, valueText, x, y, defaultFont) {
        // Create a box with both title and value
        const boxWidth = 1.85;  // Width for the total box
        const boxHeight = 0.35; // Height for the total box
        
        // Add background box with border
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: x,
            y: y,
            w: boxWidth,
            h: boxHeight,
            fill: { color: 'FFFFFF' }, // White background
            line: { color: themeColors.primary, width: 0.75 }
        });
        
        // Add title text (year) on the left
        slide.addText(titleText, {
            x: x + 0.05,
            y: y + 0.03,
            w: 0.75,
            h: boxHeight - 0.06,
            fontSize: 9,
            bold: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'left',
            valign: 'middle'
        });
        
        // Add value on the right
        slide.addText(valueText, {
            x: x + 0.80,
            y: y + 0.03,
            w: boxWidth - 0.85,
            h: boxHeight - 0.06,
            fontSize: 10,
            bold: true,
            fontFace: defaultFont,
            color: themeColors.accent1,
            align: 'right',
            valign: 'middle'
        });
    }

    /**
     * Create a chart title
     * @param {Object} slide - The slide to add the title to
     * @param {string} titleText - The title text
     * @param {Object} container - The container dimensions
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     */
    function createChartTitle(slide, titleText, container, themeColors, defaultFont) {
        slide.addText(titleText, {
            x: container.x, 
            y: container.y + 0.1, // Reduced spacing to fit smaller container
            w: container.w, 
            h: 0.3, // Reduced height
            fontSize: 14, // Reduced font size to fit smaller container
            bold: true,
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'center'
        });
    }
    
    /**
     * Get line chart options with proper styling
     * @param {Object} container - The container dimensions
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @returns {Object} The chart options
     */
    function getLineChartOptions(container, themeColors, defaultFont) {
        const chartX = container.x + 0.1;   // Reduced left margin
        const chartY = container.y + 0.35;  // Less space for title to maximize chart area
        const chartW = container.w - 0.2;   // Wider chart with minimal horizontal padding
        const chartH = container.h - 0.65;  // Slightly more space at bottom for the larger total boxes
        
        return {
            x: chartX, 
            y: chartY, 
            w: chartW, 
            h: chartH,
            chartType: 'line',
            lineSize: 2.5,                  // Slightly thicker lines for better visibility
            showTitle: false,
            showValue: false,
            showLegend: false,              // Legend removed as requested (already in footer)
            chartColors: ['0070C0', 'ED7D31'], // Blue for current year, Orange for previous year
            showMarker: true,
            markerSize: 5,                  // Slightly larger markers
            valAxisMaxVal: 400000000,
            valAxisMinVal: 0,
            valAxisMajorUnit: 50000000,
            catAxisLabelFontSize: 8,        // Smaller font for axis labels
            valAxisLabelFontSize: 8,        // Smaller font for axis labels
            valAxisLabelFontFace: defaultFont,
            catAxisLabelFontFace: defaultFont,
            catAxisLabelRotate: 45,
            valAxisLabelFormatCode: '#,##0', // Format numbers with commas
            showCatAxisTitle: false,
            showValAxisTitle: false,
            showHorizGridLines: true,
            lineWidthGrid: 0.5,             // Thinner grid lines
            showVertGridLines: false,
            border: { pt: 0, color: 'FFFFFF' } // No border around the chart
        };
    }
    
    /**
     * Create the sector box in the top-left corner
     * @param {Object} slide - The slide to add the box to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @returns {Object} The box dimensions {x, y, w, h}
     */
    function createSectorBox(slide, pptx, themeColors) {
        const boxDimensions = { 
            x: 0.22, y: 0.08, w: 3.06, h: 0.63 
        };
        
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x,
            y: boxDimensions.y, 
            w: boxDimensions.w, 
            h: boxDimensions.h,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        return boxDimensions;
    }
    
    /**
     * Add the sector icon or fallback
     * @param {Object} slide - The slide to add the icon to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @param {string} iconPath - Path to the icon image
     */
    function addSectorIcon(slide, pptx, themeColors, iconPath) {
        const iconDimensions = { 
            x: 0.26, y: 0.13, w: 0.57, h: 0.57 
        };
        
        try {
            slide.addImage({
                path: iconPath, 
                x: iconDimensions.x, 
                y: iconDimensions.y, 
                w: iconDimensions.w, 
                h: iconDimensions.h
            });
        } catch (e) {
            console.warn('Sector icon not found, using fallback shape', e);
            
            slide.addShape(pptx.shapes.RECTANGLE, {
                x: iconDimensions.x, 
                y: iconDimensions.y, 
                w: iconDimensions.w, 
                h: iconDimensions.h,
                fill: { color: themeColors.secondary },
                line: { color: themeColors.primary, width: 0.75 }
            });
        }
    }
    
    /**
     * Add sector name and target text
     * @param {Object} slide - The slide to add the text to
     * @param {string} sectorName - The sector name
     * @param {string} targetText - The target text
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     */
    function addSectorText(slide, sectorName, targetText, themeColors, defaultFont) {
        createTextBox(slide, sectorName, {
            x: 0.79, y: 0.11, w: 2.44, h: 0.41,
            fontSize: 18, bold: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center',
            valign: 'middle'
        });
        
        createTextBox(slide, targetText, {
            x: 0.78, y: 0.39, w: 2.51, h: 0.29,
            fontSize: 10.5, italic: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center'
        });
    }
    
    /**
     * Create the MUDeNR outcomes box in top-middle
     * @param {Object} slide - The slide to add the box to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @returns {Object} The box dimensions {x, y, w, h}
     */
    function createMudenrBox(slide, pptx, themeColors) {
        const boxDimensions = { 
            x: 3.28, y: 0.08, w: 9.83, h: 0.63 
        };
        
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x, 
            y: boxDimensions.y, 
            w: boxDimensions.w, 
            h: boxDimensions.h,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        return boxDimensions;
    }
    
    /**
     * Add MUDeNR outcome bullets to the slide
     * @param {Object} slide - The slide to add the text to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} boxDimensions - The MUDeNR box dimensions
     * @param {string} defaultFont - The default font
     * @param {Object} themeColors - The theme colors
     */
    function addMudenrOutcomes(slide, pptx, boxDimensions, defaultFont, themeColors) {
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
            x: boxDimensions.x + 0.07, 
            y: boxDimensions.y - 0.03, 
            w: 3.56, 
            h: 0.64,
            fontSize: 8,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'left',
            valign: 'top',
            paraSpaceBefore: 0,
            paraSpaceAfter: 0,
            lineSpacingMultiple: 0.9
        });
        
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
            x: boxDimensions.x + 3.59, 
            y: boxDimensions.y - 0.02, 
            w: 3.56, 
            h: 0.64,
            fontSize: 8,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'left',
            valign: 'top',
            paraSpaceBefore: 0,
            paraSpaceAfter: 0,
            lineSpacingMultiple: 0.9
        });
    }
    
    /**
     * Create the quarter box in the top-right
     * @param {Object} slide - The slide to add the box to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @param {string} quarterText - The quarter text (e.g., 'Q2 2025')
     * @param {string} defaultFont - The default font
     * @returns {Object} The box dimensions {x, y, w, h}
     */
    function createQuarterBox(slide, pptx, themeColors, quarterText, defaultFont) {
        const boxDimensions = { 
            x: 10.58, y: 0.08, w: 1.87, h: 0.63 
        };
        
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x, 
            y: boxDimensions.y, 
            w: boxDimensions.w, 
            h: boxDimensions.h,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x + boxDimensions.w, 
            y: boxDimensions.y, 
            w: 0.66, 
            h: boxDimensions.h,
            fill: { color: 'FFFF00' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        createTextBox(slide, quarterText, { 
            x: boxDimensions.x, 
            y: boxDimensions.y, 
            w: boxDimensions.w, 
            h: boxDimensions.h,
            fontSize: 14, bold: true, 
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center',
            valign: 'middle'
        });
        
        return boxDimensions;
    }
    
    /**
     * Add legend title to the footer
     * @param {Object} slide - The slide to add the text to
     * @param {string} defaultFont - The default font
     * @param {Object} themeColors - The theme colors
     */
    function addLegendTitle(slide, defaultFont, themeColors) {
        createTextBox(slide, 'Legend:', {
            x: 0.2, y: 7.14, w: 0.69, h: 0.25,
            fontSize: 9, italic: true,
            fontFace: defaultFont,
            color: themeColors.lightText,
            align: 'left',
            valign: 'middle'
        });
    }
    
    /**
     * Add a legend item (color square and label)
     * @param {Object} slide - The slide to add the legend to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {string} color - The color of the indicator (hex without #)
     * @param {string} label - The label text
     * @param {number} x - The x position of the color indicator
     * @param {number} y - The y position of the color indicator
     * @param {number} textX - The x position of the label text
     * @param {number} textY - The y position of the label text
     * @param {string} defaultFont - The default font
     */
    function addLegendItem(slide, pptx, color, label, x, y, textX, textY, defaultFont) {
        createColorIndicator(slide, pptx, color, x, y, 0.31, 0.31);
        
        createTextBox(slide, label, {
            x: textX, y: textY, w: 1.5748, h: 0.4055,
            fontSize: 9, italic: true,
            fontFace: defaultFont,
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
    }
    
    /**
     * Add a year indicator circle with label
     * @param {Object} slide - The slide to add the indicator to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {string} year - The year text
     * @param {string} color - The circle color (hex without #)
     * @param {number} circleX - The x position of the circle
     * @param {number} circleY - The y position of the circle
     * @param {number} textX - The x position of the text
     * @param {number} textY - The y position of the text
     * @param {string} defaultFont - The default font
     */
    function addYearIndicator(slide, pptx, year, color, circleX, circleY, textX, textY, defaultFont) {
        slide.addShape(pptx.shapes.OVAL, {
            x: circleX, y: circleY, w: 0.118, h: 0.118,
            fill: { color: color },
            line: { color: color, width: 0.5 }
        });
        
        createTextBox(slide, year, {
            x: textX, y: textY, w: 0.642, h: 0.252,
            fontSize: 8,
            fontFace: defaultFont,
            color: '000000',
            align: 'left',
            valign: 'middle'
        });
    }
    
    /**
     * Create draft text in the footer
     * @param {Object} slide - The slide to add the text to 
     * @param {string} date - The formatted date string
     * @param {string} defaultFont - The default font
     */
    function createDraftText(slide, date, defaultFont) {
        createTextBox(slide, date, {
            x: 9.197, y: 7.098, w: 2.150, h: 0.339,
            fontSize: 14, bold: true,
            fontFace: defaultFont,
            color: 'FF0000',
            align: 'left',
            valign: 'middle'
        });
    }

    /**
     * Create a KPI box with formatted title, value and description (Legacy support)
     * This function is now a dispatcher based on layout_type in detailJson.
     * @param {Object} slide - The slide to add the KPI box to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @param {string} kpiName - The name/title of the KPI (from metrics_details.name)
     * @param {Object} detailJson - The parsed detail_json object for the KPI
     * @param {number} boxIndex - Index for positioning multiple boxes (0, 1, 2)
     */
    function createKpiBox(slide, pptx, themeColors, defaultFont, kpiName, detailJson, boxIndex = 0) {
        const baseVerticalPosCm = 7.53; // Base vertical position in cm
        const verticalSpacingCm = 1.8;  // Vertical spacing between boxes in cm
        const kpiBoxHeightCm = 1.57;    // Height of a single KPI box in cm

        const yPosIn = (baseVerticalPosCm + (boxIndex * verticalSpacingCm)) / 2.54;

        const boxWidthIn = 11.0 / 2.54;
        const boxHeightIn = kpiBoxHeightCm / 2.54;
        const boxXIn = 22.58 / 2.54; // Horizontal position for all KPI boxes

        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxXIn,
            y: yPosIn,
            w: boxWidthIn,
            h: boxHeightIn,
            fill: { color: 'FFFFFF' }, // White background
            line: { color: themeColors.primary, width: 1 } // Border
        });

        switch (detailJson.layout_type) {
            case 'simple':
                renderSimpleKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, detailJson.items, boxXIn, yPosIn, boxWidthIn, boxHeightIn);
                break;
            case 'detailed_list':
                renderDetailedListKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, detailJson.items, boxXIn, yPosIn, boxWidthIn, boxHeightIn);
                break;
            case 'comparison':
                renderComparisonKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, detailJson.items, boxXIn, yPosIn, boxWidthIn, boxHeightIn);
                break;
            default:
                console.warn(`Unsupported KPI layout_type: ${detailJson.layout_type}`);
                renderSimpleKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, [{ value: 'N/A', description: 'Unsupported layout' }], boxXIn, yPosIn, boxWidthIn, boxHeightIn);
        }
    }

    function renderSimpleKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, items, boxXIn, boxYIn, boxWidthIn, boxHeightIn) {
        const item = items && items.length > 0 ? items[0] : { value: 'N/A', description: '' };
        const valueText = item.value || '0';
        const descriptionText = item.description || '';

        const titleXIn = boxXIn + (0.23 / 2.54);
        const titleYIn = boxYIn + (0.16 / 2.54);
        const titleWIn = (4.39 / 2.54);
        const titleHIn = boxHeightIn - (0.32 / 2.54);

        slide.addText(kpiName, {
            x: titleXIn, y: titleYIn, w: titleWIn, h: titleHIn,
            fontSize: 8, bold: true, underline: true, fontFace: defaultFont,
            color: themeColors.text, align: 'center', valign: 'middle'
        });

        const valueXIn = titleXIn + titleWIn + (0.23 / 2.54);
        const valueYIn = boxYIn + (0.16 / 2.54);
        const valueWIn = (1.74 / 2.54);
        const valueHIn = boxHeightIn - (0.32 / 2.54);

        let valueOptions = {
            x: valueXIn, y: valueYIn, w: valueWIn, h: valueHIn,
            fontSize: 30, bold: true, fontFace: defaultFont,
            color: '4472C4', align: 'center', valign: 'middle'
        };

        if (String(valueText).includes('%')) {
            const numericValue = String(valueText).replace('%', '');
            slide.addText(numericValue, valueOptions);
            slide.addText('%', {
                x: valueXIn + valueWIn - (0.4 / 2.54),
                y: valueYIn + (0.15 / 2.54),
                w: (0.4 / 2.54), h: (0.5 / 2.54),
                fontSize: 18, bold: true, fontFace: defaultFont,
                color: '4472C4', align: 'left', valign: 'top'
            });
        } else {
            slide.addText(valueText, valueOptions);
        }

        const descXIn = valueXIn + valueWIn + (0.23 / 2.54);
        const descYIn = boxYIn + (0.16 / 2.54);
        const descWIn = boxWidthIn - (titleWIn + valueWIn + (0.92 / 2.54));
        const descHIn = boxHeightIn - (0.32 / 2.54);

        slide.addText(descriptionText, {
            x: descXIn, y: descYIn, w: descWIn, h: descHIn,
            fontSize: 8, fontFace: defaultFont, color: themeColors.text,
            align: 'center', valign: 'middle', breakLine: true
        });
    }

    function renderDetailedListKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, items, boxXIn, boxYIn, boxWidthIn, boxHeightIn) {
        slide.addText(kpiName, {
            x: boxXIn + 0.1, y: boxYIn + 0.05, w: boxWidthIn - 0.2, h: 0.2,
            fontSize: 10, bold: true, underline: true, fontFace: defaultFont,
            color: themeColors.primary, align: 'left', valign: 'top'
        });

        let currentY = boxYIn + 0.25;
        const itemHeight = 0.15;
        const maxItemY = boxYIn + boxHeightIn - 0.05;

        if (items && items.length > 0) {
            items.forEach((item, index) => {
                if (currentY + itemHeight > maxItemY) return;

                let itemText = [];
                if (item.label) {
                    itemText.push({ text: item.label + (item.value ? ': ' : ''), options: { fontSize: 8, bold: true, fontFace: defaultFont, color: themeColors.text } });
                }
                if (item.value) {
                    itemText.push({ text: String(item.value), options: { fontSize: 8, fontFace: defaultFont, color: themeColors.secondary } });
                }
                if (item.description) {
                    itemText.push({ text: ` (${item.description})`, options: { fontSize: 7, italic: true, fontFace: defaultFont, color: themeColors.lightText } });
                }
                
                if (itemText.length > 0) {
                     slide.addText(itemText, {
                        x: boxXIn + 0.2,
                        y: currentY,
                        w: boxWidthIn - 0.4,
                        h: itemHeight,
                        fontFace: defaultFont,
                        bullet: { type: 'bullet', code: '2022' },
                        lineSpacing: 10,
                        valign: 'top'
                    });
                    currentY += itemHeight + 0.02;
                }
            });
        } else {
            slide.addText('No details available.', {
                x: boxXIn + 0.2, y: currentY, w: boxWidthIn - 0.4, h: itemHeight,
                fontSize: 8, fontFace: defaultFont, color: themeColors.lightText, align: 'center'
            });
        }
    }

    function renderComparisonKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, items, boxXIn, boxYIn, boxWidthIn, boxHeightIn) {
        slide.addText(kpiName, {
            x: boxXIn + 0.1, y: boxYIn + 0.05, w: boxWidthIn - 0.2, h: 0.2,
            fontSize: 10, bold: true, underline: true, fontFace: defaultFont,
            color: themeColors.primary, align: 'center', valign: 'top'
        });

        const itemAreaY = boxYIn + 0.25;
        const itemAreaH = boxHeightIn - 0.3;
        const numItems = items ? items.length : 0;
        const itemWidth = numItems > 0 ? (boxWidthIn - 0.2) / numItems : boxWidthIn - 0.2;

        if (items && items.length > 0) {
            items.forEach((item, index) => {
                const itemX = boxXIn + 0.1 + (index * itemWidth);
                
                if (item.label) {
                    slide.addText(item.label, {
                        x: itemX, y: itemAreaY, w: itemWidth, h: 0.15,
                        fontSize: 8, bold: true, fontFace: defaultFont,
                        color: themeColors.text, align: 'center', valign: 'top'
                    });
                }

                if (item.value) {
                    slide.addText(String(item.value), {
                        x: itemX, y: itemAreaY + 0.15, w: itemWidth, h: 0.2,
                        fontSize: 18, bold: true, fontFace: defaultFont,
                        color: themeColors.secondary, align: 'center', valign: 'middle'
                    });
                }

                if (item.description) {
                    slide.addText(item.description, {
                        x: itemX, y: itemAreaY + 0.35, w: itemWidth, h: 0.1,
                        fontSize: 7, italic: true, fontFace: defaultFont,
                        color: themeColors.lightText, align: 'center', valign: 'top'
                    });
                }
            });
        } else {
            slide.addText('No comparison data available.', {
                x: boxXIn + 0.1, y: itemAreaY, w: boxWidthIn - 0.2, h: itemAreaH,
                fontSize: 8, fontFace: defaultFont, color: themeColors.lightText, align: 'center'
            });
        }
    }
    
    function createErrorKpiBox(slide, pptx, themeColors, defaultFont, errorMessage, boxIndex = 0) {
        const baseVerticalPosCm = 7.53;
        const verticalSpacingCm = 1.8;
        const kpiBoxHeightCm = 1.57;

        const yPosIn = (baseVerticalPosCm + (boxIndex * verticalSpacingCm)) / 2.54;
        const boxWidthIn = 11.0 / 2.54;
        const boxHeightIn = kpiBoxHeightCm / 2.54;
        const boxXIn = 22.58 / 2.54;

        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxXIn, y: yPosIn, w: boxWidthIn, h: boxHeightIn,
            fill: { color: 'FDECEA' },
            line: { color: themeColors.redStatus, width: 1 }
        });

        slide.addText(errorMessage, {
            x: boxXIn + 0.1, y: yPosIn + 0.1, w: boxWidthIn - 0.2, h: boxHeightIn - 0.2,
            fontSize: 8, fontFace: defaultFont, color: themeColors.redStatus,
            align: 'center', valign: 'middle',
            breakLine: true
        });
    }

    /**
     * Create the first KPI box with formatted title, value and description
     * @param {Object} slide - The slide to add the KPI box to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @param {Object} kpiData - The KPI data (title, value, description)
     */
    function createKpi1Box(slide, pptx, themeColors, defaultFont, kpiData) {
        const verticalSpacing = 0;
        const baseVerticalPos = 7.53;
        const boxDimensions = {
            w: 11.0 / 2.54,
            h: 1.57 / 2.54,
            x: 22.58 / 2.54,
            y: baseVerticalPos / 2.54
        };
        
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x,
            y: boxDimensions.y,
            w: boxDimensions.w,
            h: boxDimensions.h,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        slide.addText(kpiData.name || kpiData.title || '', {
            x: 22.81 / 2.54,
            y: (7.69 + verticalSpacing) / 2.54,
            w: 4.39 / 2.54,
            h: 1.41 / 2.54,
            fontSize: 8,
            bold: true,
            underline: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center',
            valign: 'middle'
        });
        
        let valueText = kpiData.value || '0';
        let valueOptions = {
            x: 27.21 / 2.54,
            y: (7.73 + verticalSpacing) / 2.54,
            w: 1.74 / 2.54,
            h: 1.2 / 2.54,
            fontSize: 30,
            bold: true,
            fontFace: defaultFont,
            color: '4472C4',
            align: 'center', 
            valign: 'middle'
        };
        
        if (valueText.includes('%')) {
            const numericValue = valueText.replace('%', '');
            slide.addText(numericValue, valueOptions);
            slide.addText('%', {
                x: (27.21 + 0.9) / 2.54,
                y: (7.73 + 0.15 + verticalSpacing) / 2.54,
                w: 0.4 / 2.54,
                h: 0.5 / 2.54,
                fontSize: 18,
                bold: true,
                fontFace: defaultFont,
                color: '4472C4',
                align: 'left',
                valign: 'top'
            });
        } else {
            slide.addText(valueText, valueOptions);
        }
        
        slide.addText(kpiData.description || '', {
            x: 29.51 / 2.54,
            y: (7.78 + verticalSpacing) / 2.54,
            w: 3.33 / 2.54,
            h: 1.2 / 2.54,
            fontSize: 8,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center', 
            valign: 'middle',
            breakLine: true
        });
    }
    
    /**
     * Create the second KPI box with formatted title, value and description
     * @param {Object} slide - The slide to add the KPI box to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @param {Object} kpiData - The KPI data (title, value, description)
     */
    function createKpi2Box(slide, pptx, themeColors, defaultFont, kpiData) {
        const verticalSpacing = 1.8;
        const baseVerticalPos = 7.53;
        const boxDimensions = {
            w: 11.0 / 2.54,
            h: 1.57 / 2.54,
            x: 22.58 / 2.54,
            y: (baseVerticalPos + verticalSpacing) / 2.54
        };
        
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x,
            y: boxDimensions.y,
            w: boxDimensions.w,
            h: boxDimensions.h,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        slide.addText(kpiData.name || kpiData.title || '', {
            x: 22.81 / 2.54,
            y: (7.69 + verticalSpacing) / 2.54,
            w: 4.39 / 2.54,
            h: 1.41 / 2.54,
            fontSize: 8,
            bold: true,
            underline: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center',
            valign: 'middle'
        });
        
        let valueText = kpiData.value || '0';
        let valueOptions = {
            x: 27.21 / 2.54,
            y: (7.73 + verticalSpacing) / 2.54,
            w: 1.74 / 2.54,
            h: 1.2 / 2.54,
            fontSize: 30,
            bold: true,
            fontFace: defaultFont,
            color: '4472C4',
            align: 'center', 
            valign: 'middle'
        };
        
        if (valueText.includes('%')) {
            const numericValue = valueText.replace('%', '');
            slide.addText(numericValue, valueOptions);
            slide.addText('%', {
                x: (27.21 + 0.9) / 2.54,
                y: (7.73 + 0.15 + verticalSpacing) / 2.54,
                w: 0.4 / 2.54,
                h: 0.5 / 2.54,
                fontSize: 18,
                bold: true,
                fontFace: defaultFont,
                color: '4472C4',
                align: 'left',
                valign: 'top'
            });
        } else {
            slide.addText(valueText, valueOptions);
        }
        
        slide.addText(kpiData.description || '', {
            x: 29.51 / 2.54,
            y: (7.78 + verticalSpacing) / 2.54,
            w: 3.33 / 2.54,
            h: 1.2 / 2.54,
            fontSize: 8,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center', 
            valign: 'middle',
            breakLine: true
        });
    }
    
    /**
     * Create the third KPI box with formatted title, value and description
     * @param {Object} slide - The slide to add the KPI box to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @param {Object} kpiData - The KPI data (title, value, description)
     */
    function createKpi3Box(slide, pptx, themeColors, defaultFont, kpiData) {
        const verticalSpacing = 3.6;
        const baseVerticalPos = 7.53;
        const boxDimensions = {
            w: 11.0 / 2.54,
            h: 1.57 / 2.54,
            x: 22.58 / 2.54,
            y: (baseVerticalPos + verticalSpacing) / 2.54
        };
        
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x,
            y: boxDimensions.y,
            w: boxDimensions.w,
            h: boxDimensions.h,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        slide.addText(kpiData.name || kpiData.title || '', {
            x: 22.81 / 2.54,
            y: (7.69 + verticalSpacing) / 2.54,
            w: 4.39 / 2.54,
            h: 1.41 / 2.54,
            fontSize: 8,
            bold: true,
            underline: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center',
            valign: 'middle'
        });
        
        let valueText = kpiData.value || '0';
        let valueOptions = {
            x: 27.21 / 2.54,
            y: (7.73 + verticalSpacing) / 2.54,
            w: 1.74 / 2.54,
            h: 1.2 / 2.54,
            fontSize: 30,
            bold: true,
            fontFace: defaultFont,
            color: '4472C4',
            align: 'center', 
            valign: 'middle'
        };
        
        if (valueText.includes('%')) {
            const numericValue = valueText.replace('%', '');
            slide.addText(numericValue, valueOptions);
            slide.addText('%', {
                x: (27.21 + 0.9) / 2.54,
                y: (7.73 + 0.15 + verticalSpacing) / 2.54,
                w: 0.4 / 2.54,
                h: 0.5 / 2.54,
                fontSize: 18,
                bold: true,
                fontFace: defaultFont,
                color: '4472C4',
                align: 'left',
                valign: 'top'
            });
        } else {
            slide.addText(valueText, valueOptions);
        }
        
        slide.addText(kpiData.description || '', {
            x: 29.51 / 2.54,
            y: (7.78 + verticalSpacing) / 2.54,
            w: 3.33 / 2.54,
            h: 1.2 / 2.54,
            fontSize: 8,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center', 
            valign: 'middle',
            breakLine: true
        });
    }

    return {
        getThemeColors,
        getDefaultFont,
        defineReportMaster,
        getSlideObjects,
        createColorIndicator,
        createTextBox,
        createChartContainer,
        createTotalValueBox,
        createChartTitle,
        getLineChartOptions,
        createSectorBox,
        addSectorIcon,
        addSectorText,
        createMudenrBox,
        addMudenrOutcomes,
        createQuarterBox,
        addLegendTitle,
        addLegendItem,
        addYearIndicator,
        createDraftText,
        createKpiBox,
        createErrorKpiBox,
        createKpi1Box, 
        createKpi2Box,
        createKpi3Box
    };
})();