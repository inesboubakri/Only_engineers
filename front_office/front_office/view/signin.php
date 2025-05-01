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
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google">
                    </button>
                    <button class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button class="social-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </button>
                </div>
                
                <div class="signup-prompt">
                    <span>Don't have an account?</span>
                    <a href="signup.php">Sign Up</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../controller/signin.js"></script>
</body>
</html>