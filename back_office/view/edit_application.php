<?php
define('BASE_PATH', dirname(__DIR__, 2)); // Points to C:\xampp\htdocs\projet_web\projet_web
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/front_office/front_office/controller/controller_apply.php';

// Check if the ID is provided in the URL
if (!isset($_GET['ID'])) {
    echo "No application ID provided.";
    exit();
}

$id = $_GET['ID'];

try {
    // Fetch the application details using the ID
    $stmt = config::getConnexion()->prepare("SELECT * FROM candidature WHERE ID = :ID");
    $stmt->bindParam(':ID', $id, PDO::PARAM_INT);
    $stmt->execute();
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        echo "<p class='message'>No application found with ID: " . htmlspecialchars($id) . "</p>";
        exit();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Handle form submission to update the application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle file upload
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../view/uploads/';
            $resumeFileName = basename($_FILES['resume']['name']);
            $resumePath = $uploadDir . $resumeFileName;

            // Check if the uploads directory exists, and create it if it doesn't
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Move the uploaded file to the uploads directory
            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resumePath)) {
                echo "Error uploading file.";
                exit();
            }

            // Store the relative path in the database
            $resume = 'uploads/' . $resumeFileName;
        } else {
            // If no new file is uploaded, keep the existing resume
            $resume = $application['resume'];
        }

        // Update the application in the database
        $stmt = config::getConnexion()->prepare("
            UPDATE candidature
            SET nom_candidat = :nom_candidat,
                prenom_candidat = :prenom_candidat,
                email = :email,
                role = :role,
                adresse = :adresse,
                city = :city,
                Date = :Date,
                resume = :resume
            WHERE ID = :ID
        ");

        // Bind the form data to the query
        $stmt->bindParam(':ID', $_POST['ID'], PDO::PARAM_INT);
        $stmt->bindParam(':nom_candidat', $_POST['nom_candidat']);
        $stmt->bindParam(':prenom_candidat', $_POST['prenom_candidat']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':role', $_POST['role']);
        $stmt->bindParam(':adresse', $_POST['adresse']);
        $stmt->bindParam(':city', $_POST['city']);
        $stmt->bindParam(':Date', $_POST['Date']);
        $stmt->bindParam(':resume', $resume);
        $stmt->execute();

        // Redirect to the list page with a success message
        header("Location: http://localhost:8888/projet_web/projet_web/back_office/view/jobs.php?message=Application updated successfully!");
        exit();
    } catch (Exception $e) {
        echo "<p class='message'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Application</title>
    <link rel="stylesheet" href="../../front_office/front_office/view/list1_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="applications-container">
        <div class="applications-header">
            <h2>Edit Application</h2>
        </div>

        <form method="POST" action="edit_application.php?ID=<?= htmlspecialchars($application['ID']) ?>" enctype="multipart/form-data">
            <input type="hidden" name="ID" value="<?= htmlspecialchars($application['ID']) ?>">

            <table class="applications-table">
                <tbody>
                    <tr>
                        <td><label for="nom_candidat">First Name:</label></td>
                        <td><input type="text" id="nom_candidat" name="nom_candidat" value="<?= htmlspecialchars($application['nom_candidat']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="prenom_candidat">Last Name:</label></td>
                        <td><input type="text" id="prenom_candidat" name="prenom_candidat" value="<?= htmlspecialchars($application['prenom_candidat']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="email">Email:</label></td>
                        <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($application['email']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="role">Role:</label></td>
                        <td><input type="text" id="role" name="role" value="<?= htmlspecialchars($application['role']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="adresse">Address:</label></td>
                        <td><input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($application['adresse']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="city">City:</label></td>
                        <td><input type="text" id="city" name="city" value="<?= htmlspecialchars($application['city']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="Date">Date:</label></td>
                        <td><input type="date" id="Date" name="Date" value="<?= htmlspecialchars($application['Date']) ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="resume">Resume:</label></td>
                        <td>
                            <?php if (!empty($application['resume'])): ?>
                                <a href="<?= htmlspecialchars($application['resume']) ?>" target="_blank">View Current Resume</a>
                            <?php endif; ?>
                            <input type="file" id="resume" name="resume">
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="jobs.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>