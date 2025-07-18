export function validateUsernameOrEmail(input) {
    // Accepts either a valid email or a non-empty username
    return /^[^@]+@[^@]+\.[^@]+$/.test(input) || input.length > 0;
}
export function validatePassword(password) {
    return password.length >= 8;
} 