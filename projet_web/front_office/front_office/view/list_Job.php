<?php
require_once('../../../../config.php');

try {
    $stmt = config::getConnexion()->prepare("SELECT * FROM offre ORDER BY date DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<h2>Liste des Offres d'Emploi</h2>

<a href="add_Job.php">➕ Ajouter une offre</a>
<br><br>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Titre</th>
        <th>Entreprise</th>
        <th>Emplacement</th>
        <th>Description</th>
        <th>Date</th>
        <th>Type</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($jobs as $job): ?>
        <tr>
            <td><?= $job['id'] ?></td>
            <td><?= $job['titre'] ?></td>
            <td><?= $job['entreprise'] ?></td>
            <td><?= $job['emplacement'] ?></td>
            <td><?= $job['description'] ?></td>
            <td><?= $job['date'] ?></td>
            <td><?= $job['type'] ?></td>
            <td>
                <a href="edit_Job.php?id=<?= $job['id'] ?>">✏️ Edit</a> |
                <a href="delete_Job.php?id=<?= $job['id'] ?>" onclick="return confirm('Are you sure?')">🗑 Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
