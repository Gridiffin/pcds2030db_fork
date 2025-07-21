/**
 * Jest Setup File
 * Global test configuration and mocks
 */

// Mock Chart.js globally
global.Chart = jest.fn(() => ({
    destroy: jest.fn(),
    update: jest.fn(),
    data: { datasets: [] },
    options: {}
}));

// Mock console methods to reduce test noise
global.console = {
    ...console,
    log: jest.fn(),
    debug: jest.fn(),
    info: jest.fn(),
    warn: jest.fn(),
    error: jest.fn()
};

// Mock localStorage
const localStorageMock = {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn(),
};
global.localStorage = localStorageMock;

// Mock sessionStorage
const sessionStorageMock = {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn(),
};
global.sessionStorage = sessionStorageMock;

// Add TextEncoder and TextDecoder for Node.js compatibility
const { TextEncoder, TextDecoder } = require('util');
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;

// Mock HTMLCanvasElement for Chart.js compatibility
HTMLCanvasElement.prototype.getContext = jest.fn(() => ({
    clearRect: jest.fn(),
    fillRect: jest.fn(),
    getImageData: jest.fn(),
    putImageData: jest.fn(),
    createImageData: jest.fn(),
    setTransform: jest.fn(),
    drawImage: jest.fn(),
    save: jest.fn(),
    fillText: jest.fn(),
    restore: jest.fn(),
    beginPath: jest.fn(),
    moveTo: jest.fn(),
    lineTo: jest.fn(),
    closePath: jest.fn(),
    stroke: jest.fn(),
    translate: jest.fn(),
    scale: jest.fn(),
    rotate: jest.fn(),
    arc: jest.fn(),
    fill: jest.fn(),
    measureText: jest.fn(() => ({ width: 0 })),
    transform: jest.fn(),
    rect: jest.fn(),
    clip: jest.fn(),
}));

// Suppress CSS import warnings
const originalWarn = console.warn;
beforeAll(() => {
    console.warn = (...args) => {
        if (typeof args[0] === 'string' && args[0].includes('CSS import')) {
            return;
        }
        originalWarn(...args);
    };
});

afterAll(() => {
    console.warn = originalWarn;
});
