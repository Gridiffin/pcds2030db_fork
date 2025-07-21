export function validateUsernameOrEmail(input) {
    // Handle null, undefined, or empty inputs
    if (!input || typeof input !== 'string') {
        return false;
    }
    
    // Trim whitespace and check if empty
    const trimmed = input.trim();
    if (trimmed.length === 0) {
        return false;
    }
    
    // Accepts either a valid email or a non-empty username
    return /^[^@]+@[^@]+\.[^@]+$/.test(trimmed) || trimmed.length > 0;
}

export function validatePassword(password) {
    // Handle null, undefined, or non-string inputs
    if (!password || typeof password !== 'string') {
        return false;
    }
    
    return password.length >= 8;
} 