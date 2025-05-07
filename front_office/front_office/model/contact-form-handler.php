<?php
// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];

// Email recipient (your email)
$to = "boubakriines11@gmail.com";

// Email headers
$headers = "From: " . $email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Email body
$email_body = "
<html>
<head>
  <title>New Contact Form Submission</title>
</head>
<body>
  <h2>Contact Form Submission</h2>
  <p><strong>Name:</strong> $name</p>
  <p><strong>Email:</strong> $email</p>
  <p><strong>Subject:</strong> $subject</p>
  <p><strong>Message:</strong></p>
  <p>" . nl2br($message) . "</p>
</body>
</html>
";

// Send email
$success = mail($to, "New Contact Form: $subject", $email_body, $headers);

// Redirect back to the form page with status
if ($success) {
    header("Location: home.html?status=success#contact-section");
} else {
    header("Location: home.html?status=error#contact-section");
}
?>
