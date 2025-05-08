<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Job</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 30px;
      background-color: #f8f9fa;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    form {
      max-width: 600px;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }
    input[type="text"], input[type="date"], textarea, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    textarea {
      resize: vertical;
    }
    input[type="submit"] {
      margin-top: 20px;
      padding: 10px 15px;
      background-color: #28a745;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    input[type="submit"]:hover {
      background-color: #218838;
    }
    .message {
      text-align: center;
      color: green;
    }
  </style>
</head>
<body>

<h1>Edit Job Offer</h1>

<?php
// Your PHP logic stays the same...
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = config::getConnexion()->prepare("SELECT * FROM offre WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            echo "<p class='message'>No job found with ID: " . htmlspecialchars($id) . "</p>";
            exit();
        }
    } catch (Exception $e) {
        echo "<p class='message'>Error: " . $e->getMessage() . "</p>";
        exit();
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $titre = $_POST['titre'];
    $entreprise = $_POST['entreprise'];
    $emplacement = $_POST['emplacement'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $type = $_POST['type'];

    try {
        $stmt = config::getConnexion()->prepare("
            UPDATE offre 
            SET titre = :titre, entreprise = :entreprise, emplacement = :emplacement, 
                description = :description, date = :date, type = :type 
            WHERE id = :id");

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':entreprise', $entreprise);
        $stmt->bindParam(':emplacement', $emplacement);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':type', $type);
        $stmt->execute();

        echo "<p class='message'>Job updated successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='message'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<?php if (isset($job) && $job): ?>
<form method="POST" action="edit_Job.php">
    <input type="hidden" name="id" value="<?= htmlspecialchars($job['id']) ?>">

    <select name="titre" id="titre" onchange="toggleOtherField()">
      <option value="Frontend" <?= $job['titre'] == 'Frontend' ? 'selected' : '' ?>>Front-End Web Developer</option>
      <option value="Backend" <?= $job['titre'] == 'Backend' ? 'selected' : '' ?>>Back-End Web Developer</option>
      <option value="Full Stack" <?= $job['titre'] == 'Full Stack' ? 'selected' : '' ?>>Full Stack Web Developer</option>
      <option value="Data Science" <?= $job['titre'] == 'Data Science' ? 'selected' : '' ?>>Data Scientist</option>
      <option value="IT" <?= $job['titre'] == 'IT' ? 'selected' : '' ?>>IT Support Specialist</option>
      <option value="DevOps" <?= $job['titre'] == 'DevOps' ? 'selected' : '' ?>>DevOps Engineer</option>
      <option value="UI" <?= $job['titre'] == 'UI' ? 'selected' : '' ?>>UI/UX Designer</option>
      <option value="Game" <?= $job['titre'] == 'Game' ? 'selected' : '' ?>>Game Developer</option>
      <option value="Other" <?= $job['titre'] == 'Other' ? 'selected' : '' ?>>Other</option>
  </select>
        <input type="text" id="OtherJobTitle" name="OtherJobTitle" placeholder="Enter Job Title" style="display:none;">


    <label>Entreprise:</label>
    <input type="text" name="entreprise" value="<?= htmlspecialchars($job['entreprise']) ?>">

    <label>Emplacement:</label>
    <input type="text" name="emplacement" value="<?= htmlspecialchars($job['emplacement']) ?>">

    <label>Description:</label>
    <textarea name="description"><?= htmlspecialchars($job['description']) ?></textarea>

    <label>Date:</label>
    <input type="date" name="date" value="<?= htmlspecialchars($job['date']) ?>">

    <label>Type:</label>
    <select name="type">
        <option value="full-time" <?= $job['type'] == 'full-time' ? 'selected' : '' ?>>Full Time</option>
        <option value="part-time" <?= $job['type'] == 'part-time' ? 'selected' : '' ?>>Part Time</option>
        <option value="internship" <?= $job['type'] == 'internship' ? 'selected' : '' ?>>Internship</option>
    </select>

    <input type="submit" value="Update Job">
</form>
<?php else: ?>
<p class="message">No job data available to edit.</p>
<?php endif; ?>

</body>
</html>
