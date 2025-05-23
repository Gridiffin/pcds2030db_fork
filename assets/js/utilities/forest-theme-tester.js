/**
 * PCDS2030 Dashboard - Forest Theme Responsive Testing Utility
 * 
 * This script provides tools for testing the responsive design of the dashboard
 * across different viewport sizes. To use, paste this code into your browser console
 * on any page in the dashboard.
 */

// Self-executing function to avoid polluting global namespace
(function() {
    // Configuration for test view sizes
    const viewportSizes = [
        { name: 'Mobile S', width: 320, height: 568, icon: '📱' },
        { name: 'Mobile M', width: 375, height: 667, icon: '📱' },
        { name: 'Mobile L', width: 425, height: 812, icon: '📱' },
        { name: 'Tablet', width: 768, height: 1024, icon: '📋' },
        { name: 'Laptop', width: 1024, height: 768, icon: '💻' },
        { name: 'Laptop L', width: 1440, height: 900, icon: '💻' },
        { name: 'Desktop', width: 1920, height: 1080, icon: '🖥️' }
    ];

    // CSS for the testing UI
    const css = `
        .ftr-container {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 9999;
            background: rgba(83, 125, 93, 0.95);
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            max-width: 300px;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(158, 188, 138, 0.3);
        }
        .ftr-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 5px;
        }
        .ftr-title {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        .ftr-close {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.7;
        }
        .ftr-close:hover {
            opacity: 1;
        }
        .ftr-info {
            font-size: 12px;
            margin-bottom: 10px;
        }
        .ftr-viewport-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
            max-height: 200px;
            overflow-y: auto;
        }
        .ftr-viewport-item {
            background: rgba(255,255,255,0.1);
            padding: 8px 12px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .ftr-viewport-item:hover {
            background: rgba(255,255,255,0.2);
        }
        .ftr-viewport-name {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ftr-viewport-size {
            font-size: 11px;
            opacity: 0.8;
        }
        .ftr-current {
            font-size: 11px;
            margin-top: 10px;
            text-align: center;
            padding: 5px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        .ftr-grid-toggle {
            margin-top: 10px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s ease;
        }
        .ftr-grid-toggle:hover {
            background: rgba(255,255,255,0.25);
        }
        .ftr-grid {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9998;
            pointer-events: none;
            display: none;
        }
        .ftr-grid-line {
            position: absolute;
            background: rgba(210, 208, 160, 0.2);
        }
        .ftr-grid-line-label {
            position: absolute;
            background: rgba(83, 125, 93, 0.8);
            color: white;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 2px;
        }
    `;

    // Create and append the style element
    const styleEl = document.createElement('style');
    styleEl.textContent = css;
    document.head.appendChild(styleEl);

    // Create the testing UI
    function createTestingUI() {
        const container = document.createElement('div');
        container.className = 'ftr-container';
        container.innerHTML = `
            <div class="ftr-header">
                <h3 class="ftr-title">🌲 Forest Theme Responsive Tester</h3>
                <button class="ftr-close">&times;</button>
            </div>
            <div class="ftr-info">
                Click a size to simulate that viewport:
            </div>
            <div class="ftr-viewport-list">
                ${viewportSizes.map(size => `
                    <div class="ftr-viewport-item" data-width="${size.width}" data-height="${size.height}">
                        <div class="ftr-viewport-name">
                            <span>${size.icon}</span> ${size.name}
                        </div>
                        <div class="ftr-viewport-size">${size.width}×${size.height}</div>
                    </div>
                `).join('')}
            </div>
            <div class="ftr-current">
                Current: ${window.innerWidth}×${window.innerHeight}
            </div>
            <button class="ftr-grid-toggle">Toggle Layout Grid</button>
        `;

        document.body.appendChild(container);

        // Create responsive grid overlay
        const grid = document.createElement('div');
        grid.className = 'ftr-grid';
        document.body.appendChild(grid);

        // Add event listeners
        container.querySelector('.ftr-close').addEventListener('click', () => {
            container.remove();
            grid.remove();
            styleEl.remove();
        });

        container.querySelector('.ftr-grid-toggle').addEventListener('click', () => {
            if (grid.style.display === 'block') {
                grid.style.display = 'none';
            } else {
                grid.style.display = 'block';
                updateGrid();
            }
        });

        container.querySelectorAll('.ftr-viewport-item').forEach(item => {
            item.addEventListener('click', () => {
                const width = parseInt(item.dataset.width);
                const height = parseInt(item.dataset.height);
                
                // We can't actually resize the window, but we can show an alert with instructions
                alert(`To test at ${width}×${height}:\n\n1. Open DevTools (F12)\n2. Toggle device toolbar (Ctrl+Shift+M)\n3. Set dimensions to ${width}×${height}`);
                
                // Update current display as a visual indicator
                container.querySelector('.ftr-current').textContent = `Target: ${width}×${height}`;
            });
        });

        // Update current size on window resize
        window.addEventListener('resize', () => {
            container.querySelector('.ftr-current').textContent = `Current: ${window.innerWidth}×${window.innerHeight}`;
            if (grid.style.display === 'block') {
                updateGrid();
            }
        });
    }

    // Update grid overlay
    function updateGrid() {
        const grid = document.querySelector('.ftr-grid');
        grid.innerHTML = '';

        // Create vertical lines for common breakpoints
        const breakpoints = [320, 375, 425, 576, 768, 992, 1200, 1400];
        
        breakpoints.forEach(width => {
            if (width <= window.innerWidth) {
                const line = document.createElement('div');
                line.className = 'ftr-grid-line';
                line.style.left = `${width}px`;
                line.style.top = '0';
                line.style.width = '1px';
                line.style.height = '100%';
                
                const label = document.createElement('div');
                label.className = 'ftr-grid-line-label';
                label.textContent = `${width}px`;
                label.style.top = '5px';
                label.style.left = `${width + 5}px`;
                
                grid.appendChild(line);
                grid.appendChild(label);
            }
        });
    }

    // Start responsive testing
    createTestingUI();
    console.log('%c🌲 Forest Theme Responsive Tester initialized!', 'color:#537D5D;font-weight:bold;');
    console.log('%cUse this tool to help test responsive design across different viewport sizes.', 'color:#73946B');

})();
