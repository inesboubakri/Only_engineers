document.addEventListener('DOMContentLoaded', function() {
    // Elements du modal
    const addCourseModal = document.getElementById('addCourseModal');
    const addCourseBtn = document.getElementById('addCourseBtn');
    const closeBtn = document.querySelector('#addCourseModal .close-modal');
    const cancelBtn = document.getElementById('cancelAddCourse');
    const addCourseForm = document.getElementById('addCourseForm');

    // Vérification des éléments
    if (!addCourseModal || !addCourseBtn || !closeBtn || !cancelBtn || !addCourseForm) {
        console.error('Un ou plusieurs éléments du modal sont manquants');
        return;
    }

    // Gestion de l'ouverture/fermeture du modal
    addCourseBtn.addEventListener('click', () => {
        addCourseModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    function closeAddCourseModal() {
        addCourseModal.style.display = 'none';
        document.body.style.overflow = '';
        addCourseForm.reset();
    }

    closeBtn.addEventListener('click', closeAddCourseModal);
    cancelBtn.addEventListener('click', closeAddCourseModal);

    window.addEventListener('click', (e) => {
        if (e.target === addCourseModal) closeAddCourseModal();
    });

    addCourseModal.querySelector('.modal-content').addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Soumission du formulaire
    addCourseForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = addCourseForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
    
        try {
            const formData = new FormData(addCourseForm);
            formData.append('action', 'create');
            
            const response = await fetch('../controllers/course_crud.php', {
                method: 'POST',
                body: formData,
                credentials: 'include' // Important for sessions
            });
    
            // Handle 401 Unauthorized
            if (response.status === 401) {
                showNotification('error', 'Please login to create courses');
                window.location.href = '/login.php';
                return;
            }
    
            // Handle other errors
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Request failed');
            }
    
            const result = await response.json();
            
            if (result.status === 'success') {
                showNotification('success', result.message);
                closeAddCourseModal();
                loadCourses();
            } else {
                throw new Error(result.message || 'Unknown error');
            }
        } catch (error) {
            showNotification('error', error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Add Course';
        }
    });

    // Fonction pour afficher les notifications
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${type === 'success' ? '✓' : '✕'}</span>
                <div class="notification-message">${message}</div>
            </div>
            <div class="notification-progress"></div>
        `;

        const container = document.getElementById('toast-container') || document.body;
        container.appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 10);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    // Chargement des cours
    async function loadCourses() {
        try {
            const response = await fetch('../controllers/course_crud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=read'
            });

            if (!response.ok) {
                throw new Error('Erreur de chargement des cours');
            }

            const data = await response.json();

            if (data.status === 'success') {
                const tbody = document.querySelector('.users-table tbody');
                if (!tbody) {
                    console.error('Table body not found');
                    return;
                }

                tbody.innerHTML = data.data.map(course => `
                    <tr>
                        <td>${course.course_id}</td>
                        <td><div class="course-icon">📚</div></td>
                        <td>${course.title}</td>
                        <td>${course.status === 'free' ? 'Gratuit' : 'Payant'}</td>
                        <td>${course.fees == 0 ? 'Gratuit' : `$${parseFloat(course.fees).toFixed(2)}`}</td>
                        <td><a href="${course.course_link || '#'}" target="_blank">Voir</a></td>
                        <td><a href="${course.certification_link || '#'}" target="_blank">Voir</a></td>
                        <td><span class="status-badge ${course.status === 'free' ? 'free-course' : 'paid-course'}">
                            ${course.status === 'free' ? 'Gratuit' : 'Payant'}
                        </span></td>
                        <td>
                            <div class="actions-wrapper">
                                <button class="action-btn" onclick="showActions(this)">⋮</button>
                                <div class="dropdown-content">
                                    <button class="dropdown-item edit" onclick="editCourse('${course.course_id}')">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <button class="dropdown-item delete" onclick="confirmDelete('${course.course_id}')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            showNotification('error', 'Erreur de chargement des cours');
        }
    }

    // Initialisation
    loadCourses();
});

// Fonctions globales
function showActions(button) {
    const dropdown = button.nextElementSibling;
    document.querySelectorAll('.dropdown-content').forEach(d => {
        if (d !== dropdown) d.classList.remove('show');
    });
    dropdown.classList.toggle('show');
}

function editCourse(courseId) {
    console.log('Éditer le cours:', courseId);
    // Implémentez la logique d'édition ici
}

function confirmDelete(courseId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')) {
        deleteCourse(courseId);
    }
}

async function deleteCourse(courseId) {
    try {
        const response = await fetch('../controllers/course_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&courseId=${encodeURIComponent(courseId)}`
        });

        const result = await response.json();
        
        if (result.status === 'success') {
            document.querySelector(`[data-course-id="${courseId}"]`)?.closest('tr')?.remove();
            showNotification('success', 'Cours supprimé avec succès');
        } else {
            throw new Error(result.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', error.message || 'Erreur lors de la suppression');
    }
}

// Fermer les dropdowns quand on clique ailleurs
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-btn') && !e.target.closest('.dropdown-content')) {
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
    }
});