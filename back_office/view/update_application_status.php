<?php
define('BASE_PATH', dirname(__DIR__, 2)); // Points to C:\xampp\htdocs\projet_web\projet_web
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $pdo = config::getConnexion();

    // Check if ID and status are provided
    if (!isset($_GET['ID']) || !is_numeric($_GET['ID']) || !isset($_GET['status']) || !in_array($_GET['status'], ['accepted', 'rejected'])) {
        die("Invalid or missing parameters");
    }

    $id = $_GET['ID'];
    $status = $_GET['status'];

    // Fetch application details
    $stmt = $pdo->prepare("SELECT nom_candidat, prenom_candidat, email, role FROM candidature WHERE ID = :id");
    $stmt->execute([':id' => $id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        die("Application not found");
    }

    // Update status
    $stmt = $pdo->prepare("UPDATE candidature SET status = :status WHERE ID = :id");
    $stmt->execute([':status' => $status, ':id' => $id]);

    // Send email
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Update with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'qmayra1234@gmail.com'; // Your Gmail email
        $mail->Password = 'wknymemamuonryyi'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Job Portal Admin');
        $mail->addAddress($application['email'], $application['nom_candidat'] . ' ' . $application['prenom_candidat']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Job Application Status Update';
        $mail->Body = $status === 'accepted' ?
            "<h3>Dear {$application['nom_candidat']} {$application['prenom_candidat']},</h3>
             <p>Congratulations! Your application for the <strong>{$application['role']}</strong> position has been <strong>accepted</strong>. We will contact you soon with the next steps.</p>
             <p>Thank you for applying!</p>" :
            "<h3>Dear {$application['nom_candidat']} {$application['prenom_candidat']},</h3>
             <p>Thank you for applying for the <strong>{$application['role']}</strong> position. Unfortunately, your application has been <strong>rejected</strong>. We appreciate your interest and wish you the best in your future endeavors.</p>";

        $mail->send();
    } catch (Exception $e) {
        die("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    // Redirect back to jobs.php
    header('Location: jobs.php?message=Application ' . $status . ' successfully!');
    exit;
} catch (Exception $e) {
    die("Error updating status: " . $e->getMessage());
}
?>