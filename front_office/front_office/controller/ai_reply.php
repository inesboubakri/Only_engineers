<?php
// Texte à tokeniser
$texte = "Bonjour, je m'appelle Yasmine et je travaille sur un projet NLP.";

// Échapper les guillemets pour éviter les erreurs de ligne de commande
$texte_echappe = escapeshellarg($texte);

// Exécuter le script Python
$commande = "python tokenizer.py $texte_echappe";
$output = shell_exec($commande);

// Décoder les tokens depuis JSON
$tokens = json_decode($output, true);

// Afficher les résultats
echo "<pre>";
print_r($tokens);
echo "</pre>";
?>
