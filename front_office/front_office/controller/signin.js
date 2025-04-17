// Signin page functionality
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
    
    // We're now handling form submission via PHP action attribute, 
    // so no need to prevent the default form submission
    
    // Handle "Remember Me" functionality with cookies or localStorage
    const rememberCheckbox = document.getElementById('remember');
    const emailField = document.querySelector('input[name="email"]');
    
    if (rememberCheckbox && emailField) {
        // Check if there's a remembered email and pre-fill the form
        const rememberedEmail = localStorage.getItem('rememberedEmail');
        if (rememberedEmail) {
            emailField.value = rememberedEmail;
            rememberCheckbox.checked = true;
        }
        
        // Store email in localStorage if remember me is checked
        rememberCheckbox.addEventListener('change', function() {
            if (this.checked && emailField.value.trim() !== '') {
                localStorage.setItem('rememberedEmail', emailField.value);
            } else {
                localStorage.removeItem('rememberedEmail');
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
    
    // Social login buttons (for demonstration)
    const socialButtons = document.querySelectorAll('.social-btn');
    
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.classList[1];
            alert(`${platform.charAt(0).toUpperCase() + platform.slice(1)} login coming soon!`);
        });
    });
    
    // Forgot password functionality
    const forgotPasswordLink = document.querySelector('.forgot-password');
    
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            const email = emailField ? emailField.value : '';
            
            if (!email) {
                alert('Please enter your email address in the email field first.');
                return;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return;
            }
            
            // Here you would typically send a password reset email
            // For now, we'll just simulate the process
            
            alert(`Password reset instructions have been sent to ${email}. Please check your inbox.`);
        });
    }
});
