const { validateUsernameOrEmail, validatePassword } = require('../../assets/js/shared/loginLogic');

test('valid email passes', () => {
    expect(validateUsernameOrEmail('test@example.com')).toBe(true);
});
test('invalid email fails but valid as username', () => {
    expect(validateUsernameOrEmail('bademail')).toBe(true); // valid as username
});
test('empty input fails', () => {
    expect(validateUsernameOrEmail('')).toBe(false);
});
test('valid username passes', () => {
    expect(validateUsernameOrEmail('john_doe')).toBe(true);
    expect(validateUsernameOrEmail('user123')).toBe(true);
});
test('username with spaces passes', () => {
    expect(validateUsernameOrEmail('john doe')).toBe(true);
});
test('password length valid', () => {
    expect(validatePassword('12345678')).toBe(true);
    expect(validatePassword('abcdefgh')).toBe(true);
});
test('password length invalid', () => {
    expect(validatePassword('short')).toBe(false);
    expect(validatePassword('')).toBe(false);
}); 