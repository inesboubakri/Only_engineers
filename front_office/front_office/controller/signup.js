// Signup page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePassword = document.querySelector('.toggle-password');
    const passwordField = document.getElementById('password');
    
    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', function() {
            // Toggle password visibility
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle eye icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Form submission
    const signupForm = document.getElementById('signupForm');
    
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            // Temporarily prevent form submission to validate
            e.preventDefault();
            
            // Get form values
            const fullName = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const termsAgree = document.getElementById('termsAgree').checked;
            
            // Clear previous error messages
            clearErrors();
            
            // Validate form
            let isValid = true;
            
            if (!fullName) {
                showError('fullName', 'Full name is required');
                isValid = false;
            } else if (fullName.length < 3) {
                showError('fullName', 'Full name must be at least 3 characters');
                isValid = false;
            }
            
            // Basic email validation
            if (!email) {
                showError('email', 'Email address is required');
                isValid = false;
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showError('email', 'Please enter a valid email address');
                    isValid = false;
                }
            }
            
            // Password strength validation
            if (!password) {
                showError('password', 'Password is required');
                isValid = false;
            } else if (password.length < 8) {
                showError('password', 'Password must be at least 8 characters long');
                isValid = false;
            } else {
                // Check for uppercase, lowercase, and number
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                
                if (!hasUppercase || !hasLowercase || !hasNumber) {
                    let errorMsg = 'Password must contain ';
                    const missing = [];
                    
                    if (!hasUppercase) missing.push('an uppercase letter');
                    if (!hasLowercase) missing.push('a lowercase letter');
                    if (!hasNumber) missing.push('a number');
                    
                    errorMsg += missing.join(', ');
                    showError('password', errorMsg);
                    isValid = false;
                }
            }
            
            if (!termsAgree) {
                showError('termsAgree', 'You must agree to the terms of service');
                isValid = false;
            }
            
            // If form is valid, submit it directly to the server
            if (isValid) {
                // Remove the event listener to prevent infinite loop
                const form = e.target;
                form.removeEventListener('submit', arguments.callee);
                form.submit();
            }
        });
    }
    
    // Language selector (for demonstration)
    const languageSelector = document.querySelector('.language-selector');
    
    if (languageSelector) {
        languageSelector.addEventListener('click', function() {
            alert('Language selection feature coming soon!');
        });
    }
    
    // Social signup buttons (for demonstration)
    const socialButtons = document.querySelectorAll('.social-btn');
    
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.classList[1];
            alert(`${platform.charAt(0).toUpperCase() + platform.slice(1)} login coming soon!`);
        });
    });
});

/**
 * Show error message for a specific field
 * @param {string} fieldId - The ID of the field with error
 * @param {string} message - The error message to display
 */
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Add error class to field
    field.classList.add('error');
    
    // Create error message element if it doesn't exist
    let errorElement = document.getElementById(`${fieldId}-error`);
    
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.id = `${fieldId}-error`;
        errorElement.className = 'error-message';
        
        // Insert after the field
        if (fieldId === 'termsAgree') {
            // For checkbox, insert after the label
            const checkboxGroup = field.closest('.checkbox-group');
            checkboxGroup.appendChild(errorElement);
        } else {
            // For other fields, insert after the field
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        }
    }
    
    errorElement.textContent = message;
}

/**
 * Clear all error messages
 */
function clearErrors() {
    // Remove error class from all fields
    const fields = document.querySelectorAll('.form-group input');
    fields.forEach(field => field.classList.remove('error'));
    
    // Remove all error message elements
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(element => element.remove());
}

/**
 * Check for errors messages from server
 */
function checkForErrors() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    
    if (error) {
        // Create error banner
        const errorBanner = document.createElement('div');
        errorBanner.className = 'error-banner';
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.className = 'close-btn';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', function() {
            errorBanner.remove();
        });
        
        errorBanner.appendChild(closeBtn);
        
        // Set error message based on error code
        let errorMessage = '';
        
        switch (error) {
            case 'email_exists':
                errorMessage = 'This email address is already registered. Please use a different email or sign in.';
                break;
            case 'invalid_email':
                errorMessage = 'Please enter a valid email address.';
                break;
            case 'weak_password':
                errorMessage = 'Password is too weak. It should be at least 8 characters with uppercase, lowercase, and numbers.';
                break;
            default:
                errorMessage = 'An error occurred. Please try again.';
        }
        
        // Create error message element
        const errorText = document.createElement('p');
        errorText.textContent = errorMessage;
        errorBanner.appendChild(errorText);
        
        // Add to page
        const formContainer = document.querySelector('.signup-form-container');
        if (formContainer) {
            formContainer.insertBefore(errorBanner, formContainer.firstChild);
        }
    }
}

// Call checkForErrors when page loads
document.addEventListener('DOMContentLoaded', checkForErrors);

/**
 * Setup real-time validation for form fields
 */
function setupRealTimeValidation() {
    const fullNameField = document.getElementById('fullName');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    
    if (fullNameField) {
        fullNameField.addEventListener('blur', function() {
            const value = this.value.trim();
            
            if (value === '') {
                showError('fullName', 'Full name is required');
            } else if (value.length < 3) {
                showError('fullName', 'Full name must be at least 3 characters');
            } else {
                // Clear error
                this.classList.remove('error');
                const errorElement = document.getElementById('fullName-error');
                if (errorElement) errorElement.remove();
            }
        });
    }
    
    if (emailField) {
        emailField.addEventListener('blur', function() {
            const value = this.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value === '') {
                showError('email', 'Email address is required');
            } else if (!emailRegex.test(value)) {
                showError('email', 'Please enter a valid email address');
            } else {
                // Clear error
                this.classList.remove('error');
                const errorElement = document.getElementById('email-error');
                if (errorElement) errorElement.remove();
            }
        });
    }
    
    if (passwordField) {
        passwordField.addEventListener('blur', function() {
            const value = this.value;
            
            if (value === '') {
                showError('password', 'Password is required');
            } else if (value.length < 8) {
                showError('password', 'Password must be at least 8 characters long');
            } else {
                const hasUppercase = /[A-Z]/.test(value);
                const hasLowercase = /[a-z]/.test(value);
                const hasNumber = /[0-9]/.test(value);
                
                if (!hasUppercase || !hasLowercase || !hasNumber) {
                    let errorMsg = 'Password must contain ';
                    const missing = [];
                    
                    if (!hasUppercase) missing.push('an uppercase letter');
                    if (!hasLowercase) missing.push('a lowercase letter');
                    if (!hasNumber) missing.push('a number');
                    
                    errorMsg += missing.join(', ');
                    showError('password', errorMsg);
                } else {
                    // Clear error
                    this.classList.remove('error');
                    const errorElement = document.getElementById('password-error');
                    if (errorElement) errorElement.remove();
                }
            }
        });
    }
}

// Call setupRealTimeValidation when page loads
document.addEventListener('DOMContentLoaded', setupRealTimeValidation);
