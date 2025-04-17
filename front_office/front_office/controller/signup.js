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
            e.preventDefault();
            
            // Get form values
            const fullName = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const termsAgree = document.getElementById('termsAgree').checked;
            
            // Validate form
            if (!fullName || !email || !password || !termsAgree) {
                alert('Please fill in all fields and agree to the terms.');
                return;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return;
            }
            
            // Password strength validation (at least 8 characters)
            if (password.length < 8) {
                alert('Password must be at least 8 characters long.');
                return;
            }
            
            // Here you would typically send the data to your server
            // For now, we'll just simulate a successful signup
            
            // Show success message
            alert('Account created successfully! Welcome to OnlyEngineers!');
            
            // Redirect to home page (you can change this to wherever you want)
            window.location.href = 'home.html';
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
