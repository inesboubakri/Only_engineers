

    <script>
        // Définir openEditModal comme une fonction globale
        window.openEditModal = function(hackathonStr) {
            try {
                const hackathon = typeof hackathonStr === 'string' ? JSON.parse(hackathonStr) : hackathonStr;
                
                // Fermer tous les menus dropdown ouverts
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });

                document.getElementById('edit_id').value = hackathon.id;
                document.getElementById('edit_name').value = hackathon.name;
                document.getElementById('edit_description').value = hackathon.description;
                document.getElementById('edit_start_date').value = hackathon.start_date;
                document.getElementById('edit_end_date').value = hackathon.end_date;
                document.getElementById('edit_start_time').value = hackathon.start_time;
                document.getElementById('edit_end_time').value = hackathon.end_time;
                document.getElementById('edit_location').value = hackathon.location;
                document.getElementById('edit_required_skills').value = hackathon.required_skills;
                document.getElementById('edit_organizer').value = hackathon.organizer;
                document.getElementById('edit_max_participants').value = hackathon.max_participants;
                document.getElementById('edit_prize_pool').value = hackathon.prize_pool;

                const editModal = document.getElementById('editHackathonModal');
                editModal.classList.add('visible');
                document.body.style.overflow = 'hidden';
            } catch (error) {
                console.error('Error parsing hackathon data:', error);
                alert('Une erreur est survenue lors de l\'ouverture du formulaire d\'édition');
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('addHackathonModal');
            const addButton = document.querySelector('.add-button');
            const closeBtn = document.querySelector('.close');
            const cancelBtn = document.querySelector('.cancel-btn');
            const form = document.getElementById('addHackathonForm');
            const editModal = document.getElementById('editHackathonModal');
            const deleteModal = document.getElementById('deleteConfirmModal');
            const editForm = document.getElementById('editHackathonForm');
            let currentHackathonId = null;

            // Définir la fonction confirmDelete globalement
            window.confirmDelete = function(id) {
                currentHackathonId = id;
                deleteModal.classList.add('visible');
                document.body.style.overflow = 'hidden';
                // Fermer le menu dropdown
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            };

            function openModal() {
                modal.classList.add('visible');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.remove('visible');
                document.body.style.overflow = '';
                form.reset();
            }

            addButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openModal();
            });

            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Prevent clicks inside modal content from closing the modal
            modal.querySelector('.modal-content').addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (validateForm(this)) {
                    const formData = new FormData(form);
                    try {
                        const response = await fetch('http://localhost/projet_web/back_office/controllers/hackathonsController.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            closeModal();
                            loadHackathons();
                            alert('Hackathon added successfully!');
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error adding hackathon');
                    }
                }
            });

            // Load initial data
            loadHackathons();

            // Edit and Delete functionality
            function closeModals() {
                editModal.classList.remove('visible');
                deleteModal.classList.remove('visible');
                document.body.style.overflow = '';
                editForm.reset();
                currentHackathonId = null;
            }

            // Event listeners for edit and delete buttons
            document.addEventListener('click', function(e) {
                const target = e.target;

                if (target.matches('.action-btn')) {
                    const row = target.closest('tr');
                    const hackathonId = row.querySelector('td:first-child').textContent;
                    
                    if (target.textContent === 'Edit') {
                        const hackathon = {
                            id: hackathonId,
                            name: row.querySelector('td:nth-child(2)').textContent,
                            description: row.querySelector('td:nth-child(3)').textContent,
                            start_date: row.querySelector('td:nth-child(4)').textContent,
                            end_date: row.querySelector('td:nth-child(5)').textContent,
                            start_time: row.querySelector('td:nth-child(6)').textContent,
                            end_time: row.querySelector('td:nth-child(7)').textContent,
                            location: row.querySelector('td:nth-child(8)').textContent,
                            required_skills: row.querySelector('td:nth-child(9)').textContent,
                            organizer: row.querySelector('td:nth-child(10)').textContent,
                            max_participants: row.querySelector('td:nth-child(11)').textContent,
                            prize_pool: row.querySelector('td:nth-child(12)').textContent,
                            image: row.querySelector('td:nth-child(13) img').src
                        };
                        openEditModal(hackathon);
                    } else if (target.textContent === 'Delete') {
                        confirmDelete(hackathonId);
                    }
                }
            });

            // Handle edit form submission
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (validateForm(this, true)) {
                    const formData = new FormData(editForm);
                    const id = formData.get('id');
                    
                    try {
                        const response = await fetch(`http://localhost/projet_web/back_office/controllers/hackathonsController.php?id=${id}`, {
                            method: 'POST', // Changed to POST to handle file upload
                            body: formData // Send FormData directly
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            closeModals();
                            loadHackathons();
                            alert('Hackathon updated successfully!');
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error updating hackathon');
                    }
                }
            });

            // Handle delete confirmation
            deleteModal.querySelector('.confirm-btn').addEventListener('click', async function() {
                if (currentHackathonId) {
                    try {
                        const response = await fetch(`http://localhost/projet_web/back_office/controllers/hackathonsController.php?id=${currentHackathonId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            closeModals();
                            loadHackathons();
                            alert('Hackathon supprimé avec succès!');
                        } else {
                            alert('Erreur: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la suppression du hackathon');
                    }
                }
            });

            // Close modals on cancel/close buttons
            editModal.querySelector('.close').addEventListener('click', closeModals);
            editModal.querySelector('.cancel-btn').addEventListener('click', closeModals);
            deleteModal.querySelector('.cancel-btn').addEventListener('click', closeModals);

            // Close modals when clicking outside
            [editModal, deleteModal].forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModals();
                    }
                });
            });

            // Fonctions de validation
            function validateForm(form, isEdit = false) {
                const prefix = isEdit ? 'edit_' : '';
                const errors = {};
                
                // Validation du nom (3-40 caractères)
                const name = form[prefix + 'name'].value;
                if (name.length < 3 || name.length > 40) {
                    errors.name = 'Le nom doit contenir entre 3 et 40 caractères';
                }

                // Validation de l'organisateur (3-20 caractères)
                const organizer = form[prefix + 'organizer'].value;
                if (organizer.length < 3 || organizer.length > 20) {
                    errors.organizer = 'L\'organisateur doit contenir entre 3 et 20 caractères';
                }

                // Validation de la description (10-500 mots)
                const description = form[prefix + 'description'].value;
                const wordCount = description.trim().split(/\s+/).length;
                if (wordCount < 10 || wordCount > 500) {
                    errors.description = 'La description doit contenir entre 10 et 500 mots';
                }

                // Validation des dates
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const startDate = new Date(form[prefix + 'start_date'].value);
                const endDate = new Date(form[prefix + 'end_date'].value);

                if (startDate < today) {
                    errors.startDate = 'La date de début ne peut pas être antérieure à aujourd\'hui';
                }
                if (endDate < today) {
                    errors.endDate = 'La date de fin ne peut pas être antérieure à aujourd\'hui';
                }
                if (endDate < startDate) {
                    errors.endDate = 'La date de fin doit être postérieure à la date de début';
                }

                // Validation de la localisation (minimum 3 mots)
                const location = form[prefix + 'location'].value;
                const locationWords = location.trim().split(/\s+/).length;
                if (locationWords < 3) {
                    errors.location = 'La localisation doit contenir au moins 3 mots';
                }

                // Validation des compétences requises (séparées par des virgules)
                const skills = form[prefix + 'required_skills'].value;
                if (!skills.includes(',')) {
                    errors.skills = 'Les compétences doivent être séparées par des virgules';
                }

                // Prize Pool doit être positif
                const prizePool = parseInt(form[prefix + 'prize_pool'].value);
                if (isNaN(prizePool) || prizePool <= 0) {
                    errors.prizePool = 'Le Prize Pool doit être supérieur à 0';
                }

                // Afficher les erreurs dans les spans correspondants
                Object.keys(errors).forEach(key => {
                    const errorSpan = document.getElementById((isEdit ? 'edit-' : '') + key.toLowerCase() + '-error');
                    if (errorSpan) {
                        errorSpan.textContent = errors[key];
                    }
                });

                return Object.keys(errors).length === 0;
            }

            // Nettoyer les messages d'erreur lors de la saisie
            function setupInputValidation(form, isEdit = false) {
                const inputs = form.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.addEventListener('input', function() {
                        const errorSpan = this.parentElement.querySelector('.error-message');
                        if (errorSpan) {
                            errorSpan.textContent = '';
                        }
                    });
                });
            }

            setupInputValidation(form);
            setupInputValidation(editForm, true);
        });

        // Load hackathons data
        function loadHackathons() {
            fetch('http://localhost/projet_web/back_office/controllers/hackathonsController.php')
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('hackathonsTableBody');
                    tableBody.innerHTML = '';
                    
                    if (Array.isArray(data)) {
                        data.forEach(hackathon => {
                            const row = document.createElement('tr');
                            const encodedHackathon = encodeURIComponent(JSON.stringify(hackathon));
                            row.innerHTML = `
                                <td>${hackathon.id}</td>
                                <td>${hackathon.name || ''}</td>
                                <td>${hackathon.description || ''}</td>
                                <td>${hackathon.start_date || ''}</td>
                                <td>${hackathon.end_date || ''}</td>
                                <td>${hackathon.start_time || ''}</td>
                                <td>${hackathon.end_time || ''}</td>
                                <td>${hackathon.location || ''}</td>
                                <td>${hackathon.required_skills || ''}</td>
                                <td>${hackathon.organizer || ''}</td>
                                <td>${hackathon.max_participants || ''}</td>
                                <td>$${hackathon.prize_pool ? hackathon.prize_pool.toLocaleString() : '0'}</td>
                                <td><img src="${hackathon.image ? '../uploads/hackathon_images/' + hackathon.image : '../uploads/hackathon_images/default.png'}" 
                                        alt="Hackathon Image" 
                                        style="width: 50px; height: 50px; object-fit: cover;" 
                                        onerror="this.src='../uploads/hackathon_images/default.png'"></td>
                                <td class="relative">
                                    <div class="actions-wrapper">
                                        <button class="action-btn" onclick="toggleDropdown(${hackathon.id})">:</button>
                                        <div class="dropdown-content" id="dropdown-${hackathon.id}">
                                            <button onclick="openEditModal(decodeURIComponent('${encodedHackathon}'))" class="dropdown-item">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button onclick="confirmDelete(${hackathon.id})" class="dropdown-item delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des hackathons:', error);
                    const tableBody = document.getElementById('hackathonsTableBody');
                    tableBody.innerHTML = '<tr><td colspan="14" style="text-align: center; color: red;">Erreur lors du chargement des hackathons</td></tr>';
                });
        }

        // Ajouter la prévisualisation de l'image
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const preview = document.createElement('img');
                preview.className = 'image-preview';
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                const existingPreview = this.parentNode.querySelector('.image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                this.parentNode.appendChild(preview);
                reader.readAsDataURL(file);
            }
        });

        // Gérer l'affichage du menu dropdown
        function toggleDropdown(id) {
            // Fermer tous les autres dropdowns
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                if (dropdown.id !== `dropdown-${id}`) {
                    dropdown.classList.remove('show');
                }
            });

            // Toggle le dropdown actuel
            const dropdown = document.getElementById(`dropdown-${id}`);
            dropdown.classList.toggle('show');
        }

        // Fermer les dropdowns quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('action-btn')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });

        // Empêcher la propagation du clic dans le dropdown
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>