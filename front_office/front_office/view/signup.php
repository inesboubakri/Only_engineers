<?php
session_start();
// Display errors if there are any
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}

// Get form data if it exists
if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - OnlyEngineers</title>
    <link rel="stylesheet" href="../view/signup.css">
    <link rel="stylesheet" href="../view/signup-error.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Basic reset */
        /* OnlyEngineers - Professional Signin Page with Animations */
:root {
    --primary: #2563eb;           /* Engineering blue */
    --primary-dark: #1e40af;
    --primary-light: #3b82f6;
    --secondary: #059669;        /* Success green */
    --accent: #d97706;           /* Warning orange */
    --dark: #1e293b;            /* Dark slate */
    --medium: #64748b;           /* Medium slate */
    --light: #f8fafc;           /* Light background */
    --border: #e2e8f0;          /* Border color */
    --error: #dc2626;           /* Error red */
    --success: #10b981;         /* Success green */
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
    100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

body {
    background-color: var(--light);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    color: var(--dark);
    line-height: 1.6;
}

.signup-container {
    display: flex;
    width: 1000px;
    height: 600px;
    background-color: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    position: relative;
    animation: fadeIn 0.6s ease-out forwards;
}

/* OnlyEngineers - Premium Left Panel Animation */
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
/* Right Panel - Signin Form */
.right-panel {
    width: 55%;
    padding: 50px 40px;
    display: flex;
    flex-direction: column;
    position: relative;
}

.language-selector {
    position: absolute;
    top: 20px;
    right: 40px;
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    color: var(--medium);
    transition: color 0.3s;
}

.language-selector:hover {
    color: var(--primary);
}

.language-selector i {
    margin-left: 5px;
    font-size: 12px;
    transition: transform 0.3s;
}

.language-selector:hover i {
    transform: translateY(2px);
}

.signup-form-container {
    max-width: 400px;
    margin: auto;
    width: 100%;
    animation: fadeIn 0.8s ease-out 0.4s both;
}

.signup-form-container h2 {
    font-size: 28px;
    color: var(--dark);
    margin-bottom: 30px;
    font-weight: 700;
}

.form-group {
    margin-bottom: 20px;
    position: relative;
    animation: fadeIn 0.8s ease-out forwards;
}

.form-group:nth-child(1) { animation-delay: 0.5s; }
.form-group:nth-child(2) { animation-delay: 0.6s; }
.form-group:nth-child(3) { animation-delay: 0.7s; }

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 15px 20px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 16px;
    color: var(--dark);
    background-color: white;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    transform: translateY(-1px);
}

.password-group {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--medium);
    cursor: pointer;
    font-size: 16px;
    transition: color 0.3s;
}

.toggle-password:hover {
    color: var(--primary);
}

.checkbox-group {
    display: flex;
    align-items: flex-start;
    margin-top: 10px;
    animation: fadeIn 0.8s ease-out 0.8s both;
}

.checkbox-group input[type="checkbox"] {
    margin-top: 3px;
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
    cursor: pointer;
    transition: transform 0.2s;
}

.checkbox-group input[type="checkbox"]:hover {
    transform: scale(1.1);
}

.checkbox-group label {
    font-size: 14px;
    color: var(--medium);
    line-height: 1.5;
    margin-left: 10px;
    cursor: pointer;
}

.checkbox-group a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
}

.checkbox-group a:hover {
    text-decoration: underline;
}

.signup-button {
    width: 100%;
    padding: 16px;
    background-color: var(--primary);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 15px;
    animation: fadeIn 0.8s ease-out 0.9s both;
}

.signup-button:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
}

.signup-button:active {
    transform: translateY(0);
}

.divider {
    display: flex;
    align-items: center;
    margin: 25px 0;
    color: var(--medium);
    font-size: 14px;
    animation: fadeIn 0.8s ease-out 1s both;
}

.divider::before,
.divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background-color: var(--border);
}

.divider::before {
    margin-right: 15px;
}

.divider::after {
    margin-left: 15px;
}

.social-signup {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 25px;
    animation: fadeIn 0.8s ease-out 1.1s both;
}

.social-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.social-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.social-btn:active {
    transform: translateY(-1px);
}

.social-btn img {
    width: 20px;
    height: 20px;
}

.social-btn i {
    font-size: 18px;
}

.google {
    color: #DB4437;
}

.google:hover {
    background-color: rgba(219, 68, 55, 0.1);
    border-color: #DB4437;
}

.github {
    color: #333;
}

.github:hover {
    background-color: rgba(51, 51, 51, 0.1);
    border-color: #333;
}

.login-link {
    text-align: center;
    font-size: 14px;
    color: var(--medium);
    animation: fadeIn 0.8s ease-out 1.2s both;
    margin-top: -5mm;
}

.login-link a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    position: relative;
}

.login-link a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--primary);
    transition: width 0.3s;
}

.login-link a:hover::after {
    width: 100%;
}

/* Error Styling */
.error-banner {
    background-color: rgba(220, 38, 38, 0.1);
    border-left: 4px solid var(--error);
    color: var(--error);
    padding: 14px 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    position: relative;
    font-size: 14px;
    animation: fadeIn 0.5s ease-out, pulse 1.5s infinite;
}

.close-btn {
    position: absolute;
    right: 12px;
    top: 12px;
    background: none;
    border: none;
    color: var(--error);
    font-size: 18px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    transition: transform 0.3s;
}

.close-btn:hover {
    transform: rotate(90deg);
}

.error-message {
    color: var(--error);
    font-size: 13px;
    margin-top: 6px;
    display: block;
    font-weight: 500;
}

.error {
    border-color: var(--error) !important;
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% { transform: translateX(-1px); }
    20%, 80% { transform: translateX(2px); }
    30%, 50%, 70% { transform: translateX(-3px); }
    40%, 60% { transform: translateX(3px); }
}

/* Responsive Design */
@media (max-width: 900px) {
    .signup-container {
        width: 95%;
        flex-direction: column;
        height: auto;
        margin: 30px 0;
    }
    
    .left-panel, .right-panel {
        width: 100%;
    }
    
    .left-panel {
        padding: 30px;
        height: 300px;
    }
    
    .hero-content {
        margin-top: 30px;
    }
    
    .hero-content h1 {
        font-size: 28px;
        margin-bottom: 20px;
    }
    
    .illustration {
        height: 150px;
        margin-top: 20px;
    }
    
    .right-panel {
        padding: 40px 30px;
    }
    
    .signup-form-container {
        padding: 20px 0;
    }
}

@media (max-width: 480px) {
    .left-panel {
        height: 250px;
        padding: 25px;
    }
    
    .hero-content h1 {
        font-size: 24px;
    }
    
    .right-panel {
        padding: 30px 20px;
    }
    
    .signup-form-container h2 {
        font-size: 24px;
        margin-bottom: 20px;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"] {
        padding: 12px 15px;
    }
    
    .signup-button {
        padding: 14px;
    }
}
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="left-panel">
            <div class="logo">
                <h3>OnlyEngineers</h3>
                <p>Connect, Learn, Engineer</p>
            </div>
            
            <div class="hero-content">
                <h1>Learn From World's<br>Best Engineers<br>Around The World.</h1>
                
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
            
            <div class="signup-form-container">
                <h2>Create Account</h2>
                
                <!-- Display error banner if any -->
                <?php if (isset($errors) && !empty($errors)): ?>
                <div class="error-banner">
                    <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
                    <ul>
                        <?php foreach($errors as $key => $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Changed form to submit to register_user.php -->
                <form id="signupForm" action="../model/register_user.php" method="POST">
                    <div class="form-group">
                        <input type="text" id="fullName" name="fullName" placeholder="Full Name" required 
                               value="<?php echo isset($formData['fullName']) ? htmlspecialchars($formData['fullName']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <input type="email" id="email" name="email" placeholder="Email Address" required
                               value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group password-group">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="termsAgree" name="termsAgree" required>
                        <label for="termsAgree">I agree to the <a href="#">terms of service</a> and <a href="#">privacy policy</a></label>
                    </div>
                    
                    <button type="submit" class="signup-button">Sign Up</button>
                </form>
                
                <div class="divider">
                    <span>Or Sign Up With</span>
                </div>
                
                <div class="social-signup">
                <button class="social-btn google">
                        <img src="../ressources/google.png" alt="Google">
                    </button>
                    <button class="social-btn github">
                        <img src="../ressources/github.svg" alt="Google">
                    </button>
                </div>
                
                <div class="login-link">
                    <p>Already have an account? <a href="../view/signin.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../controller/signup.js"></script>
</body>
</html>
