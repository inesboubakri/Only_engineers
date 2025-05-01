<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../front_office/front_office/view/signin.php");
    exit();
}

// Include database connection
require_once 'db_connectionback.php';

// Connect to the database
try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: ../view/hackathons.php?success=0&message=No hackathon ID provided");
        exit();
    }
    
    $hackathonId = $_GET['id'];
    
    // Handle form submission for updating hackathon
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate and sanitize input
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $startDate = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $endDate = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
        $startTime = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
        $endTime = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
        $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
        $requiredSkills = filter_input(INPUT_POST, 'required_skills', FILTER_SANITIZE_STRING);
        $organizer = filter_input(INPUT_POST, 'organizer', FILTER_SANITIZE_STRING);
        $maxParticipants = filter_input(INPUT_POST, 'max_participants', FILTER_VALIDATE_INT);
        
        // Get and validate latitude and longitude
        $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
        $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);
        
        // Validate geographic coordinates if provided
        if ($latitude !== false && $longitude !== false) {
            if ($latitude < -90 || $latitude > 90) {
                header("Location: ../view/hackathons.php?success=0&message=Invalid latitude value. Must be between -90 and 90.");
                exit();
            }
            
            if ($longitude < -180 || $longitude > 180) {
                header("Location: ../view/hackathons.php?success=0&message=Invalid longitude value. Must be between -180 and 180.");
                exit();
            }
        } else {
            // If coordinates are invalid, set them to NULL
            $latitude = null;
            $longitude = null;
        }
        
        // Handle image upload if a new image is provided
        $imageFileName = null;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../front_office/front_office/ressources/hackathon_images/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $imageFileName = 'hackathon_' . time() . '_' . $_FILES['image']['name'];
            $uploadFile = $uploadDir . $imageFileName;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $imageFileName = 'hackathon_images/' . $imageFileName;
            } else {
                // If upload fails, set error and redirect
                header("Location: ../view/hackathons.php?success=0&message=Failed to upload image");
                exit();
            }
        }
        
        // Update hackathon in the database
        $updateQuery = "UPDATE hackathons SET 
                        name = :name, 
                        description = :description, 
                        start_date = :start_date, 
                        end_date = :end_date, 
                        start_time = :start_time, 
                        end_time = :end_time, 
                        location = :location, 
                        required_skills = :required_skills, 
                        organizer = :organizer, 
                        max_participants = :max_participants";
        
        // Add image to query only if a new one was uploaded
        if ($imageFileName !== null) {
            $updateQuery .= ", image = :image";
        }
        
        // Add latitude and longitude to query
        $updateQuery .= ", latitude = :latitude, longitude = :longitude";
        
        $updateQuery .= " WHERE id = :id";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->bindParam(':start_time', $startTime);
        $stmt->bindParam(':end_time', $endTime);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':required_skills', $requiredSkills);
        $stmt->bindParam(':organizer', $organizer);
        $stmt->bindParam(':max_participants', $maxParticipants);
        $stmt->bindParam(':latitude', $latitude);
        $stmt->bindParam(':longitude', $longitude);
        $stmt->bindParam(':id', $hackathonId);
        
        // Bind image parameter only if a new one was uploaded
        if ($imageFileName !== null) {
            $stmt->bindParam(':image', $imageFileName);
        }
        
        if ($stmt->execute()) {
            header("Location: ../view/hackathons.php?success=1&message=Hackathon updated successfully");
            exit();
        } else {
            header("Location: ../view/hackathons.php?success=0&message=Failed to update hackathon");
            exit();
        }
    }
    
    // Fetch hackathon data for editing
    $stmt = $conn->prepare("SELECT * FROM hackathons WHERE id = :id");
    $stmt->bindParam(':id', $hackathonId);
    $stmt->execute();
    
    $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hackathon) {
        header("Location: ../view/hackathons.php?success=0&message=Hackathon not found");
        exit();
    }
    
} catch(Exception $e) {
    echo "Database operation failed: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hackathon | Dashboard</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748;
        }
        
        .form-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-column {
            flex: 1;
        }
        
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #4a5568;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="time"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        input:focus, 
        textarea:focus,
        select:focus {
            border-color: #4c6ef5;
            outline: none;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }
        
        .cancel-btn {
            padding: 10px 20px;
            background-color: #e2e8f0;
            color: #4a5568;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .save-btn {
            padding: 10px 20px;
            background-color: #4c6ef5;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .cancel-btn:hover {
            background-color: #cbd5e0;
        }
        
        .save-btn:hover {
            background-color: #3b5bdb;
        }
        
        .current-image {
            margin-top: 10px;
            max-width: 300px;
            max-height: 200px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .image-info {
            margin-top: 5px;
            font-size: 13px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="form-title">Edit Hackathon</h1>
        
        <div class="form-card">
            <form action="edit_hackathon.php?id=<?php echo $hackathonId; ?>" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="name">Hackathon Title</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($hackathon['name']); ?>" required>
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label for="organizer">Organizer</label>
                            <input type="text" id="organizer" name="organizer" value="<?php echo htmlspecialchars($hackathon['organizer']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($hackathon['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($hackathon['start_date']); ?>" required>
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input type="time" id="start_time" name="start_time" value="<?php echo htmlspecialchars($hackathon['start_time']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($hackathon['end_date']); ?>" required>
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label for="end_time">End Time</label>
                            <input type="time" id="end_time" name="end_time" value="<?php echo htmlspecialchars($hackathon['end_time']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($hackathon['location']); ?>" required>
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label for="max_participants">Maximum Participants</label>
                            <input type="number" id="max_participants" name="max_participants" value="<?php echo htmlspecialchars($hackathon['max_participants']); ?>" min="1" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="required_skills">Required Skills (comma separated)</label>
                    <input type="text" id="required_skills" name="required_skills" value="<?php echo htmlspecialchars($hackathon['required_skills']); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="latitude">Latitude</label>
                            <input type="text" id="latitude" name="latitude" value="<?php echo htmlspecialchars($hackathon['latitude']); ?>">
                        </div>
                    </div>
                    <div class="form-column">
                        <div class="form-group">
                            <label for="longitude">Longitude</label>
                            <input type="text" id="longitude" name="longitude" value="<?php echo htmlspecialchars($hackathon['longitude']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Hackathon Image</label>
                    <?php if (!empty($hackathon['image'])): ?>
                        <div>
                            <p class="image-info">Current image:</p>
                            <img src="../../front_office/front_office/ressources/<?php echo htmlspecialchars($hackathon['image']); ?>" alt="Hackathon Image" class="current-image">
                            <p class="image-info">Upload a new image to replace the current one (optional)</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <div class="button-group">
                    <a href="../view/hackathons.php" class="cancel-btn">Cancel</a>
                    <button type="submit" class="save-btn">Update Hackathon</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>