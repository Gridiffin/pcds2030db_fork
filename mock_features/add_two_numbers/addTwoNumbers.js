function addTwoNumbers(a, b) {
    return a + b;
}

// For Node.js and browser
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { addTwoNumbers };
} else {
    window.addTwoNumbers = addTwoNumbers;
} 