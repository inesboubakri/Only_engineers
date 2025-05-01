<?php
session_start();
require_once '../model/db_connection.php';
require_once '../model/get_hackathons.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php?error=login_required");
    exit();
}

// Check if hackathon ID is provided
if (!isset($_GET['hackathon_id'])) {
    header("Location: hackathons.php");
    exit();
}

$hackathonId = (int)$_GET['hackathon_id'];

// Fetch hackathon details
try {
    $conn = getConnection();
    $sql = "SELECT * FROM hackathons WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $hackathonId);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: hackathons.php?error=invalid_hackathon");
        exit();
    }
    
    $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header("Location: hackathons.php?error=database_error");
    exit();
}

// Fetch user info
try {
    $userSql = "SELECT * FROM users WHERE user_id = :id";
    $userStmt = $conn->prepare($userSql);
    $userStmt->bindParam(":id", $_SESSION['user_id']);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Just continue if we can't get user data
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for <?php echo htmlspecialchars($hackathon['title']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .hidden {
            display: none;
        }
        .team-member {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Register for <?php echo htmlspecialchars($hackathon['title']); ?></h1>
        
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php
            switch ($_GET['error']) {
                case 'missing_fields':
                    echo "Please fill in all required fields.";
                    break;
                case 'photo_required':
                    echo "Please upload your photo.";
                    break;
                case 'photo_upload':
                    $message = isset($_GET['message']) ? $_GET['message'] : "Error uploading photo.";
                    echo htmlspecialchars($message);
                    break;
                case 'registration_failed':
                    $message = isset($_GET['message']) ? $_GET['message'] : "Registration failed. Please try again.";
                    echo htmlspecialchars($message);
                    break;
                case 'missing_leader_fields':
                    echo "Please fill in all team leader fields.";
                    break;
                case 'leader_photo_required':
                    echo "Please upload the team leader's photo.";
                    break;
                case 'no_team_members':
                    echo "Please add at least one team member.";
                    break;
                case 'missing_member_fields':
                    $member = isset($_GET['member']) ? (int)$_GET['member'] : 0;
                    echo "Please fill in all fields for team member #$member.";
                    break;
                case 'member_photo_required':
                    $member = isset($_GET['member']) ? (int)$_GET['member'] : 0;
                    echo "Please upload a photo for team member #$member.";
                    break;
                default:
                    echo "An error occurred. Please try again.";
            }
            ?>
        </div>
        <?php endif; ?>
        
        <div class="mb-4">
            <label class="form-label fw-bold">Registration Type:</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="participation_type_selector" id="individual" value="individual" checked>
                <label class="form-check-label" for="individual">
                    Individual Registration
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="participation_type_selector" id="team" value="team">
                <label class="form-check-label" for="team">
                    Team Registration
                </label>
            </div>
        </div>
        
        <!-- Individual Registration Form -->
        <form id="individual-form" action="../controller/hackathon-registration.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="hackathon_id" value="<?php echo $hackathonId; ?>">
            <input type="hidden" name="participation_type" value="individual">
            
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="photo">Your Photo:</label>
                <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                <small class="form-text text-muted">Upload a clear photo of yourself. Maximum size: 5MB. Accepted formats: JPG, PNG, GIF.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        
        <!-- Team Registration Form -->
        <form id="team-form" class="hidden" action="../controller/hackathon-registration.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="hackathon_id" value="<?php echo $hackathonId; ?>">
            <input type="hidden" name="participation_type" value="team">
            
            <h3>Team Leader Information</h3>
            <div class="form-group">
                <label for="team_leader_name">Team Leader Name:</label>
                <input type="text" class="form-control" id="team_leader_name" name="team_leader_name" value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="team_leader_email">Team Leader Email:</label>
                <input type="email" class="form-control" id="team_leader_email" name="team_leader_email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="team_leader_phone">Team Leader Phone:</label>
                <input type="tel" class="form-control" id="team_leader_phone" name="team_leader_phone" required>
            </div>
            
            <div class="form-group">
                <label for="team_leader_photo">Team Leader Photo:</label>
                <input type="file" class="form-control" id="team_leader_photo" name="team_leader_photo" accept="image/*" required>
                <small class="form-text text-muted">Upload a clear photo. Maximum size: 5MB. Accepted formats: JPG, PNG, GIF.</small>
            </div>
            
            <h3>Team Members</h3>
            <div class="team-members-container">
                <div class="team-member" id="member-1">
                    <h4>Team Member #1</h4>
                    <div class="form-group">
                        <label for="member_1_name">Full Name:</label>
                        <input type="text" class="form-control" id="member_1_name" name="member_1_name" required>
                    </div>
                    <div class="form-group">
                        <label for="member_1_email">Email:</label>
                        <input type="email" class="form-control" id="member_1_email" name="member_1_email" required>
                    </div>
                    <div class="form-group">
                        <label for="member_1_phone">Phone Number:</label>
                        <input type="tel" class="form-control" id="member_1_phone" name="member_1_phone" required>
                    </div>
                    <div class="form-group">
                        <label for="member_1_photo">Photo:</label>
                        <input type="file" class="form-control" id="member_1_photo" name="member_1_photo" accept="image/*" required>
                        <small class="form-text text-muted">Upload a clear photo. Maximum size: 5MB. Accepted formats: JPG, PNG, GIF.</small>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="member_count" id="member_count" value="1">
            
            <div class="mb-3">
                <button type="button" class="btn btn-secondary" id="add-member">Add Another Team Member</button>
            </div>
            
            <button type="submit" class="btn btn-primary">Register Team</button>
        </form>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Toggle between individual and team forms
        document.addEventListener('DOMContentLoaded', function() {
            const individualRadio = document.getElementById('individual');
            const teamRadio = document.getElementById('team');
            const individualForm = document.getElementById('individual-form');
            const teamForm = document.getElementById('team-form');
            
            function updateFormDisplay() {
                if (individualRadio.checked) {
                    individualForm.classList.remove('hidden');
                    teamForm.classList.add('hidden');
                } else {
                    individualForm.classList.add('hidden');
                    teamForm.classList.remove('hidden');
                }
            }
            
            individualRadio.addEventListener('change', updateFormDisplay);
            teamRadio.addEventListener('change', updateFormDisplay);
            
            // Add team member functionality
            const addMemberBtn = document.getElementById('add-member');
            const teamMembersContainer = document.querySelector('.team-members-container');
            const memberCountInput = document.getElementById('member_count');
            
            let memberCount = 1;
            
            addMemberBtn.addEventListener('click', function() {
                memberCount++;
                memberCountInput.value = memberCount;
                
                const newMember = document.createElement('div');
                newMember.classList.add('team-member');
                newMember.id = `member-${memberCount}`;
                
                newMember.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4>Team Member #${memberCount}</h4>
                        <button type="button" class="btn btn-sm btn-danger remove-member" data-member="${memberCount}">Remove</button>
                    </div>
                    <div class="form-group">
                        <label for="member_${memberCount}_name">Full Name:</label>
                        <input type="text" class="form-control" id="member_${memberCount}_name" name="member_${memberCount}_name" required>
                    </div>
                    <div class="form-group">
                        <label for="member_${memberCount}_email">Email:</label>
                        <input type="email" class="form-control" id="member_${memberCount}_email" name="member_${memberCount}_email" required>
                    </div>
                    <div class="form-group">
                        <label for="member_${memberCount}_phone">Phone Number:</label>
                        <input type="tel" class="form-control" id="member_${memberCount}_phone" name="member_${memberCount}_phone" required>
                    </div>
                    <div class="form-group">
                        <label for="member_${memberCount}_photo">Photo:</label>
                        <input type="file" class="form-control" id="member_${memberCount}_photo" name="member_${memberCount}_photo" accept="image/*" required>
                        <small class="form-text text-muted">Upload a clear photo. Maximum size: 5MB. Accepted formats: JPG, PNG, GIF.</small>
                    </div>
                `;
                
                teamMembersContainer.appendChild(newMember);
                
                // Add event listener for the remove button
                const removeBtn = newMember.querySelector('.remove-member');
                removeBtn.addEventListener('click', function() {
                    const memberToRemove = document.getElementById(`member-${this.dataset.member}`);
                    teamMembersContainer.removeChild(memberToRemove);
                    
                    // Re-number remaining members
                    let newCount = 0;
                    const members = teamMembersContainer.querySelectorAll('.team-member');
                    members.forEach((member, index) => {
                        newCount = index + 1;
                        const memberNum = newCount;
                        
                        member.id = `member-${memberNum}`;
                        
                        // Update the heading
                        const heading = member.querySelector('h4');
                        heading.textContent = `Team Member #${memberNum}`;
                        
                        // Update fields
                        const fields = ['name', 'email', 'phone', 'photo'];
                        fields.forEach(field => {
                            const input = member.querySelector(`#member_${member.dataset.originalIndex}_${field}`);
                            if (input) {
                                input.id = `member_${memberNum}_${field}`;
                                input.name = `member_${memberNum}_${field}`;
                            }
                        });
                        
                        // Update remove button
                        const removeBtn = member.querySelector('.remove-member');
                        if (removeBtn) {
                            removeBtn.dataset.member = memberNum;
                        }
                        
                        // Store original index
                        member.dataset.originalIndex = memberNum;
                    });
                    
                    memberCount = newCount;
                    memberCountInput.value = memberCount;
                });
                
                // Store original index
                newMember.dataset.originalIndex = memberCount;
            });
        });
    </script>
</body>
</html>