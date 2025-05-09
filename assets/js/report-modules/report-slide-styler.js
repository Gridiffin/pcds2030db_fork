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
    }    /**
     * Create a chart container with border
     * @param {Object} slide - The slide to add the container to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @returns {Object} The container dimensions {x, y, w, h}
     */    function createChartContainer(slide, pptx, themeColors) {
        // Exact dimensions as specified:
        // Converting from cm to inches: H = 6.13cm = 2.41 inches, W = 10.28cm = 4.05 inches
        // Position: horizontal = 23.3cm = 9.17 inches, vertical = 1.47cm = 0.58 inches
        const container = {
            x: 9.17, // Position from left (23.3cm)
            y: 0.58, // Position from top (1.47cm)
            w: 4.05, // Width in inches (10.28cm)
            h: 2.41  // Height in inches (6.13cm)
        };
        
        // Add container shape
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: container.x, 
            y: container.y, 
            w: container.w, 
            h: container.h,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        return container;
    }
    
    /**
     * Create a chart title
     * @param {Object} slide - The slide to add the title to
     * @param {string} titleText - The title text
     * @param {Object} container - The container dimensions
     * @param {Object} themeColors - The theme colors
     * @param {string} defaultFont - The default font
     */    function createChartTitle(slide, titleText, container, themeColors, defaultFont) {
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
     */    function getLineChartOptions(container, themeColors, defaultFont) {
        // Calculate chart position with minimal padding to maximize chart area
        const chartX = container.x + 0.15;  // Small left margin
        const chartY = container.y + 0.4;   // Space for title
        const chartW = container.w - 0.3;   // Wide chart with minimal horizontal padding
        const chartH = container.h - 0.7;   // Allow space for the total boxes at bottom
        
        return {
            x: chartX, 
            y: chartY, 
            w: chartW, 
            h: chartH,
            chartType: 'line',
            lineSize: 2,
            showTitle: false,
            showValue: false,
            showLegend: false,              // Legend removed as requested (already in footer)
            // Colors matching the footer indicators
            chartColors: ['0070C0', 'ED7D31'], // Blue for current year, Orange for previous year
            showMarker: true,
            markerSize: 4,
            // Axis formatting
            valAxisMaxVal: 400000000,
            valAxisMinVal: 0,
            valAxisMajorUnit: 50000000,
            catAxisLabelFontSize: 8,        // Smaller font for axis labels
            valAxisLabelFontSize: 8,        // Smaller font for axis labels
            valAxisLabelFontFace: defaultFont,
            catAxisLabelFontFace: defaultFont,
            catAxisLabelRotate: 45,
            valAxisLabelFormatCode: '#,##0', // Format numbers with commas
            // Hide axis titles as requested
            showCatAxisTitle: false,
            showValAxisTitle: false,
            // Grid lines
            showHorizGridLines: true,
            showVertGridLines: false
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
        
        // Add the sector box with border
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
            // Add forest icon
            slide.addImage({
                path: iconPath, 
                x: iconDimensions.x, 
                y: iconDimensions.y, 
                w: iconDimensions.w, 
                h: iconDimensions.h
            });
        } catch (e) {
            console.warn('Sector icon not found, using fallback shape', e);
            
            // Fallback: Add a colored shape instead of the image
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
        // Add sector name text - centered, bold, and black
        createTextBox(slide, sectorName, {
            x: 0.79, y: 0.11, w: 2.44, h: 0.41,
            fontSize: 18, bold: true,
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'center',
            valign: 'middle'
        });
        
        // Add export target text below sector name
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
        
        // Add larger box for MUDeNR outcomes
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
        
        // Add quarter section box
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x, 
            y: boxDimensions.y, 
            w: boxDimensions.w, 
            h: boxDimensions.h,
            fill: { color: 'FFFFFF' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add yellow square next to the quarter box with no gap
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: boxDimensions.x + boxDimensions.w, 
            y: boxDimensions.y, 
            w: 0.66, 
            h: boxDimensions.h,
            fill: { color: 'FFFF00' },
            line: { color: themeColors.primary, width: 1 }
        });
        
        // Add quarter information in the box
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
        // Add "Legend:" text
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
        // Add color square
        createColorIndicator(slide, pptx, color, x, y, 0.31, 0.31);
        
        // Add text
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
        // Add circle indicator
        slide.addShape(pptx.shapes.OVAL, {
            x: circleX, y: circleY, w: 0.118, h: 0.118,
            fill: { color: color },
            line: { color: color, width: 0.5 }
        });
        
        // Add year text
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
        // Add Draft text box with red text, bold
        createTextBox(slide, date, {
            x: 9.197, y: 7.098, w: 2.150, h: 0.339,
            fontSize: 14, bold: true,
            fontFace: defaultFont,
            color: 'FF0000',
            align: 'left',
            valign: 'middle'
        });
    }    /**
     * Create a total value box
     * @param {Object} slide - The slide to add the box to
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors
     * @param {string} label - The label for the total box (e.g., "2024 Export")
     * @param {string} value - The total value to display (with RM prefix)
     * @param {number} x - The x position of the box
     * @param {number} y - The y position of the box
     * @param {string} defaultFont - The default font
     */    function createTotalValueBox(slide, pptx, themeColors, label, value, x, y, defaultFont) {
        // More compact boxes that fit within the chart container
        // H = 0.25 inches, W = 1.8 inches - smaller but still readable
        const boxWidth = 1.8;   
        const boxHeight = 0.25; 
        
        // Add container shape with solid white background for better visibility
        slide.addShape(pptx.shapes.RECTANGLE, {
            x: x, 
            y: y, 
            w: boxWidth, 
            h: boxHeight,
            fill: { color: 'FFFFFF' }, // Solid white for better readability
            line: { color: themeColors.primary, width: 0.75 }, // Slightly thicker border
            shadow: { type: 'outer', angle: 45, blur: 2, offset: 1, color: 'CCCCCC' } // Shadow for separation
        });
        
        // Add label text (bold, as requested)
        slide.addText(label, {
            x: x + 0.05, 
            y: y + 0.01, 
            w: boxWidth * 0.35, 
            h: boxHeight - 0.02,
            fontSize: 9, 
            bold: true,
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'left',
            valign: 'middle'
        });
        
        // Add value text (right-aligned to fit large numbers better)
        slide.addText(value, {
            x: x + (boxWidth * 0.35), 
            y: y + 0.01, 
            w: boxWidth * 0.65 - 0.05, 
            h: boxHeight - 0.02,
            fontSize: 8, // Smaller for better fit
            fontFace: defaultFont,
            color: themeColors.text,
            align: 'right',
            valign: 'middle'
        });
    }

    // Expose public methods
    return {
        getThemeColors,
        getDefaultFont,
        defineReportMaster,
        createColorIndicator,
        createTextBox,
        createChartContainer,
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
        createTotalValueBox
    };
})();