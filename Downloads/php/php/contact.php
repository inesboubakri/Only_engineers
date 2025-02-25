<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats du formulaire</title>
</head>
<body>

    <h2>Informations envoyées</h2>

    <?php
   =
    if (
        empty($_GET['nom']) || empty($_GET['prenom']) || empty($_GET['email']) ||
        empty($_GET['telephone']) || empty($_GET['adresse']) || empty($_GET['code_postal'])
    ) {
        echo "<p class='error'>Champs manquants</p>";
    } else {
       
        echo "<table>
                <tr><th>Champ</th><th>Valeur</th></tr>
                <tr><td>Nom</td><td>" . htmlspecialchars($_GET['nom']) . "</td></tr>
                <tr><td>Prénom</td><td>" . htmlspecialchars($_GET['prenom']) . "</td></tr>
                <tr><td>Email</td><td>" . htmlspecialchars($_GET['email']) . "</td></tr>
                <tr><td>Téléphone</td><td>" . htmlspecialchars($_GET['telephone']) . "</td></tr>
                <tr><td>Adresse</td><td>" . htmlspecialchars($_GET['adresse']) . "</td></tr>
                <tr><td>Code Postal</td><td>" . htmlspecialchars($_GET['code_postal']) . "</td></tr>
              </table>";
    }
    ?>

</body>
</html>
