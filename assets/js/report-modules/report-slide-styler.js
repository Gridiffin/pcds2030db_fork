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
            w: (1.37 / 2.54), // Adjusted width to 1.37cm
            h: (0.74 / 2.54), // Adjusted height to 0.74cm
            fontSize: 9,
            bold: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'left',
            valign: 'middle'
        });
        
        // Add value on the right
        const valueTextW_inch = (2.97 / 2.54); // Width of the valueText text box in inches

        slide.addText(valueText, {
            // To make the valueText's right edge align with the parent box's right edge (x + boxWidth):
            // The left edge of valueText (its x property) must be: (parent box right edge) - (valueText width)
            x: (x + boxWidth) - valueTextW_inch,
            y: y + 0.03,
            w: valueTextW_inch, // Set the width of the text box
            h: boxHeight - 0.06,
            fontSize: 7, 
            bold: true,
            fontFace: defaultFont,
            color: themeColors.accent1,
            align: 'right', // Text content will align to the right of this text box
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
     * @param {Object} container - The container dimensions {x, y, w, h}
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @returns {Object} The chart options
     */
    function getLineChartOptions(container, themeColors, defaultFont) {
        const chartX = container.x + 0.1;   // Reduced left margin
        const chartY = container.y + 0.35;  // Less space for title to maximize chart area
        const chartW = container.w - 0.2;   // Wider chart with minimal horizontal padding
        const chartH = container.h - 0.65;  // Slightly more space at bottom for the larger total boxes
        
        // Default chart colors, can be overridden by specific chart calls
        let defaultChartColors = ['0070C0', 'ED7D31', 'A5A5A5', 'FFC000', '4472C4', '70AD47'];
        if (themeColors.chartSeriesColors && themeColors.chartSeriesColors.length > 0) {
            defaultChartColors = themeColors.chartSeriesColors;
        }

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
            chartColors: defaultChartColors, // Use dynamic/default series colors
            showMarker: true,
            markerSize: 5,                  // Slightly larger markers
            valAxisMaxVal: 400000000,       // Default, should be overridden by specific chart logic if needed
            valAxisMinVal: 0,
            valAxisMajorUnit: 50000000,     // Default, should be overridden
            catAxisLabelFontSize: 8,        // Smaller font for axis labels
            valAxisLabelFontSize: 8,        // Smaller font for axis labels
            valAxisLabelFontFace: defaultFont,
            catAxisLabelFontFace: defaultFont,
            catAxisLabelRotate: 45,
            valAxisLabelFormatCode: '#,##0', // Format numbers with commas, no decimals
            showCatAxisTitle: false,
            showValAxisTitle: false,
            showHorizGridLines: true,
            lineWidthGrid: 0.5,             // Thinner grid lines
            showVertGridLines: false,
            border: { pt: 0, color: 'FFFFFF' } // No border around the chart
        };
    }

    /**
     * Calculate a reasonable major unit for a chart axis based on its max value.
     * @param {number} maxValue - The maximum value on the axis.
     * @returns {number} A suitable major unit.
     */
    function calculateMajorUnit(maxValue) {
        if (maxValue <= 0) return 10; // Default for zero or negative max
        const magnitude = Math.pow(10, Math.floor(Math.log10(maxValue)));
        const mostSignificantDigit = Math.floor(maxValue / magnitude);

        if (mostSignificantDigit < 2) return magnitude / 5; // e.g., max 150, unit 20
        if (mostSignificantDigit < 5) return magnitude / 2; // e.g., max 350, unit 50
        return magnitude; // e.g., max 750, unit 100
    }

    /**
     * Get an array of distinct colors for chart series.
     * @param {number} numSeries - The number of series that need colors.
     * @returns {Array<string>} An array of hex color codes.
     */
    function getChartSeriesColors(numSeries) {
        const defaultColors = [
            '0072C6', // Blue
            'ED7D31', // Orange
            'A5A5A5', // Grey
            'FFC000', // Gold
            '4472C4', // Light Blue
            '70AD47', // Green
            '255E91', // Darker Blue
            '9E480E', // Darker Orange
            '636363', // Darker Grey
            'BF8F00'  // Darker Gold
        ];
        if (numSeries <= defaultColors.length) {
            return defaultColors.slice(0, numSeries);
        }
        // If more colors are needed, you might want to generate them or repeat with slight variations
        // For now, just repeat the list if numSeries is too large
        const colors = [];
        for (let i = 0; i < numSeries; i++) {
            colors.push(defaultColors[i % defaultColors.length]);
        }
        return colors;
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
        // Define positioning and dimensions in cm
        const firstKpiHorizontalPosCm = 22.75;
        const kpiBoxHeightCm = 1.57;    // Height of a single KPI box in cm
        const kpiBoxWidthCm = 11.0;     // Width of a single KPI box in cm

        let yPosCm;
        if (boxIndex === 0) {
            yPosCm = 7.64; // Vertical position for the first box
        } else if (boxIndex === 1) {
            yPosCm = 9.31; // Vertical position for the second box
        } else if (boxIndex === 2) {
            yPosCm = 10.97; // Vertical position for the third box
        } else {
            // Fallback for any additional boxes, though typically only 3 are expected
            // This maintains a consistent spacing based on the last defined gap if more than 3 KPIs were somehow processed.
            const previousBoxYPosCm = 10.97;
            const verticalSpacingCm = 1.5; // Default spacing if more than 3 boxes
            yPosCm = previousBoxYPosCm + ((boxIndex - 2) * (kpiBoxHeightCm + verticalSpacingCm));
        }

        // Convert cm to inches for PptxGenJS
        const yPosIn = yPosCm / 2.54;
        const xPosIn = firstKpiHorizontalPosCm / 2.54;
        const boxWidthIn = kpiBoxWidthCm / 2.54;
        const boxHeightIn = kpiBoxHeightCm / 2.54;

        slide.addShape(pptx.shapes.RECTANGLE, {
            x: xPosIn,
            y: yPosIn,
            w: boxWidthIn,
            h: boxHeightIn,
            fill: { color: 'FFFFFF' }, // White background
            line: { color: themeColors.primary, width: 1 } // Border
        });

        switch (detailJson.layout_type) {
            case 'simple':
                renderSimpleKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, detailJson.items, xPosIn, yPosIn, boxWidthIn, boxHeightIn);
                break;
            case 'detailed_list':
                renderDetailedListKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, detailJson.items, xPosIn, yPosIn, boxWidthIn, boxHeightIn);
                break;
            case 'comparison':
                renderComparisonKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, detailJson.items, xPosIn, yPosIn, boxWidthIn, boxHeightIn);
                break;
            default:
                console.warn(`Unsupported KPI layout_type: ${detailJson.layout_type}`);
                renderSimpleKpiLayout(slide, pptx, themeColors, defaultFont, kpiName, [{ value: 'N/A', description: 'Unsupported layout' }], xPosIn, yPosIn, boxWidthIn, boxHeightIn);
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
        const boxPadding = 0.05; // Padding inside the main KPI box

        // --- 1. KPI Name Column (Left Side) ---
        const kpiNameW = boxWidthIn * 0.40; 
        const kpiNameX = boxXIn + boxPadding;
        const kpiNameY = boxYIn + boxPadding;
        const kpiNameH = boxHeightIn - (2 * boxPadding);

        slide.addText(kpiName || '', {
            x: kpiNameX,
            y: kpiNameY,
            w: kpiNameW,
            h: kpiNameH,
            fontSize: 8,
            bold: true,
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'left',
            valign: 'top',
            breakLine: true
        });

        // --- 2. Data Area (Right Side for Value/Label/Description Sets) ---
        const dataAreaX = kpiNameX + kpiNameW + boxPadding;
        const dataAreaW = boxWidthIn - kpiNameW - (3 * boxPadding);
        const dataAreaY = boxYIn + boxPadding;
        const dataAreaH = boxHeightIn - (2 * boxPadding);

        const displayItems = items && items.length ? items.slice(0, 2) : [];

        if (displayItems.length > 0) {
            const numDataRows = displayItems.length;
            const dataRowH = dataAreaH / numDataRows; 

            displayItems.forEach((item, index) => {
                const currentRowY = dataAreaY + (index * dataRowH);
                const itemInternalPadding = 0.03;

                const valueColW = dataAreaW * 0.35; 
                const textStackColX = dataAreaX + valueColW + itemInternalPadding;
                const textStackColW = dataAreaW - valueColW - itemInternalPadding;

                let valueText = item.value || '0';
                const valueFontSize = 18;
                const percentFontSize = 10;
                const valueColor = themeColors.secondary || '4472C4';
                
                const valueElX = dataAreaX; // Base X for the value area
                const valueElY = currentRowY + itemInternalPadding;
                const valueElH = dataRowH - (2 * itemInternalPadding);

                if (String(valueText).includes('%')) {
                    const numericValue = String(valueText).replace('%', '');
                    const percentSymbol = '%';
                    
                    // Estimate width for numeric part. This might need fine-tuning based on typical value lengths.
                    const numericPartWEst = valueColW * 0.70; // Estimate 70% for number
                    const percentSymbolW = valueColW * 0.30; // Estimate 30% for symbol

                    // Add numeric part
                    slide.addText(numericValue, {
                        x: valueElX,
                        y: valueElY,
                        w: numericPartWEst,
                        h: valueElH,
                        fontSize: valueFontSize,
                        bold: true,
                        fontFace: defaultFont,
                        color: valueColor,
                        align: 'right', 
                        valign: 'middle'
                    });

                    // Add percent symbol
                    slide.addText(percentSymbol, {
                        x: valueElX + numericPartWEst - (0.02 / 2.54), // Position it slightly overlapping or very close to the number
                        y: valueElY, // Same Y as numeric part
                        w: percentSymbolW, 
                        h: valueElH, // Same H as numeric part
                        fontSize: percentFontSize,
                        bold: true,
                        fontFace: defaultFont,
                        color: valueColor,
                        align: 'left', 
                        valign: 'middle' 
                    });

                } else {
                    slide.addText(String(valueText), {
                        x: valueElX,
                        y: valueElY,
                        w: valueColW,
                        h: valueElH,
                        fontSize: valueFontSize,
                        bold: true,
                        fontFace: defaultFont,
                        color: valueColor,
                        align: 'center',
                        valign: 'middle'
                    });
                }

                const labelStackH = dataRowH - (2 * itemInternalPadding);
                const labelH = labelStackH * 0.45; 
                const descH = labelStackH * 0.55;  

                const labelY = currentRowY + itemInternalPadding;
                const descY = labelY + labelH; 

                if (item.label) {
                    slide.addText(item.label || '', {
                        x: textStackColX,
                        y: labelY,
                        w: textStackColW,
                        h: labelH,
                        fontSize: 6,
                        bold: true,
                        fontFace: defaultFont,
                        color: themeColors.text,
                        align: 'left',
                        valign: 'top',
                        breakLine: true
                    });
                }

                if (item.description) {
                    slide.addText(item.description || '', {
                        x: textStackColX,
                        y: descY,
                        w: textStackColW,
                        h: descH,
                        fontSize: 6,
                        italic: true, 
                        fontFace: defaultFont,
                        color: themeColors.lightText,
                        align: 'left',
                        valign: 'top',
                        breakLine: true
                    });
                }
            });
        } else {
            slide.addText('No data available.', {
                x: dataAreaX,
                y: dataAreaY,
                w: dataAreaW,
                h: dataAreaH,
                fontSize: 8,
                fontFace: defaultFont,
                color: themeColors.lightText,
                align: 'center',
                valign: 'middle'
            });
        }
    }
    
    function createErrorKpiBox(slide, pptx, themeColors, defaultFont, errorMessage, boxIndex = 0) {
        const baseVerticalPosCm = 9.5;
        const verticalSpacingCm = 1.8;
        const kpiBoxHeightCm = 1.57;

        const yPosIn = (baseVerticalPosCm + (boxIndex * verticalSpacingCm)) / 2.54;
        const boxWidthIn = 3.67 / 2.54;
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
    }    /**
     * Get enhanced line chart options specifically for the Total Degraded Area chart
     * @param {Object} position - The position object {x, y, w, h}
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @param {string} chartTitle - The title of the chart
     * @param {string} chartUnit - The unit for the chart (e.g., 'Ha')
     * @param {number} maxValue - The maximum value in the chart data
     * @returns {Object} The chart options
     */    function getDegradedAreaChartOptions(position, themeColors, defaultFont, chartTitle, chartUnit, maxValue) {
        // Start with base line chart options
        const chartOptions = getLineChartOptions(position, themeColors, defaultFont);
        
        // Override/set specific options for this chart
        chartOptions.showLegend = true;
        chartOptions.legendPos = 'b'; // Legend at the bottom
        chartOptions.catAxisTitle = 'Month';
        chartOptions.valAxisTitle = `${chartTitle} (${chartUnit})`;
        
        // Calculate appropriate axis values based on actual data (dynamic scaling)
        chartOptions.valAxisMinVal = 0;
        if (maxValue > 0) {
            chartOptions.valAxisMaxVal = maxValue * 1.1; // Add 10% buffer
            chartOptions.valAxisMajorUnit = calculateMajorUnit(maxValue * 1.1);
        } else {
            // Default values if no data
            chartOptions.valAxisMaxVal = 1000;
            chartOptions.valAxisMajorUnit = 200;
        }
        chartOptions.valAxisLabelFormatCode = '#,##0'; // Format numbers with commas, no decimals
        
        // Enhanced horizontal grid lines to increase the gap between rows
        chartOptions.showHorizGridLines = true;
        chartOptions.lineWidthGrid = 0.5; 
        chartOptions.gridLineColor = 'D9D9D9'; // Light gray grid lines        // Keep our fixed spacing without further adjustment        chartOptions.valAxisLineColor = 'D9D9D9'; // Match grid line color for consistency
        chartOptions.valAxisLabelFrequency = 1; // Show every y-axis label
        chartOptions.catAxisLabelFontSize = 8; // Smaller font for x-axis labels
        chartOptions.valAxisLabelFontSize = 8; // Smaller font for y-axis labels
        
        // Line specific styles
        chartOptions.showMarker = true;
        chartOptions.markerSize = 5;
        chartOptions.lineSize = 2.5;
        chartOptions.lineSmooth = false;
        
        return chartOptions;
    }

    /**
     * Add a Total Degraded Area chart to the slide with optimal styling
     * @param {Object} slide - The slide to add the chart to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     * @param {Object} chartApiData - The chart data from API
     * @param {Object} position - The position object for the chart
     */
    function addTotalDegradedAreaChart(slide, pptx, themeColors, defaultFont, chartApiData, position) {
        console.log("Adding Total Degraded Area chart with data:", chartApiData);
        if (!chartApiData || !chartApiData.data || !chartApiData.data.labels) {
            console.error('Total Degraded Area chart data from API is missing or malformed.', chartApiData);
            createTextBox(slide, 'Total Degraded Area data unavailable.', { 
                x: position.x, y: position.y, w: position.w, h: 0.5, 
                fontFace: defaultFont, fontSize: 10, color: themeColors.redStatus || 'FF0000', 
                align: 'center', valign: 'middle' 
            });
            return;
        }

        const degradedAreaMetricData = chartApiData.data;
        const chartTitle = chartApiData.title || 'Total Degraded Area';
        const chartUnit = degradedAreaMetricData.units || 'Ha';

        const yearsToShow = ['2022', '2023', '2024'];
        const monthLabels = degradedAreaMetricData.labels || ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        
        const chartDataSeries = [];

        // Define colors for the series
        const seriesColors = getChartSeriesColors(yearsToShow.length);
        let colorIndex = 0;

        yearsToShow.forEach(year => {
            const yearDataKey = `data${year}`;
            if (degradedAreaMetricData.hasOwnProperty(yearDataKey)) {
                const yearValues = degradedAreaMetricData[yearDataKey] || Array(12).fill(0);
                
                // Ensure yearValues is an array of 12 numbers
                const fullYearValues = [...yearValues];
                while (fullYearValues.length < 12) fullYearValues.push(0);
                const clippedYearValues = fullYearValues.slice(0, 12);

                chartDataSeries.push({
                    name: year, // Legend entry for the year
                    labels: monthLabels, // X-axis labels (months)
                    values: clippedYearValues, // Y-axis values for this year
                    opts: { color: seriesColors[colorIndex % seriesColors.length] } // Series-specific color
                });
                colorIndex++;
            } else {
                console.warn(`Data for year ${year} not found in degraded_area_chart data.`);
            }
        });

        if (chartDataSeries.length === 0) {
            console.error('No data series to plot for Total Degraded Area chart.');
            createTextBox(slide, 'No data series for Total Degraded Area.', { 
                x: position.x, y: position.y, w: position.w, h: 0.5, 
                fontFace: defaultFont, fontSize: 10, color: themeColors.text || '000000', 
                align: 'center', valign: 'middle' 
            });
            return;
        }
        
        // Calculate the maximum value for axis scaling
        const allValues = chartDataSeries.flatMap(series => series.values);
        const maxVal = Math.max(...allValues, 0);        // Get optimized chart options for this specific chart with dynamic scaling based on actual data
        const chartOptions = getDegradedAreaChartOptions(position, themeColors, defaultFont, chartTitle, chartUnit, maxVal);
        chartOptions.chartColors = seriesColors.slice(0, chartDataSeries.length);
          // Add chart title with even smaller gap
        createChartTitle(slide, chartTitle, {
            x: position.x, 
            y: position.y - 0.05, // Very minimal gap between title and chart
            w: position.w, 
            h: 0.15 // Reduced height for title to bring it closer to chart
        }, themeColors, defaultFont);

        slide.addChart(pptx.ChartType.line, chartDataSeries, chartOptions);
        console.log("Total Degraded Area chart added to slide.");
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
        calculateMajorUnit,
        getChartSeriesColors,
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
        getDegradedAreaChartOptions,
        addTotalDegradedAreaChart,
        addTimberExportChart
    };

    /**
     * Add a timber export line chart to the slide
     * @param {Object} slide - The slide to populate
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     * @param {string} defaultFont - The default font
     * @param {Object} data - The data from the API
     */
    function addTimberExportChart(slide, pptx, themeColors, defaultFont, data) {
        console.log("Adding timber export line chart with real data");
        
        // Create container using the styler function
        const container = createChartContainer(slide, pptx, themeColors);
        
        // Get current date and extract current year and previous year
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const previousYear = currentYear - 1;
        
        // Add title using the styler function
        createChartTitle(slide, 'Timber Export Value (RM)', container, themeColors, defaultFont);
        
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
        console.log("Maximum monthly value:", maxMonthlyValue);
        
        // Calculate total values for each year
        const previousYearTotal = clippedPreviousYearData.reduce((sum, val) => sum + (val || 0), 0);
        const currentYearTotal = clippedCurrentYearData.reduce((sum, val) => sum + (val || 0), 0);
        
        // For large export values, reduce precision to conserve space while maintaining readability
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
        createTotalValueBox(
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
        createTotalValueBox(
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
        ];        // Get chart options from the styler
        const chartOptions = getLineChartOptions(container, themeColors, defaultFont);
        
        // Calculate appropriate axis values based on actual data
        if (maxMonthlyValue > 0) {
            chartOptions.valAxisMinVal = 0;
            chartOptions.valAxisMaxVal = maxMonthlyValue * 1.1; // Add 10% buffer
            chartOptions.valAxisMajorUnit = calculateMajorUnit(maxMonthlyValue * 1.1);
        }
        
        // Add chart (no need to set axis titles as they've been removed)
        slide.addChart(pptx.ChartType.line || 'line', chartData, chartOptions);
        console.log("Line chart with real data added to slide");
    }
})();