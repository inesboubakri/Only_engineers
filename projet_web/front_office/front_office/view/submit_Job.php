<?php
include 'config.php'; // This should contain your DB connection ($conn)

// Fetch form values
$titre = $_POST['titre'];
$entreprise = $_POST['entreprise'];
$eplacement = $_POST['emplacement'];
$description = $_POST['description'];
$date = $_POST['date'];
$type = $_POST['type'];

// Insert into offer table
$sql = "INSERT INTO offre (titre, entreprise, eplacement, description, date,type)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $titre, $entreprise, $eplacement, $description, $date, $type);
if ($stmt->execute()) {
    echo "<p>🎉 Job posted successfully!</p>";
    echo "<script>window.location.href = 'index.html';</script>";
} else {
    echo "❌ Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
