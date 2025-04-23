// Signin page functionality
document.addEventListener('DOMContentLoaded', () => {
    const signinForm = document.getElementById('signinForm');
    const errorMessage = document.getElementById('error-message');
    const submitBtn = signinForm.querySelector('button[type="submit"]');

    if (signinForm) {
        signinForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            // Reset error message
            if (errorMessage) {
                errorMessage.style.display = 'none';
                errorMessage.textContent = '';
            }

            // Enhanced validation
            if (!email || !password) {
                showError('Please enter both email and password');
                return;
            }

            // Email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Please enter a valid email address');
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            const originalBtnText = submitBtn.textContent;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

            try {
                const formData = new FormData(signinForm);
                const response = await fetch('../model/process_signin.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                if (data.success) {
                    // Save email in localStorage if remember me is checked
                    const rememberMe = document.getElementById('rememberMe');
                    if (rememberMe && rememberMe.checked) {
                        localStorage.setItem('rememberedEmail', email);
                    } else {
                        localStorage.removeItem('rememberedEmail');
                    }

                    // Redirect to appropriate page
                    window.location.href = data.redirect;
                } else {
                    if (data.show_register) {
                        showError(`${data.message} <a href="signup.html" class="register-link">Sign up now</a>`, true);
                    } else {
                        showError(data.message);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred. Please try again.');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }

    // Function to show error messages
    function showError(message, isHTML = false) {
        if (errorMessage) {
            errorMessage.style.display = 'block';
            if (isHTML) {
                errorMessage.innerHTML = message;
            } else {
                errorMessage.textContent = message;
            }
            errorMessage.classList.add('shake');
            setTimeout(() => errorMessage.classList.remove('shake'), 500);
        }
    }

    // Check for remembered email
    const rememberedEmail = localStorage.getItem('rememberedEmail');
    if (rememberedEmail) {
        const emailField = document.getElementById('email');
        const rememberMeCheckbox = document.getElementById('rememberMe');
        if (emailField && rememberMeCheckbox) {
            emailField.value = rememberedEmail;
            rememberMeCheckbox.checked = true;
        }
    }

    // Add password visibility toggle
    const togglePassword = document.querySelector('.toggle-password');
    const passwordField = document.getElementById('password');
    
    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', () => {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            const icon = togglePassword.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }

    // Language selector
    const languageSelector = document.querySelector('.language-selector');
    if (languageSelector) {
        languageSelector.addEventListener('click', function() {
            alert('Language selection feature coming soon!');
        });
    }
    
    // Social login buttons
    const socialButtons = document.querySelectorAll('.social-btn');
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.classList[1];
            showError(`${platform.charAt(0).toUpperCase() + platform.slice(1)} login coming soon!`);
        });
    });
});
