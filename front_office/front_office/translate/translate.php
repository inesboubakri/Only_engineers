<?php

if (isset($_POST['texte']) && isset($_POST['langue_source']) && isset($_POST['langue_destination'])) {
    // Récupérer les données envoyées via POST
    $texte = $_POST['texte'];
    $langue_source = $_POST['langue_source'];
    $langue_destination = $_POST['langue_destination'];

    // Commande pour appeler le script Python
    $command = escapeshellcmd("C:/Python39/python.exe C:/xampp/htdocs/fpjw/projet_web/front_office/front_office/translate/traduction_script.py '$texte' '$langue_source' '$langue_destination'");
    
    // Exécuter la commande et récupérer la traduction
    $output = shell_exec($command);

    // Retourner la traduction au format JSON
    echo json_encode(['traduction' => $output]);
} else {
    echo json_encode(['error' => 'Données manquantes']);
}

?>
