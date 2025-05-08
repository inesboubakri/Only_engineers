<!-- filepath: c:\xampp\htdocs\projet_web\projet_web\front_office\front_office\view\apply_job.php -->
<?php
session_start();

if (isset($_SESSION['id'])) {
    $offre_id = $_SESSION['id'];
} else {
    echo "No job ID provided.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Job Application</title>
  <link rel="stylesheet" href="../view/job_styles2.css">
  <script src="../controller/job_script.js" defer></script>
</head>
<body>
  <form action="../controller/controller_apply.php" method="POST" enctype="multipart/form-data" id="applyJobForm">
    <input type="hidden" name="offre_id" value="<?= htmlspecialchars($offre_id) ?>">
    <div class="container">
      <div class="apply-box">
        <h1>Job Application</h1>

        <div class="form-container">
          <!-- First Name -->
          <div class="form-control">
            <label for="nom_candidat">First Name</label>
            <input type="text" id="nom_candidat" name="nom_candidat" placeholder="Enter First Name" required minlength="2">
            <small class="error-message"></small>
          </div>

          <!-- Last Name -->
          <div class="form-control">
            <label for="prenom_candidat">Last Name</label>
            <input type="text" id="prenom_candidat" name="prenom_candidat" placeholder="Enter Last Name" required minlength="2">
            <small class="error-message"></small>
          </div>

          <!-- Email -->
          <div class="form-control">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter email" required>
            <small class="error-message"></small>
          </div>

          <!-- Job Role -->
          <div class="form-control">
            <label for="role">Job Role</label>
            <select name="role" id="role" required>
              <option value="" disabled selected>Select a Role</option>
              <option value="Frontend">Front-End Web Developer</option>
              <option value="Backend">Back-End Web Developer</option>
              <option value="Full Stack">Full Stack Web Developer</option>
              <option value="Data Science">Data Scientist</option>
              <option value="IT">IT Support Specialist</option>
              <option value="DevOps">DevOps Engineer</option>
              <option value="UI">UI/UX Designer</option>
              <option value="Game">Game Developer</option>
            </select>
            <small class="error-message"></small>
          </div>

          <!-- Address -->
          <div class="form-control">
            <label for="adresse">Address</label>
            <textarea name="adresse" id="adresse" cols="30" rows="4" placeholder="Enter full address" required minlength="5"></textarea>
            <small class="error-message"></small>
          </div>

          <!-- City -->
          <div class="form-control">
            <label for="city">City</label>
            <input type="text" id="city" name="city" placeholder="Enter City" required minlength="2">
            <small class="error-message"></small>
          </div>

          <!-- Date -->
          <div class="form-control">
            <label for="Date">Date</label>
            <input type="date" id="Date" name="Date" value="<?= date('Y-m-d') ?>" required>
            <small class="error-message"></small>
          </div>

          <!-- Resume -->
          <div class="form-control">
            <label for="resume">Upload your CV</label>
            <input type="file" id="resume" name="resume" required>
            <small class="error-message"></small>
          </div>

          <!-- Submit Button -->
          <div class="button-container">
            <button type="submit">Register now</button>
          </div>
        </div>
      </div>
    </div>
  </form>

  <script>
    // JavaScript for client-side validation
    document.getElementById('applyJobForm').addEventListener('submit', function (event) {
      const form = event.target;
      let isValid = true;

      // Validate First Name
      const firstName = form.querySelector('#nom_candidat');
      if (firstName.value.trim().length < 2) {
        isValid = false;
        firstName.nextElementSibling.textContent = 'First name must be at least 2 characters.';
      } else {
        firstName.nextElementSibling.textContent = '';
      }

      // Validate Last Name
      const lastName = form.querySelector('#prenom_candidat');
      if (lastName.value.trim().length < 2) {
        isValid = false;
        lastName.nextElementSibling.textContent = 'Last name must be at least 2 characters.';
      } else {
        lastName.nextElementSibling.textContent = '';
      }

      // Validate Email
      const email = form.querySelector('#email');
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email.value.trim())) {
        isValid = false;
        email.nextElementSibling.textContent = 'Please enter a valid email address.';
      } else {
        email.nextElementSibling.textContent = '';
      }

      // Validate Address
      const address = form.querySelector('#adresse');
      if (address.value.trim().length < 5) {
        isValid = false;
        address.nextElementSibling.textContent = 'Address must be at least 5 characters.';
      } else {
        address.nextElementSibling.textContent = '';
      }

      // Validate City
      const city = form.querySelector('#city');
      if (city.value.trim().length < 2) {
        isValid = false;
        city.nextElementSibling.textContent = 'City must be at least 2 characters.';
      } else {
        city.nextElementSibling.textContent = '';
      }

      // Validate Resume
      const resume = form.querySelector('#resume');
      if (resume.files.length === 0) {
        isValid = false;
        resume.nextElementSibling.textContent = 'Please upload your CV.';
      } else {
        resume.nextElementSibling.textContent = '';
      }

      // Prevent form submission if validation fails
      if (!isValid) {
        event.preventDefault();
      }
    });
  </script>
</body>
</html>