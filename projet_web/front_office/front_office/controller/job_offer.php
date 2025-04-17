<?php
// job_offer_controller.php (Controller)

include '../../projet_web/config.php'; // DB connection
include 'job_offer.php'; // Model

class JobOfferController 
{
    private $jobOffer;

    public function __construct($db) {
        $this->jobOffer = new JobOffer($db);
    }

    public function handleFormSubmission() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $titre = $_POST['titre'];
            $entreprise = $_POST['entreprise'];
            $emplacement = $_POST['emplacement'];
            $description = $_POST['description'];
            $date = $_POST['date'];
            $type = $_POST['type'];

            if ($this->jobOffer->addJobOffer($titre, $entreprise, $emplacement, $description, $date, $type)) {
                echo "<p>🎉 Job posted successfully!</p>";
                echo "<script>window.location.href = 'index.html';</script>";
            } else {
                echo "❌ Error: Could not post the job offer.";
            }
        }
    }

    public function showJobOffer($offer) {
        echo '<table border="1" width="100%">
            <tr align="center">
                <th>Title</th>
                <th>Company</th>
                <th>Location</th>
                <th>Description</th>
                <th>Date</th>
                <th>Type</th>
            </tr>
            <tr>
                <td>'. htmlspecialchars($offer->getTitre()) .'</td>
                <td>'. htmlspecialchars($offer->getEntreprise()) .'</td>
                <td>'. htmlspecialchars($offer->getEmplacement()) .'</td>
                <td>'. htmlspecialchars($offer->getDescription()) .'</td>
                <td>'. htmlspecialchars($offer->getDate()) .'</td>
                <td>'. htmlspecialchars($offer->getType()) .'</td>
            </tr>
        </table>';
    }
}

// Example usage
$controller = new JobOfferController(Config::getConnexion());
$controller->handleFormSubmission();
?>
