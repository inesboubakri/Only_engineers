<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management</title>
    <style>
        .learn-more {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            margin: 20px 0;
        }
        
        .learn-more:hover {
            background-color: #45a049;
        }
        
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="success-message" style="display: block;">
            Project added successfully!
        </div>
        <?php endif; ?>
        
        <h1>Project Management</h1>
        
        <a href="add_project.php" class="learn-more">Add a Project</a>
        
        <!-- Rest of your page content here -->
    </div>
    
    <script>
        // Hide success message after 3 seconds
        setTimeout(function() {
            var successMessage = document.querySelector('.success-message');
            if(successMessage) {
                successMessage.style.display = 'none';
            }
        }, 3000);
    </script>
</body>
</html>
