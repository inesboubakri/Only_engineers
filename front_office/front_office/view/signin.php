<?php
// Start session to access session variables
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyEngineers - Sign In</title>
    <link rel="stylesheet" href="signin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Additional styles for error messages */
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .error-message ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        .left-panel {
    width: 45%;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    padding: 40px;
    position: relative;
    overflow: hidden;
    z-index: 1;
    isolation: isolate;
}

.left-panel::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: 
        radial-gradient(circle at 30% 30%, 
            rgba(255,255,255,0.15) 0%, 
            rgba(255,255,255,0) 25%),
        radial-gradient(circle at 70% 70%, 
            rgba(255,255,255,0.1) 0%, 
            rgba(255,255,255,0) 25%);
    z-index: -1;
    animation: float 15s ease-in-out infinite alternate;
}

.left-panel::after {
    content: '';
    position: absolute;
    bottom: -100px;
    right: -100px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, rgba(59, 130, 246, 0) 70%);
    z-index: -1;
    animation: pulse 8s ease-in-out infinite;
}

@keyframes circuitAnimation {
    0% { background-position: 0% 0%; }
    100% { background-position: 100% 100%; }
}

.engineering-circuit {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        linear-gradient(90deg, 
            rgba(255,255,255,0.03) 1px, 
            transparent 1px),
        linear-gradient(rgba(255,255,255,0.03) 1px, 
            transparent 1px);
    background-size: 40px 40px;
    z-index: -1;
    animation: circuitAnimation 120s linear infinite;
}
.logo {
    position: relative;
    z-index: 3;
    animation: fadeInDown 0.8s ease-out both;
}

.logo h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 4px;
    letter-spacing: 1px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.logo p {
    font-size: 14px;
    opacity: 0.9;
    font-weight: 300;
    letter-spacing: 0.5px;
}

.hero-content {
    margin-top: 60px;
    position: relative;
    z-index: 2;
    animation: fadeInUp 0.8s ease-out 0.2s both;
}

.hero-content h1 {
    font-size: 32px;
    font-weight: 800;
    line-height: 1.3;
    margin-bottom: 30px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.15);
    position: relative;
}

.hero-content h1::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 0;
    width: 60px;
    height: 4px;
    background: rgba(255,255,255,0.7);
    border-radius: 2px;
    animation: widthGrow 1.2s ease-out 0.8s both;
}

.engineering-illustration {
    position: relative;
    height: 280px;
    margin-top: 40px;
    background-image: url('../ressources/engineering-illustration.svg');
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.95;
    filter: drop-shadow(0 5px 15px rgba(0,0,0,0.2));
    animation: float 6s ease-in-out infinite, glow 4s ease-in-out infinite alternate;
}

/* Keyframes for premium animations */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

@keyframes fadeInDown {
    from { 
        opacity: 0;
        transform: translateY(-30px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from { 
        opacity: 0;
        transform: translateY(30px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes widthGrow {
    from { width: 0; opacity: 0; }
    to { width: 60px; opacity: 1; }
}

@keyframes pulse {
    0% { transform: scale(0.8); opacity: 0.6; }
    50% { transform: scale(1.1); opacity: 0.9; }
    100% { transform: scale(0.8); opacity: 0.6; }
}

@keyframes glow {
    from { filter: drop-shadow(0 5px 15px rgba(0,0,0,0.2)); }
    to { filter: drop-shadow(0 5px 25px rgba(255,255,255,0.3)); }
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .left-panel {
        height: 300px;
        padding: 30px;
    }
    
    .hero-content {
        margin-top: 30px;
    }
    
    .hero-content h1 {
        font-size: 28px;
    }
    
    .engineering-illustration {
        height: 180px;
        margin-top: 20px;
    }
}

@media (max-width: 480px) {
    .left-panel::before {
        animation-duration: 20s;
    }
    
    .hero-content h1 {
        font-size: 24px;
    }
    
    .engineering-illustration {
        height: 150px;
    }
}
.Faceid{
    
    background-color: #fff;
    border: 1px solid #ccc;

}

/* Modal styles for forgot password */
.modal {
    display: none;
    position: fixed;
    z-index: 10;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    width: 400px;
    max-width: 90%;
    position: relative;
    animation: slideIn 0.3s ease;
}

.close-modal {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 24px;
    color: #aaa;
    cursor: pointer;
    transition: color 0.2s;
}

.close-modal:hover {
    color: #333;
}

.modal-step {
    text-align: center;
}

.modal-step h3 {
    font-size: 22px;
    margin-bottom: 15px;
    color: #333;
}

.modal-step p {
    color: #666;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 1.5;
}

.form-group {
    margin-bottom: 20px;
    position: relative;
}

.error-text {
    color: #dc3545;
    font-size: 13px;
    margin-top: 5px;
    margin-bottom: 10px;
    text-align: left;
}

.primary-button {
    width: 100%;
    padding: 12px;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.primary-button:hover {
    background-color: #2563eb;
}

.primary-button:disabled {
    background-color: #a0c0f8;
    cursor: not-allowed;
}

.resend-link {
    margin-top: 15px;
    font-size: 13px;
}

.resend-link a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.resend-link a:hover {
    text-decoration: underline;
}

.verification-code-group input {
    letter-spacing: 2px;
    font-size: 18px;
    text-align: center;
}

.password-strength-meter {
    margin: 15px 0;
    text-align: left;
}

.strength-bar {
    height: 5px;
    background-color: #f1f1f1;
    border-radius: 10px;
    margin-bottom: 5px;
    position: relative;
    overflow: hidden;
}

.strength-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 0%;
    background-color: #ff4d4d;
    border-radius: 10px;
    transition: width 0.3s, background-color 0.3s;
}

.strength-bar.weak::before {
    width: 25%;
    background-color: #ff4d4d;
}

.strength-bar.medium::before {
    width: 50%;
    background-color: #ffb84d;
}

.strength-bar.strong::before {
    width: 75%;
    background-color: #35c94d;
}

.strength-bar.very-strong::before {
    width: 100%;
    background-color: #2dbe44;
}

.strength-text {
    font-size: 12px;
    color: #666;
    margin: 0;
}

.success-icon {
    width: 60px;
    height: 60px;
    background-color: #d1fae5;
    color: #10b981;
    font-size: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin: 0 auto 20px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
    </style>
</head>
<body>
    <div class="signin-container">
        <div class="left-panel">
            <div class="logo">
                <h3>OnlyEngineers</h3>
                <p>Connect, Learn, Engineer</p>
            </div>
            
            <div class="hero-content">
                <h1>Welcome Back To<br>The Engineering<br>Community.</h1>
                
                <div class="illustration">
                    <!-- Animated elements will be added via CSS -->
                </div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="language-selector">
                <span>English(USA)</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            
            <div class="signin-form-container">
                <h2>Sign In</h2>
                
                <!-- Display error messages if any -->
                <?php if (isset($_SESSION['signin_error'])): ?>
                    <div class="error-message">
                        <?php 
                            echo $_SESSION['signin_error'];
                            unset($_SESSION['signin_error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['signin_errors']) && is_array($_SESSION['signin_errors'])): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach($_SESSION['signin_errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['signin_errors']); ?>
                <?php endif; ?>
                
                <form action="../model/authenticate.php" method="POST">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>
                    
                    <div class="form-group password-group">
                        <input type="password" name="password" id="password" placeholder="Password" required>
                        <button type="button" class="toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="signin-button">Sign In</button>
                </form>
                
                <div class="divider">
                    <span>Or Sign In With</span>
                </div>
                
                <div class="social-signin">
                    <button class="social-btn google">
                        <img src="../ressources/google.png" alt="Google">
                    </button>
                    <button class="social-btn github">
                        <img src="../ressources/github.svg" alt="Google">
                    </button>
                    <button class="social-btn Faceid">
                        <img src="../ressources/faceid.jpg" alt="Google">
                    </button>
                    
                </div>
                
                <div class="signup-prompt">
                    <span>Don't have an account?</span>
                    <a href="signup.php">Sign Up</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            
            <!-- Step 1: Email Entry -->
            <div class="modal-step" id="step1">
                <h3>Reset Password</h3>
                <p>Enter your email address to receive a verification code.</p>
                <div class="form-group">
                    <input type="email" id="reset-email" placeholder="Email Address" required>
                </div>
                <div id="email-error" class="error-text"></div>
                <button id="sendCodeBtn" class="primary-button">Send Verification Code</button>
            </div>
            
            <!-- Step 2: Verification Code Entry -->
            <div class="modal-step" id="step2" style="display: none;">
                <h3>Enter Verification Code</h3>
                <p>We sent a verification code to your email. Please enter it below.</p>
                <div class="form-group verification-code-group">
                    <input type="text" id="verification-code" placeholder="Verification Code" required>
                </div>
                <div id="code-error" class="error-text"></div>
                <button id="verifyCodeBtn" class="primary-button">Verify Code</button>
                <p class="resend-link">Didn't receive the code? <a href="#" id="resendCode">Resend</a></p>
            </div>
            
            <!-- Step 3: New Password Entry -->
            <div class="modal-step" id="step3" style="display: none;">
                <h3>Create New Password</h3>
                <p>Please enter your new password.</p>
                <div class="form-group password-group">
                    <input type="password" id="new-password" placeholder="New Password" required>
                    <button type="button" class="toggle-new-password">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="form-group password-group">
                    <input type="password" id="confirm-password" placeholder="Confirm Password" required>
                    <button type="button" class="toggle-confirm-password">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div id="password-error" class="error-text"></div>
                <div class="password-strength-meter">
                    <div class="strength-bar"></div>
                    <p class="strength-text">Password strength: <span>weak</span></p>
                </div>
                <button id="resetPasswordBtn" class="primary-button">Reset Password</button>
            </div>
            
            <!-- Success Message -->
            <div class="modal-step" id="success-step" style="display: none;">
                <div class="success-icon">✓</div>
                <h3>Password Reset Successful</h3>
                <p>Your password has been reset successfully. You can now sign in with your new password.</p>
                <button id="backToLoginBtn" class="primary-button">Back to Sign In</button>
            </div>
        </div>
    </div>
    
    <script src="../controller/signin.js"></script>
</body>
</html>