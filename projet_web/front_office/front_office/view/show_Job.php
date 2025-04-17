<?php
include 'job_offer.php';          // The JobOffer model
include 'job_offer_controller.php';  // The Controller

// Create a sample job offer
$job = new JobOffer(
    "Frontend Developer",
    "TechCorp",
    "Tunis",
    "Develop responsive UIs using React.",
    "2025-04-17",
    "Full-Time"
);

// Create the controller
$controller = new JobOfferController();

// Show the job offer
$controller->showJobOffer($job);
?>
