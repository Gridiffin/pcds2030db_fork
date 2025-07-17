const { addTwoNumbers } = require('./addTwoNumbers');

test('adds 1 + 2 to equal 3', () => {
    expect(addTwoNumbers(1, 2)).toBe(3);
});

test('adds -5 + 5 to equal 0', () => {
    expect(addTwoNumbers(-5, 5)).toBe(0);
});

test('adds 0.1 + 0.2 to be close to 0.3', () => {
    expect(addTwoNumbers(0.1, 0.2)).toBeCloseTo(0.3);
}); 