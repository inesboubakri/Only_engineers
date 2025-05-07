<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["content"])) {
    // Validation et sanitisation de l'entrée
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');

    if (empty($content)) {
        echo json_encode([
            "success" => false,
            "message" => "Contenu vide ou invalide."
        ]);
        exit;
    }

    // Chemin de l'interpréteur Python dans l'environnement virtuel
    $python_interpreter = "C:\\xampp\\htdocs\\fpjw\\projet_web\\front_office\\front_office\\model\\venv\\Scripts\\python.exe";

    // Vérification de l'existence de l'interpréteur Python
    if (!file_exists($python_interpreter)) {
        echo json_encode([
            "success" => false,
            "message" => "Interpréteur Python introuvable dans l'environnement virtuel."
        ]);
        exit;
    }

    // Chemin du script Python
    $python = "C:\\xampp\\htdocs\\fpjw\\projet_web\\front_office\\front_office\\model\\QRcode.py";

    // Vérification de l'existence du fichier Python
    if (!file_exists($python)) {
        echo json_encode([
            "success" => false,
            "message" => "Script Python introuvable."
        ]);
        exit;
    }

    // Échappement sécurisé du contenu et des chemins
    $escaped_content = escapeshellarg($content);
    $escaped_python = escapeshellarg($python);
    $escaped_python_interpreter = escapeshellarg($python_interpreter);

    // Construction de la commande
    $command = "{$escaped_python_interpreter} {$escaped_python} {$escaped_content} 2>&1";

    // Exécution de la commande
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);

    // Ajout de logs pour le débogage
    error_log("Commande exécutée : $command");
    error_log("Sortie : " . implode("\n", $output));

    // Gestion du résultat
    if ($return_var === 0) {
        // Vérification de l'existence du fichier QR code généré
        $qr_path = "C:\\xampp\\htdocs\\fpjw\\projet_web\\front_office\\front_office\\qrcode.png";
        if (file_exists($qr_path)) {
            echo json_encode([
                "success" => true,
                "path" => $qr_path
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "QR code non généré."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de l'exécution du script Python: " . implode("\n", $output)
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Requête invalide."
    ]);
}
?>