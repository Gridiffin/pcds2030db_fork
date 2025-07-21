const { validateUsernameOrEmail, validatePassword } = require('../../assets/js/shared/loginLogic');

describe('Username and Email Validation', () => {
    test('valid email passes', () => {
        expect(validateUsernameOrEmail('test@example.com')).toBe(true);
        expect(validateUsernameOrEmail('user.name+tag@domain.co.uk')).toBe(true);
        expect(validateUsernameOrEmail('simple@test.com')).toBe(true);
    });

    test('invalid email passes as username', () => {
        expect(validateUsernameOrEmail('bademail')).toBe(true); // valid as username
        expect(validateUsernameOrEmail('user@')).toBe(true); // valid as username
        expect(validateUsernameOrEmail('@domain.com')).toBe(true); // valid as username
    });

    test('empty input fails', () => {
        expect(validateUsernameOrEmail('')).toBe(false);
        expect(validateUsernameOrEmail('   ')).toBe(false); // whitespace only
        expect(validateUsernameOrEmail(null)).toBe(false);
        expect(validateUsernameOrEmail(undefined)).toBe(false);
    });

    test('valid username passes', () => {
        expect(validateUsernameOrEmail('john_doe')).toBe(true);
        expect(validateUsernameOrEmail('user123')).toBe(true);
        expect(validateUsernameOrEmail('testuser')).toBe(true);
        expect(validateUsernameOrEmail('a')).toBe(true); // single character
    });

    test('username with spaces passes', () => {
        expect(validateUsernameOrEmail('john doe')).toBe(true);
        expect(validateUsernameOrEmail('first last')).toBe(true);
    });

    test('special characters in username', () => {
        expect(validateUsernameOrEmail('user-name')).toBe(true);
        expect(validateUsernameOrEmail('user.name')).toBe(true);
        expect(validateUsernameOrEmail('user123!')).toBe(true);
    });
});

describe('Password Validation', () => {
    test('password length valid', () => {
        expect(validatePassword('12345678')).toBe(true);
        expect(validatePassword('abcdefgh')).toBe(true);
        expect(validatePassword('password123')).toBe(true);
        expect(validatePassword('a'.repeat(100))).toBe(true); // very long password
    });

    test('password length invalid', () => {
        expect(validatePassword('short')).toBe(false);
        expect(validatePassword('1234567')).toBe(false); // 7 characters
        expect(validatePassword('')).toBe(false);
        expect(validatePassword('   ')).toBe(false); // whitespace only
    });

    test('password edge cases', () => {
        expect(validatePassword(null)).toBe(false);
        expect(validatePassword(undefined)).toBe(false);
        expect(validatePassword('12345678')).toBe(true); // exactly 8 characters
    });

    test('password with special characters', () => {
        expect(validatePassword('pass!@#$')).toBe(true);
        expect(validatePassword('MyP@ssw0rd')).toBe(true);
        expect(validatePassword('simple password')).toBe(true); // with space
    });
}); 