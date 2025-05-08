<?php
   require_once __DIR__ . '/../../../config.php';
   require_once __DIR__ . '/../../../vendor/autoload.php'; // For PdfParser

   use Smalot\PdfParser\Parser;

   // Check if a session is already active
   if (session_status() === PHP_SESSION_NONE) {
       session_start();
   }

   // Handle the GET request to store the job ID in the session and redirect to the application form
   if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
       $offre_id = $_GET['id'];

       try {
           // Store the `offre_id` in the session
           $_SESSION['id'] = $offre_id;

           // Redirect to the application form
           header("Location: ../view/apply_job.php");
           exit();
       } catch (Exception $e) {
           echo "Error: " . $e->getMessage();
           exit();
       }
   }

   // Handle the POST request to process the job application
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       if (isset($_SESSION['id'])) {
           $offre_id = $_SESSION['id'];

           // Retrieve form data
           $data = [
               'offre_id' => $offre_id,
               'nom_candidat' => htmlspecialchars($_POST['nom_candidat']),
               'prenom_candidat' => htmlspecialchars($_POST['prenom_candidat']),
               'email' => htmlspecialchars($_POST['email']),
               'role' => htmlspecialchars($_POST['role']),
               'adresse' => htmlspecialchars($_POST['adresse']),
               'city' => htmlspecialchars($_POST['city']),
               'Date' => htmlspecialchars($_POST['Date']),
           ];

           // Handle file upload
           if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
               $uploadDir = __DIR__ . '/../view/uploads/';
               $resumeFileName = uniqid() . '_' . basename($_FILES['resume']['name']);
               $resumePath = $uploadDir . $resumeFileName;

               // Check if the uploads directory exists, and create it if it doesn't
               if (!is_dir($uploadDir)) {
                   mkdir($uploadDir, 0777, true);
               }

               // Move the uploaded file to the uploads directory
               if (move_uploaded_file($_FILES['resume']['tmp_name'], $resumePath)) {
                   // Store the relative path in the database
                   $data['resume'] = 'uploads/' . $resumeFileName;

                   // Extract skills from PDF
                   try {
                       $parser = new Parser();
                       $pdf = $parser->parseFile($resumePath);
                       $text = $pdf->getText();

                       // Debug: Log extracted text (first 500 chars)
                       file_put_contents(__DIR__ . '/debug.txt', "Extracted Text: " . substr($text, 0, 500) . "\n");

                       // Define desired skills
                       $desiredSkills = ['PHP', 'JavaScript', 'SQL', 'HTML', 'CSS', 'Python'];
                       $matchedSkills = [];

                       // Case-insensitive matching
                       foreach ($desiredSkills as $skill) {
                           if (stripos($text, $skill) !== false) {
                               $matchedSkills[] = $skill;
                           }
                       }
                       $data['skills'] = implode(', ', $matchedSkills);

                       // Debug: Log matched skills
                       file_put_contents(__DIR__ . '/debug.txt', "Matched Skills: " . $data['skills'] . "\n", FILE_APPEND);
                   } catch (Exception $e) {
                       $data['skills'] = '';
                       file_put_contents(__DIR__ . '/debug.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
                   }
               } else {
                   echo "Error uploading file.";
                   exit();
               }
           } else {
               echo "No file uploaded or an error occurred.";
               exit();
           }

           // Process the application
           $message = apply_job::applyOffre($data);

           // Redirect to the list of job applications
           header("Location: http://localhost:8888/projet_web/projet_web/back_office/view/jobs.php");
           exit();
       } else {
           echo "No job ID provided in session.";
           exit();
       }
   }

   // Define the `apply_job` class
   class apply_job {
       public static function applyOffre($data) {
           $sql = "INSERT INTO candidature (offre_id, nom_candidat, prenom_candidat, email, resume, Date, role, adresse, city, status, skills) 
                   VALUES (:offre_id, :nom_candidat, :prenom_candidat, :email, :resume, :Date, :role, :adresse, :city, 'pending', :skills)";

           try {
               $stmt = config::getConnexion()->prepare($sql);
               $stmt->bindParam(':offre_id', $data['offre_id']);
               $stmt->bindParam(':nom_candidat', $data['nom_candidat']);
               $stmt->bindParam(':prenom_candidat', $data['prenom_candidat']);
               $stmt->bindParam(':email', $data['email']);
               $stmt->bindParam(':resume', $data['resume']);
               $stmt->bindParam(':Date', $data['Date']);
               $stmt->bindParam(':role', $data['role']);
               $stmt->bindParam(':adresse', $data['adresse']);
               $stmt->bindParam(':city', $data['city']);
               $stmt->bindParam(':skills', $data['skills']);

               $stmt->execute();

               return "Successful Job application!";
           } catch (Exception $e) {
               return "Error: " . $e->getMessage();
           }
       }
   }
   ?>