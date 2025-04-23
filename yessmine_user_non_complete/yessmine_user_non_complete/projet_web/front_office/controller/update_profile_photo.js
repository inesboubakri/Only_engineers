document.addEventListener('DOMContentLoaded', function() {
    // Trouver l'élément de photo de profil dans la navbar
    const profileBtn = document.querySelector('.profile-btn img');
    if (!profileBtn) return;

    // Charger les données de l'utilisateur
    fetch('get_user_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user.profile_photo) {
                // Mettre à jour la photo de profil
                profileBtn.src = data.user.profile_photo;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
});
