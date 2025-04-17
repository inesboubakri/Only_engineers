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
    
    // Form submission
    const signinForm = document.getElementById('signinForm');
    
    if (signinForm) {
        signinForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('rememberMe').checked;
            
            // Validate form
            if (!email || !password) {
                alert('Please enter both email and password.');
                return;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return;
            }
            
            // Here you would typically send the data to your server for authentication
            // For now, we'll just simulate a successful login
            
            // If remember me is checked, you could set a cookie or localStorage item here
            if (rememberMe) {
                // For demonstration purposes only - in a real app, you'd use a more secure method
                localStorage.setItem('rememberedEmail', email);
            }
            
            // Show success message
            alert('Sign in successful! Welcome back to OnlyEngineers!');
            
            // Redirect to home page (you can change this to wherever you want)
            window.location.href = 'home.html';
        });
    }
    
    // Check if there's a remembered email and pre-fill the form
    const rememberedEmail = localStorage.getItem('rememberedEmail');
    if (rememberedEmail) {
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.value = rememberedEmail;
            document.getElementById('rememberMe').checked = true;
        }
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
    const forgotPasswordLink = document.getElementById('forgotPassword');
    
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            
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
