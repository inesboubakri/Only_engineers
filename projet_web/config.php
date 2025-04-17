<?php

// Déclaration de la classe config
class config

{   
    // Déclaration d'une variable privée statique pour stocker l'objet PDO
    private static $pdo = null;

    // Méthode statique pour obtenir la connexion à la base de données
    public static function getConnexion()

    {

        // Vérifie si la connexion PDO n'est pas encore établie
        if (!isset(self::$pdo)) {

            // Définition des paramètres de connexion
            $servername = "localhost";  // Nom du serveur
            $username = "root";         // Nom d'utilisateur de la base de données
            $password = "";     // Mot de passe de la base de données
            $dbname = "onlyengineers";       // Nom de la base de données

            try {

                // Création d'une instance PDO pour la connexion à la base de données
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname",
                        $username,
                        $password
                );

                // Configuration du mode d'erreur pour afficher les exceptions en cas d'erreur
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Configuration du mode de récupération par défaut en tableau associatif
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Affichage d'un message de succès si la connexion est réussie
                echo "connected successfully";

            } catch (Exception $e) {

                // En cas d'erreur, affichage du message et arrêt du script
                die('Erreur: ' . $e->getMessage());

            }

        }

        // Retourne l'objet PDO pour réutilisation
        return self::$pdo;

    }

}

// Appel de la méthode pour établir la connexion
config::getConnexion();

?>
