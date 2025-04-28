<?php
require_once('../../../back_office/controller/db_connection.php');

if (!isset($_GET['id'])) {
    echo "ID de projet manquant.";
    exit;
}
$id = intval($_GET['id']);

// 1) DELETE
if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    $stmt = $conn->prepare("SELECT fichier FROM depot WHERE id = ?");
    $stmt->execute([$delId]);
    $depot = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($depot && file_exists($depot['fichier'])) {
        unlink($depot['fichier']);
    }
    $stmtDel = $conn->prepare("DELETE FROM depot WHERE id = ?");
    $stmtDel->execute([$delId]);
    header("Location: detail.php?id={$id}");
    exit;
}

// 2) FETCH project
$stmt = $conn->prepare("SELECT * FROM projet WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) {
    echo "Projet non trouvé.";
    exit;
}

// 3) HANDLE ADD / EDIT
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'];
    $date_depot  = trim($_POST['date_depot']);
    $commentaire = trim($_POST['commentaire']);
    $statut      = $_POST['statut'];

    $uploadDir = 'uploads/';
    $filePath = null;

    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['fichier']['tmp_name'];
        $fileName = $_FILES['fichier']['name'];
        $fileSize = $_FILES['fichier']['size'];
        $fileType = $_FILES['fichier']['type'];

        $allowedExtensions = ['pdf', 'docx', 'jpg', 'png', 'txt', 'zip'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = "Format de fichier non valide. (pdf, docx, jpg, png, txt, zip)";
        }

        if ($fileSize > 5 * 1024 * 1024) {
            $errors[] = "Le fichier est trop volumineux.";
        }

        if (empty($errors)) {
            $fileNameNew = time() . '-' . basename($fileName);
            $filePath = $uploadDir . $fileNameNew;

            if (!move_uploaded_file($fileTmpPath, $filePath)) {
                $errors[] = "Erreur lors du téléchargement du fichier.";
            }
        }
    }

    if (!$date_depot) {
        $errors[] = "La date du dépôt est requise.";
    } elseif (strtotime($date_depot) > time()) {
        $errors[] = "La date ne peut pas être dans le futur.";
    }
    if (strlen($commentaire) < 5) {
        $errors[] = "Le commentaire doit contenir au moins 5 caractères.";
    }
    if (!in_array($statut, ['Soumis','non soumis'])) {
        $errors[] = "Le statut est invalide.";
    }

    if (empty($errors)) {
        try {
            if ($action === 'add') {
                $stmtIns = $conn->prepare("
                    INSERT INTO depot (projet_id, date_depot, commentaire, statut, fichier)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmtIns->execute([$id, $date_depot, $commentaire, $statut, $filePath]);
                header("Location: detail.php?id={$id}");
                exit;
            } elseif ($action === 'edit') {
                $depotId = intval($_POST['depot_id']);
                $stmt = $conn->prepare("SELECT fichier FROM depot WHERE id = ?");
                $stmt->execute([$depotId]);
                $existingDepot = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($filePath && file_exists($existingDepot['fichier'])) {
                    unlink($existingDepot['fichier']);
                }

                $stmtUpd = $conn->prepare("
                    UPDATE depot
                    SET date_depot = ?, commentaire = ?, statut = ?, fichier = ?
                    WHERE id = ?
                ");
                $stmtUpd->execute([$date_depot, $commentaire, $statut, $filePath, $depotId]);
                header("Location: detail.php?id={$id}");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout ou modification du dépôt : " . $e->getMessage();
        }
    }
}

// 4) FETCH depots
$stmt2 = $conn->prepare("SELECT * FROM depot WHERE projet_id = ?");
$stmt2->execute([$id]);
$depots = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// 5) Fetch depot to edit if action is "edit"
$depotToEdit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['depot_id'])) {
    $depotIdToEdit = intval($_GET['depot_id']);
    $stmtEdit = $conn->prepare("SELECT * FROM depot WHERE id = ?");
    $stmtEdit->execute([$depotIdToEdit]);
    $depotToEdit = $stmtEdit->fetch(PDO::FETCH_ASSOC);
    if (!$depotToEdit) {
        echo "Depot à modifier non trouvé.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Détail Projet #<?=htmlspecialchars($project['id'])?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: #f9f9f9;
    }
    .container {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
    }
    h1, h2, h3 {
      color: #333;
    }
    .btn {
      display: inline-block;
      padding: 8px 16px;
      margin: 5px 0;
      background-color: #4CAF50;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }
    .btn-back {
      background-color: #555;
    }
    .btn-add {
      background-color: #2196F3;
    }
    .btn-edit {
      background-color: #FFC107;
      color: black;
    }
    .btn-delete {
      background-color: #F44336;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }
    th {
      background-color: #f2f2f2;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    .form-group input[type="date"],
    .form-group select,
    .form-group textarea,
    .form-group input[type="file"] {
      width: 100%;
      padding: 8px;
      box-sizing: border-box;
    }
    .empty {
      text-align: center;
      font-style: italic;
      color: #888;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="projects.php" class="btn btn-back">&larr; Retour</a>
    <h1><?=htmlspecialchars($project['project'])?></h1>

    <h2>Dépôts</h2>

    <a href="?id=<?= $id ?>&action=add" class="btn btn-add">Ajouter un dépôt</a>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
      <h3>Ajouter un nouveau dépôt</h3>
      <form action="detail.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" onsubmit="return validateForm(this)">
        <input type="hidden" name="action" value="add" />
        <div class="form-group">
          <label for="date_depot">Date de Dépôt</label>
          <input type="date" name="date_depot" required />
        </div>
        <div class="form-group">
          <label for="commentaire">Commentaire</label>
          <textarea name="commentaire" rows="4" required></textarea>
        </div>
        <div class="form-group">
          <label for="statut">Statut</label>
          <select name="statut" required>
            <option value="Soumis">Soumis</option>
            <option value="non soumis">non soumis</option>
          </select>
        </div>
        <div class="form-group">
          <label for="fichier">Fichier</label>
          <input type="file" name="fichier" />
        </div>
        <button type="submit" class="btn btn-add">Ajouter</button>
      </form>
    <?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && $depotToEdit): ?>
      <h3>Modifier le dépôt</h3>
      <form action="detail.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" onsubmit="return validateForm(this)">
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="depot_id" value="<?= $depotToEdit['id'] ?>" />
        <div class="form-group">
          <label for="date_depot">Date de Dépôt</label>
          <input type="date" name="date_depot" value="<?= htmlspecialchars($depotToEdit['date_depot']) ?>" required />
        </div>
        <div class="form-group">
          <label for="commentaire">Commentaire</label>
          <textarea name="commentaire" rows="4" required><?= htmlspecialchars($depotToEdit['commentaire']) ?></textarea>
        </div>
        <div class="form-group">
          <label for="statut">Statut</label>
          <select name="statut" required>
            <option value="Soumis" <?= $depotToEdit['statut'] == 'Soumis' ? 'selected' : '' ?>>Soumis</option>
            <option value="non soumis" <?= $depotToEdit['statut'] == 'non soumis' ? 'selected' : '' ?>>non soumis</option>
          </select>
        </div>
        <div class="form-group">
          <label for="fichier">Fichier (Actuel: <?= $depotToEdit['fichier'] ? basename($depotToEdit['fichier']) : 'Aucun' ?>)</label>
          <input type="file" name="fichier" />
        </div>
        <button type="submit" class="btn btn-edit">Modifier</button>
      </form>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Commentaire</th>
          <th>Statut</th>
          <th>Fichier</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($depots): ?>
          <?php foreach ($depots as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['date_depot']) ?></td>
              <td><?= htmlspecialchars($d['commentaire']) ?></td>
              <td><?= htmlspecialchars($d['statut']) ?></td>
              <td>
                <?php if ($d['fichier']): ?>
                  <a href="<?= $d['fichier'] ?>" target="_blank">Télécharger</a>
                <?php endif; ?>
              </td>
              <td>
                <a href="?id=<?= $id ?>&action=edit&depot_id=<?= $d['id'] ?>" class="btn btn-edit">Modifier</a>
                <a href="?id=<?= $id ?>&delete_id=<?= $d['id'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce dépôt ?')">Supprimer</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="empty">Aucun dépôt enregistré.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<script>
function validateForm(form) {
  const commentaire = form.commentaire.value.trim();
  const dateDepot = form.date_depot.value;
  const today = new Date();
  const depotDate = new Date(dateDepot);

  let errors = [];

  // 1. Commentaire validation
  if (commentaire.length < 5) {
    errors.push("Le commentaire doit contenir au moins 5 caractères.");
  }

  // 2. Date validation
  if (!dateDepot) {
    errors.push("La date de dépôt est obligatoire.");
  } else if (depotDate > today) {
    errors.push("La date de dépôt ne peut pas être dans le futur.");
  }

  // 3. Show errors if any
  if (errors.length > 0) {
    alert(errors.join("\n"));
    return false; // Block form submission
  }

  return true; // Allow form submission
}
</script>
</body>
</html>
