<?php
require_once __DIR__ . '/../controller/job_offer.php';


// Fetch form values
$titre = $_POST['titre'];
$entreprise = $_POST['entreprise'];
$eplacement = $_POST['emplacement'];
$description = $_POST['description'];
$date = $_POST['date'];
$type = $_POST['type'];
$email = $_POST['Email'];

// Insert into offer table
$sql = "INSERT INTO offre (titre, entreprise, eplacement, description, date, type, Email)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $titre, $entreprise, $eplacement, $description, $date, $type, $email);
if ($stmt->execute()) {
    echo "<p>🎉 Job posted successfully!</p>";
    echo "<script>window.location.href = 'index.html';</script>";
} else {
    echo "❌ Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
