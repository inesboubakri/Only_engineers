<?php
try {
    // Vérifier si les extensions nécessaires sont chargées
    if (!extension_loaded('pdo')) {
        throw new Exception("L'extension PDO n'est pas activée. Veuillez l'activer dans php.ini");
    }
    
    if (!extension_loaded('pdo_mysql')) {
        throw new Exception("L'extension PDO MySQL n'est pas activée. Veuillez l'activer dans php.ini en décommentant la ligne ;extension=pdo_mysql");
    }
    
    $host = 'localhost';
    $dbname = 'onlyengineers';
    $username = 'root';
    $password = '';
    
    // Test de connexion
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données : " . $e->getMessage());
        throw new Exception("Impossible de se connecter à la base de données. Vérifiez que MySQL est démarré et que la base de données existe.");
    }
    
} catch (Exception $e) {
    // Log l'erreur pour le débogage
    error_log("Erreur dans db_connect.php : " . $e->getMessage());
    
    // Retourner une erreur formatée
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
?>