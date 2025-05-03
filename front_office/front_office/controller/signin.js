document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded, setting up password reset handlers");
    
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
    
    // Handle "Remember Me" functionality with localStorage
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
    
    // Social login buttons
    const socialButtons = document.querySelectorAll('.social-btn');
    
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.classList[1];
            alert(`${platform.charAt(0).toUpperCase() + platform.slice(1)} login coming soon!`);
        });
    });
    
    // -------- Forgot Password Modal Functionality --------
    // Initialize variables
    const forgotPasswordLink = document.querySelector('.forgot-password');
    const modal = document.getElementById('forgotPasswordModal');
    const closeModal = document.querySelector('.close-modal');
    const backToLoginBtn = document.getElementById('backToLoginBtn');
    
    // Step elements
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const successStep = document.getElementById('success-step');
    
    // Form elements
    const resetEmailInput = document.getElementById('reset-email');
    const verificationCodeInput = document.getElementById('verification-code');
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    
    // Button elements
    const sendCodeBtn = document.getElementById('sendCodeBtn');
    const verifyCodeBtn = document.getElementById('verifyCodeBtn');
    const resetPasswordBtn = document.getElementById('resetPasswordBtn');
    const resendCodeLink = document.getElementById('resendCode');
    
    // Error elements
    const emailError = document.getElementById('email-error');
    const codeError = document.getElementById('code-error');
    const passwordError = document.getElementById('password-error');
    
    // Toggle password visibility for new password fields
    const toggleNewPassword = document.querySelector('.toggle-new-password');
    const toggleConfirmPassword = document.querySelector('.toggle-confirm-password');
    
    // Debug element existence
    console.log("Modal exists:", !!modal);
    console.log("Forgot password link exists:", !!forgotPasswordLink);
    console.log("Send code button exists:", !!sendCodeBtn);
    
    if (toggleNewPassword && newPasswordInput) {
        toggleNewPassword.addEventListener('click', function() {
            togglePasswordVisibility(newPasswordInput, this.querySelector('i'));
        });
    }
    
    if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordInput, this.querySelector('i'));
        });
    }
    
    function togglePasswordVisibility(inputField, iconElement) {
        const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
        inputField.setAttribute('type', type);
        
        iconElement.classList.toggle('fa-eye');
        iconElement.classList.toggle('fa-eye-slash');
    }
    
    // Open modal when forgot password link is clicked
    if (forgotPasswordLink) {
        // Remove any existing event listeners
        const newForgotPasswordLink = forgotPasswordLink.cloneNode(true);
        forgotPasswordLink.parentNode.replaceChild(newForgotPasswordLink, forgotPasswordLink);
        
        // Add our event listener
        newForgotPasswordLink.addEventListener('click', function(e) {
            console.log("Forgot password link clicked");
            e.preventDefault();
            e.stopPropagation();
            
            // Pre-fill the email if it's already in the login form
            if (emailField && emailField.value.trim() !== '') {
                resetEmailInput.value = emailField.value;
            }
            
            // Show step 1
            showStep(1);
            
            // Show the modal
            if (modal) {
                modal.style.display = 'block';
                console.log("Modal displayed");
            } else {
                console.error("Modal element not found");
            }
            
            return false;
        });
    } else {
        console.error("Forgot password link not found");
    }
    
    // Close modal when X is clicked
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
            resetForm();
        });
    }
    
    // Close modal when clicking outside the content
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            resetForm();
        }
    });
    
    // Close modal and go back to login form
    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            resetForm();
        });
    }
    
    // Send verification code
    if (sendCodeBtn) {
        sendCodeBtn.addEventListener('click', async function() {
            console.log("Send code button clicked");
            const email = resetEmailInput.value.trim();
            
            if (emailError) emailError.textContent = '';
            
            // Validate email
            if (!email) {
                if (emailError) emailError.textContent = 'Please enter your email address.';
                return;
            }
            
            if (!isValidEmail(email)) {
                if (emailError) emailError.textContent = 'Please enter a valid email address.';
                return;
            }
            
            // Disable button and show loading state
            sendCodeBtn.disabled = true;
            sendCodeBtn.textContent = 'Sending...';
            
            try {
                console.log("Sending request to send_verification_code.php with email:", email);
                const formData = new FormData();
                formData.append('email', email);
                
                const response = await fetch('../model/password_reset/send_verification_code.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log("Response received, status:", response.status);
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error("Non-JSON response:", text);
                    throw new Error("Response was not JSON: " + text.substring(0, 100) + "...");
                }
                
                const data = await response.json();
                console.log("Response data:", data);
                
                if (data.success) {
                    // Store email for next steps
                    localStorage.setItem('reset_email', email);
                    
                    // For debugging - if debug_code is provided, display it
                    if (data.debug_code) {
                        console.log("Debug verification code:", data.debug_code);
                        alert("For testing purposes, your verification code is: " + data.debug_code);
                    }
                    
                    // Move to step 2
                    showStep(2);
                } else {
                    // Show message even if email doesn't exist (for security)
                    if (emailError) emailError.textContent = data.message || 'An error occurred. Please try again.';
                    console.error("Error from server:", data.message, data.error);
                }
            } catch (error) {
                console.error('Error sending reset code:', error);
                if (emailError) emailError.textContent = 'An error occurred. Please try again later. See console for details.';
            } finally {
                sendCodeBtn.disabled = false;
                sendCodeBtn.textContent = 'Send Verification Code';
            }
        });
    }
    
    // Verify code
    if (verifyCodeBtn) {
        verifyCodeBtn.addEventListener('click', async function() {
            const code = verificationCodeInput.value.trim();
            const email = localStorage.getItem('reset_email');
            if (codeError) codeError.textContent = '';
            
            // Validate code
            if (!code) {
                if (codeError) codeError.textContent = 'Please enter the verification code.';
                return;
            }
            
            if (!email) {
                if (codeError) codeError.textContent = 'Session expired. Please restart the process.';
                showStep(1);
                return;
            }
            
            // Disable button and show loading state
            verifyCodeBtn.disabled = true;
            verifyCodeBtn.textContent = 'Verifying...';
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('code', code);
                
                const response = await fetch('../model/password_reset/verify_reset_code.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Move to step 3
                    showStep(3);
                } else {
                    if (codeError) codeError.textContent = data.message;
                }
            } catch (error) {
                console.error('Error verifying code:', error);
                if (codeError) codeError.textContent = 'An error occurred. Please try again later.';
            } finally {
                verifyCodeBtn.disabled = false;
                verifyCodeBtn.textContent = 'Verify Code';
            }
        });
    }
    
    // Resend code
    if (resendCodeLink) {
        resendCodeLink.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const email = localStorage.getItem('reset_email');
            if (codeError) codeError.textContent = '';
            
            if (!email) {
                if (codeError) codeError.textContent = 'Session expired. Please restart the process.';
                showStep(1);
                return;
            }
            
            // Disable link temporarily
            this.style.pointerEvents = 'none';
            this.textContent = 'Sending...';
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                
                const response = await fetch('../model/password_reset/send_verification_code.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    if (codeError) {
                        codeError.style.color = '#28a745';
                        codeError.textContent = 'Verification code sent again.';
                        
                        // Reset after 3 seconds
                        setTimeout(() => {
                            codeError.textContent = '';
                            codeError.style.color = '#dc3545';
                        }, 3000);
                    }
                } else {
                    if (codeError) codeError.textContent = data.message;
                }
            } catch (error) {
                console.error('Error resending code:', error);
                if (codeError) codeError.textContent = 'An error occurred. Please try again later.';
            } finally {
                // Re-enable link after 30 seconds
                setTimeout(() => {
                    this.style.pointerEvents = 'auto';
                    this.textContent = 'Resend';
                }, 30000);
            }
        });
    }
    
    // Password strength meter
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value);
        });
    }
    
    // Reset password
    if (resetPasswordBtn) {
        resetPasswordBtn.addEventListener('click', async function() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (passwordError) passwordError.textContent = '';
            
            // Validate passwords
            if (!password) {
                if (passwordError) passwordError.textContent = 'Please enter a new password.';
                return;
            }
            
            if (password !== confirmPassword) {
                if (passwordError) passwordError.textContent = 'Passwords do not match.';
                return;
            }
            
            // Check password strength
            if (password.length < 8) {
                if (passwordError) passwordError.textContent = 'Password must be at least 8 characters long.';
                return;
            }
            
            if (!/[0-9]/.test(password)) {
                if (passwordError) passwordError.textContent = 'Password must contain at least one number.';
                return;
            }
            
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                if (passwordError) passwordError.textContent = 'Password must contain at least one symbol.';
                return;
            }
            
            // Disable button and show loading state
            resetPasswordBtn.disabled = true;
            resetPasswordBtn.textContent = 'Updating...';
            
            try {
                const formData = new FormData();
                formData.append('password', password);
                formData.append('confirmPassword', confirmPassword);
                
                const response = await fetch('../model/password_reset/update_password.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Clear stored email
                    localStorage.removeItem('reset_email');
                    
                    // Show success message
                    showStep(4); // Success step
                } else {
                    if (passwordError) passwordError.textContent = data.message;
                }
            } catch (error) {
                console.error('Error updating password:', error);
                if (passwordError) passwordError.textContent = 'An error occurred. Please try again later.';
            } finally {
                resetPasswordBtn.disabled = false;
                resetPasswordBtn.textContent = 'Reset Password';
            }
        });
    }
    
    // Helper functions
    function showStep(stepNumber) {
        // Hide all steps
        if (step1) step1.style.display = 'none';
        if (step2) step2.style.display = 'none';
        if (step3) step3.style.display = 'none';
        if (successStep) successStep.style.display = 'none';
        
        // Show requested step
        if (stepNumber === 1 && step1) step1.style.display = 'block';
        else if (stepNumber === 2 && step2) step2.style.display = 'block';
        else if (stepNumber === 3 && step3) step3.style.display = 'block';
        else if (stepNumber === 4 && successStep) successStep.style.display = 'block';
    }
    
    function resetForm() {
        // Clear inputs
        if (resetEmailInput) resetEmailInput.value = '';
        if (verificationCodeInput) verificationCodeInput.value = '';
        if (newPasswordInput) newPasswordInput.value = '';
        if (confirmPasswordInput) confirmPasswordInput.value = '';
        
        // Clear errors
        if (emailError) emailError.textContent = '';
        if (codeError) codeError.textContent = '';
        if (passwordError) passwordError.textContent = '';
        
        // Reset password strength
        const strengthBar = document.querySelector('.strength-bar');
        const strengthText = document.querySelector('.strength-text span');
        
        if (strengthBar) {
            strengthBar.className = 'strength-bar';
        }
        
        if (strengthText) {
            strengthText.textContent = 'weak';
        }
        
        // Show first step
        showStep(1);
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function getPasswordStrength(password) {
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        const length = password.length;
        
        let strength = 0;
        
        if (length >= 8) strength += 1;
        if (length >= 12) strength += 1;
        if (hasUpper) strength += 1;
        if (hasLower) strength += 1;
        if (hasNumber) strength += 1;
        if (hasSpecial) strength += 1;
        
        if (strength < 3) return 'weak';
        if (strength < 5) return 'medium';
        if (strength < 6) return 'strong';
        return 'very-strong';
    }
    
    function updatePasswordStrength(password) {
        const strengthLevel = getPasswordStrength(password);
        const strengthBar = document.querySelector('.strength-bar');
        const strengthText = document.querySelector('.strength-text span');
        
        if (strengthBar && strengthText) {
            // Reset classes
            strengthBar.className = 'strength-bar';
            
            // Add appropriate class
            strengthBar.classList.add(strengthLevel);
            
            // Update text
            strengthText.textContent = strengthLevel;
        }
    }
});
