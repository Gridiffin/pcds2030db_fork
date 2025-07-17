// Assumes addTwoNumbers is loaded via <script> before this file

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addForm');
    const num1 = document.getElementById('num1');
    const num2 = document.getElementById('num2');
    const result = document.getElementById('result');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const val1 = parseFloat(num1.value);
        const val2 = parseFloat(num2.value);
        if (isNaN(val1) || isNaN(val2)) {
            result.textContent = 'Please enter valid numbers.';
            result.style.color = '#d32f2f';
            return;
        }
        const sum = window.addTwoNumbers(val1, val2);
        result.textContent = `Result: ${sum}`;
        result.style.color = '#388e3c';
    });
}); 