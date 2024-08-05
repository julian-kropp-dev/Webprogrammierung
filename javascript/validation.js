/*
This script validates email addresses and password strength from the registration form.
Pswds has to be at least 8 characters, containing uppercase, lowercase, numbers, and special characters
If validation fails, it displays appropriate error messages; otherwise, it allows the form to be submitted.
 */

document.addEventListener('DOMContentLoaded', () => {
    // email validation
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    // password strength validation
    function validatePassword(password) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasNonalphas = /\W/.test(password);
        return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers && hasNonalphas;
    }

    // error handling
    function showError(input, message) {
        const formGroup = input.parentElement;
        const errorElement = formGroup.querySelector('.error');
        if (errorElement) {
            errorElement.innerText = message;
        } else {
            const error = document.createElement('p');
            error.className = 'error';
            error.innerText = message;
            formGroup.appendChild(error);
        }
    }

    function removeError(input) {
        const formGroup = input.parentElement;
        const errorElement = formGroup.querySelector('.error');
        if (errorElement) {
            formGroup.removeChild(errorElement);
        }
    }

    // event listeners
    const registerForm = document.getElementById('register-form');

    registerForm.addEventListener('submit', function (e) {
        const emailInput = document.getElementById('reg-email');
        const passwordInput = document.getElementById('reg-password');
        const confirmPasswordInput = document.getElementById('confirm-password');

        let valid = true;

        if (!validateEmail(emailInput.value)) {
            showError(emailInput, 'Ungültige E-Mail-Adresse.');
            valid = false;
        } else {
            removeError(emailInput);
        }

        if (!validatePassword(passwordInput.value)) {
            showError(passwordInput, 'Passwort muss mindestens 8 Zeichen lang sein und Großbuchstaben, Kleinbuchstaben, Zahlen und Sonderzeichen enthalten.');
            valid = false;
        } else {
            removeError(passwordInput);
        }

        if (passwordInput.value !== confirmPasswordInput.value) {
            showError(confirmPasswordInput, 'Passwörter stimmen nicht überein.');
            valid = false;
        } else {
            removeError(confirmPasswordInput);
        }

        if (!valid) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });
});
