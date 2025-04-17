<?php
// Include the config file to connect to the database
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form submission
    $titre = $_POST['titre'];
    $entreprise = $_POST['entreprise'];
    $emplacement = $_POST['emplacement'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $type = $_POST['type'];

    // Prepare the SQL query
    $sql = "INSERT INTO offre (titre, entreprise, emplacement, description, date, type) 
            VALUES (:titre, :entreprise, :emplacement, :description, :date, :type)";

    try {
        // Prepare the statement
        $stmt = config::getConnexion()->prepare($sql);

        // Bind the values to the query
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':entreprise', $entreprise);
        $stmt->bindParam(':emplacement', $emplacement);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':type', $type);

        // Execute the query
        $stmt->execute();

        // If successful, show a success message
        echo "Job offer added successfully!";
    } catch (Exception $e) {
        // Catch any errors and display an error message
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job</title>
</head>
<body>

    <h1>Add Job Offer</h1>

    <form action="add_Job.php" method="POST">
        <label for="titre">Job Title:</label>
        <input type="text" id="titre" name="titre" required><br><br>

        <label for="entreprise">Company Name:</label>
        <input type="text" id="entreprise" name="entreprise" required><br><br>

        <label for="emplacement">Location:</label>
        <input type="text" id="emplacement" name="emplacement" required><br><br>

        <label for="description">Job Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="date">Job Date:</label>
        <input type="date" id="date" name="date" required><br><br>

        <label for="type">Job Type:</label>
        <select id="type" name="type" required>
            <option value="full-time">Full Time</option>
            <option value="part-time">Part Time</option>
            <option value="internship">Internship</option>
        </select><br><br>

        <input type="submit" value="Add Job">
    </form>

</body>
</html>
