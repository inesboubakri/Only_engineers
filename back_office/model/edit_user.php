<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../front_office/front_office/view/signin.php");
    exit();
}

// Check if user_id is provided in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../view/users.php");
    exit();
}

$userId = intval($_GET['id']);

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlyengs";

// Initialize variables
$user = null;
$errors = [];
$success = false;

// Connect to the database
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // If form is submitted, process the update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $fullName = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $position = trim($_POST['position']);
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        
        // Validate full name (must be two words with a space)
        if (empty($fullName)) {
            $errors[] = "Full name is required";
        } elseif (count(explode(' ', $fullName)) < 2) {
            $errors[] = "Full name must contain at least two words (first name and last name)";
        }
        
        // Validate email
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            // Check if email already exists for another user
            $emailCheckStmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email AND user_id != :user_id");
            $emailCheckStmt->bindParam(':email', $email);
            $emailCheckStmt->bindParam(':user_id', $userId);
            $emailCheckStmt->execute();
            
            if ($emailCheckStmt->rowCount() > 0) {
                $errors[] = "Email already in use by another user";
            }
        }
        
        // Validate position (must contain text, not just numbers or symbols)
        if (empty($position)) {
            $errors[] = "Position is required";
        } elseif (!preg_match('/[a-zA-Z]/', $position)) {
            $errors[] = "Position must contain letters (not just numbers or symbols)";
        }
        
        // Validate password if it's not empty (changing password)
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters";
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "Password must contain at least one uppercase letter";
            } elseif (!preg_match('/[0-9\W]/', $password)) {
                $errors[] = "Password must contain at least one number or symbol";
            }
        }
        
        // If no errors, update the user
        if (empty($errors)) {
            // Begin transaction
            $conn->beginTransaction();
            
            try {
                // Prepare SQL based on whether password is being updated
                if (!empty($password)) {
                    // Hash the new password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    $updateStmt = $conn->prepare("
                        UPDATE users SET 
                        full_name = :full_name, 
                        email = :email, 
                        password = :password, 
                        position = :position, 
                        is_admin = :is_admin 
                        WHERE user_id = :user_id
                    ");
                    $updateStmt->bindParam(':password', $hashedPassword);
                } else {
                    // Don't update password
                    $updateStmt = $conn->prepare("
                        UPDATE users SET 
                        full_name = :full_name, 
                        email = :email, 
                        position = :position, 
                        is_admin = :is_admin 
                        WHERE user_id = :user_id
                    ");
                }
                
                // Bind parameters
                $updateStmt->bindParam(':full_name', $fullName);
                $updateStmt->bindParam(':email', $email);
                $updateStmt->bindParam(':position', $position);
                $updateStmt->bindParam(':is_admin', $isAdmin);
                $updateStmt->bindParam(':user_id', $userId);
                
                // Execute the update
                $updateStmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $success = true;
                
                // Redirect back to users.php after successful update
                header("Location: ../view/users.php?success=1");
                exit();
            } catch (Exception $e) {
                // Rollback the transaction
                $conn->rollBack();
                $errors[] = "Error updating user: " . $e->getMessage();
            }
        }
    }
    
    // Get the user data (refreshed if update was successful)
    $stmt = $conn->prepare("
        SELECT user_id, full_name, email, password, profile_picture, position, is_admin 
        FROM users 
        WHERE user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    // Check if user exists
    if ($stmt->rowCount() === 0) {
        header("Location: ../view/users.php");
        exit();
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Dashboard</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Form styles */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .success-message {
            background-color: #d1fae5;
            color: #065f46;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-secondary {
            background-color: #6b7280;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        
        .form-footer {
            display: flex;
            justify-content: flex-start;
            margin-top: 30px;
        }
        
        .password-info {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .error-list {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error-list ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
        }
        
        .checkbox-container input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body class="light-theme">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon">O</div>
                <span>OnlyEngineers</span>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../view/dashboard.html"><div class="nav-icon">📊</div><span>Dashboard</span></a></li>
                    <li class="active"><a href="../view/users.php"><div class="nav-icon">👥</div><span>Users</span></a></li>
                    <li><a href="../view/jobs.html"><div class="nav-icon">👩🏻‍💻</div><span>Jobs</span></a></li>
                    <li><a href="../view/Projects.html"><div class="nav-icon">🚀</div><span>Projects</span></a></li>
                    <li><a href="../view/articles.html"><div class="nav-icon">📰</div><span>News</span></a></li>
                    <li><a href="../view/hackathons.html"><div class="nav-icon">🏆</div><span>Hackathons</span></a></li>
                    <li><a href="../view/courses.html"><div class="nav-icon">📚</div><span>Courses</span></a></li>
                    <li><div class="nav-icon">💼</div><span>Opportunities</span></li>
                    <li><div class="nav-icon">🔔</div><span>Notifications</span><div class="notification-badge">1</div></li>
                    <li><a href="../../front_office/front_office/view/signin.php"><div class="nav-icon">🚪</div><span>Sign out</span></a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Edit User <span class="wave-emoji">✏️</span></h1>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="search">
                        <div class="search-icon">🔍</div>
                    </div>
                    <!-- Theme toggle in header -->
                    <div class="header-theme-toggle">
                        <label class="theme-switch">
                            <input type="checkbox" id="theme-toggle">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="user-profile">
                        <div class="avatar">
                            <img src="https://i.pravatar.cc/100?img=32" alt="Admin User">
                        </div>
                        <span>Admin User</span>
                    </div>
                </div>
            </div>

            <!-- Edit User Form -->
            <div class="form-container">
                <?php if ($success): ?>
                    <div class="success-message">
                        User updated successfully!
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-list">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="edit_user.php?id=<?php echo $userId; ?>" method="POST">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        <div class="error-message" id="full_name_error">Full name must contain at least two words (first name and last name)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        <div class="error-message" id="email_error">Please enter a valid email address</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Leave empty to keep current password">
                        <div class="password-info">
                            Password must be at least 8 characters long, include one uppercase letter, and one number or symbol.
                            <br>Current password is hashed and cannot be displayed for security reasons.
                        </div>
                        <div class="error-message" id="password_error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" class="form-control" value="<?php echo htmlspecialchars($user['position']); ?>" required>
                        <div class="error-message" id="position_error">Position must contain letters (not just numbers or symbols)</div>
                    </div>
                    
                    <?php if (!empty($user['profile_picture'])): ?>
                        <div class="form-group">
                            <label>Profile Picture</label>
                            <?php 
                            $profilePicUrl = '../../front_office/front_office/ressources/profil.jpg';
                            $picturePath = '../../front_office/front_office/ressources/profile_pictures/' . $user['profile_picture'];
                            if (file_exists($picturePath)) {
                                $profilePicUrl = $picturePath;
                            }
                            ?>
                            <img src="<?php echo $profilePicUrl; ?>" alt="User Profile" class="profile-image">
                            <p>Current profile picture: <?php echo htmlspecialchars($user['profile_picture']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group checkbox-container">
                        <input type="checkbox" id="is_admin" name="is_admin" <?php echo $user['is_admin'] == 1 ? 'checked' : ''; ?>>
                        <label for="is_admin">Admin User</label>
                    </div>
                    
                    <div class="form-footer">
                        <a href="../view/users.php" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Basic functionality for theme toggle
        document.getElementById('theme-toggle').addEventListener('change', function() {
            document.body.classList.toggle('dark-theme');
            document.body.classList.toggle('light-theme');
        });

        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            const fullName = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const position = document.getElementById('position').value.trim();
            
            // Hide all error messages initially
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });
            
            // Validate full name (must have at least two words)
            if (fullName === '' || fullName.split(' ').filter(word => word.length > 0).length < 2) {
                document.getElementById('full_name_error').style.display = 'block';
                isValid = false;
            }
            
            // Validate email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                document.getElementById('email_error').style.display = 'block';
                isValid = false;
            }
            
            // Validate password only if provided (changing password)
            if (password !== '') {
                let passwordError = '';
                
                if (password.length < 8) {
                    passwordError = 'Password must be at least 8 characters';
                } else if (!/[A-Z]/.test(password)) {
                    passwordError = 'Password must contain at least one uppercase letter';
                } else if (!/[0-9\W]/.test(password)) {
                    passwordError = 'Password must contain at least one number or symbol';
                }
                
                if (passwordError) {
                    document.getElementById('password_error').textContent = passwordError;
                    document.getElementById('password_error').style.display = 'block';
                    isValid = false;
                }
            }
            
            // Validate position (must contain letters, not just numbers or symbols)
            if (position === '' || !/[a-zA-Z]/.test(position)) {
                document.getElementById('position_error').style.display = 'block';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Initially hide error messages
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });
        });
    </script>
</body>
</html>