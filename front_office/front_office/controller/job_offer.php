<?php
require_once __DIR__ . '/../../../config.php';
session_start();

class job_offer {
    public static function addOffre($data) {
        $sql = "INSERT INTO offre (titre, entreprise, emplacement, description, date, type, Email) 
                VALUES (:titre, :entreprise, :emplacement, :description, :date, :type, :Email)";

        try {
            $stmt = config::getConnexion()->prepare($sql);
            $stmt->bindParam(':titre', $data['titre']);
            $stmt->bindParam(':entreprise', $data['entreprise']);
            $stmt->bindParam(':emplacement', $data['emplacement']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':date', $data['date']);
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':Email', $data['Email']);
            $stmt->execute();

            return "Job offer added successfully!";
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}

//  Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //  Handle "Other" job title case here
    $titre = ($_POST['titre'] === 'Other' && !empty($_POST['OtherJobTitle']))
        ? htmlspecialchars($_POST['OtherJobTitle'])
        : htmlspecialchars($_POST['titre']);

    $data = [
        'titre' => $titre,
        'entreprise' => htmlspecialchars($_POST['entreprise']),
        'emplacement' => htmlspecialchars($_POST['emplacement']),
        'description' => htmlspecialchars($_POST['description']),
        'date' => htmlspecialchars($_POST['date']),
        'type' => htmlspecialchars($_POST['type']),
        'Email' => htmlspecialchars($_POST['Email']) // Or from session
    ];

    $message = job_offer::addOffre($data);
    
    header("Location: ../view/index.php?message=" . urlencode($message));
    exit();
}
