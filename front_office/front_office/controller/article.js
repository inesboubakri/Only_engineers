const BASE_URL = 'http://localhost/fpjw/projet_web/front_office/front_office/';
const NEWS_ENDPOINT = `${BASE_URL}controller/News.php`;
const COMMENT_ENDPOINT = `${BASE_URL}controller/comment.php`;
const PDF_ENDPOINT = 'http://localhost/fpjw/generate_pdf.php';
const DETAIL_PAGE_URL = `${BASE_URL}view/detailpage.html`;
const DEFAULT_IMAGE = `${BASE_URL}news_images/default_image.png`;

function loadComments(idnews, commentsContainer) {
    commentsContainer.innerHTML = '<p>Chargement des commentaires…</p>';
    fetch(`${COMMENT_ENDPOINT}?action=list&idnews=${idnews}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && Array.isArray(data.data)) {
                if (data.data.length === 0) {
                    commentsContainer.innerHTML = '<p>Aucun commentaire pour le moment.</p>';
                } else {
                    commentsContainer.innerHTML = data.data.map(c => `
                        <div class="comment" data-comment-id="${c.id}">
                            <p class="comment-content">${DOMPurify.sanitize(c.content)}</p>
                            <p class="comment-date">${new Date(c.created_date).toLocaleString('fr-FR')}</p>
                            <div class="comment-actions">
                                <button class="edit-btn" data-comment-id="${c.id}">Modifier</button>
                                <button class="delete-btn" data-comment-id="${c.id}">Supprimer</button>
                            </div>
                        </div>
                    `).join('');
                }
            } else {
                commentsContainer.innerHTML = `<p>Erreur : ${DOMPurify.sanitize(data.message || 'chargement')}</p>`;
            }
        })
        .catch(err => {
            console.error("Erreur chargement commentaires :", err);
            commentsContainer.innerHTML = '<p>Erreur réseau.</p>';
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('jobCardsContainer');
    const addnewsModal = document.getElementById('addnewsModal');
    const openAddModalBtn = document.getElementById('openAddModalBtn');
    const closeAddModalBtn = document.getElementById('closeAddModalBtn');
    const cancelAddModalBtn = document.getElementById('cancelAddModalBtn');
    const addnewsForm = document.getElementById('addnewsForm');
    const pdfCustomizationModal = document.getElementById('pdfCustomizationModal');
    const closePdfModalBtn = document.getElementById('closePdfModalBtn');
    const cancelPdfModalBtn = document.getElementById('cancelPdfModalBtn');
    const pdfCustomizationForm = document.getElementById('pdfCustomizationForm');
    const resetFiltersBtn = document.querySelector('.reset-filters');
    const sortSelect = document.getElementById('sortSelect');
    const articleViewToggle = document.getElementById('articleViewToggle');
    const chatbotButton = document.getElementById('chatbotButton');
    const chatbotPopup = document.getElementById('chatbot-popup');
    const chatForm = document.querySelector('.chat-form');
    const messageInput = document.querySelector('.message-input');
    const chatBody = document.querySelector('.chat-body');

    function generateArticleCards(articles) {
        container.innerHTML = articles.map(article => {
            const categoryClass = article.category.toLowerCase().replace(' & ', '-');
            const imageUrl = article.image && article.image !== 'null' && article.image.trim() !== '' 
                ? `${BASE_URL}${article.image}` 
                : DEFAULT_IMAGE;
            console.log(`Article: ${article.title}, Image URL: ${imageUrl}`);
            return `
                <div class="job-card">
                    <div class="job-content ${categoryClass}">
                        <div class="card-header">
                            <span class="date">${new Date(article.created_at).toLocaleDateString('fr-FR')}</span>
                            <span class="category-badge">${DOMPurify.sanitize(article.category)}</span>
                        </div>
                        <div class="job-title">
                            <h4>${DOMPurify.sanitize(article.title)}</h4>
                            <img src="${imageUrl}" alt="${DOMPurify.sanitize(article.title)}" class="company-logo"
                                 onerror="this.src='${DEFAULT_IMAGE}'; console.error('Image failed to load for article ${article.idnews}: ${imageUrl}');">
                        </div>
                        <div class="tags">
                            ${article.category.split(',').map(cat => `<span>${DOMPurify.sanitize(cat.trim())}</span>`).join('')}
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="job-details">
                            <div class="salary">${DOMPurify.sanitize(article.author)}</div>
                            <div class="location">${DOMPurify.sanitize(article.Reading_Time) || 'Unknown'}</div>
                        </div>
                        <button class="details" data-id="${article.idnews}" data-title="${DOMPurify.sanitize(article.title)}" data-author="${DOMPurify.sanitize(article.author)}" data-date="${article.created_at}" data-content="${DOMPurify.sanitize(article.content)}" data-image="${imageUrl}" data-category="${DOMPurify.sanitize(article.category)}">Lire l'article</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function loadArticles(filters = {}, sort = 'created_at') {
        const params = new URLSearchParams();
        if (filters.articleType && filters.articleType !== 'All') {
            params.append('articleType', filters.articleType);
        }
        if (filters.readingTime) {
            params.append('readingTime', filters.readingTime);
        }
        if (filters.publicationYear) {
            params.append('publicationYear', filters.publicationYear);
        }
        if (filters.popularity) {
            params.append('popularity', filters.popularity);
        }
        params.append('sort', sort);
        const queryString = params.toString();
        console.log(`Fetching articles with query: ${NEWS_ENDPOINT}?${queryString}`);

        fetch(`${NEWS_ENDPOINT}?${queryString}`)
            .then(res => {
                console.log('Backend response status:', res.status);
                return res.json();
            })
            .then(data => {
                console.log('Backend response:', data);
                if (data.success && Array.isArray(data.data)) {
                    generateArticleCards(data.data);
                } else {
                    container.innerHTML = `<p class="error-message">${DOMPurify.sanitize(data.message || 'Aucun article disponible')}</p>`;
                }
            })
            .catch(err => {
                console.error("Erreur chargement :", err);
                container.innerHTML = '<p class="error-message">Erreur de chargement des articles</p>';
            });
    }

    addnewsForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(addnewsForm);

        fetch(NEWS_ENDPOINT, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Article ajouté avec succès !');
                    addnewsModal.style.display = 'none';
                    addnewsForm.reset();
                    loadArticles();
                } else {
                    alert(`Erreur : ${data.message}`);
                }
            })
            .catch(err => {
                console.error("Erreur soumission formulaire :", err);
                alert('Erreur réseau lors de l\'ajout de l\'article.');
            });
    });

    openAddModalBtn.addEventListener('click', () => {
        addnewsModal.style.display = 'flex';
    });

    closeAddModalBtn.addEventListener('click', () => {
        addnewsModal.style.display = 'none';
        addnewsForm.reset();
    });

    cancelAddModalBtn.addEventListener('click', () => {
        addnewsModal.style.display = 'none';
        addnewsForm.reset();
    });

    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('details')) {
            const articleId = e.target.dataset.id;
            const title = encodeURIComponent(DOMPurify.sanitize(e.target.dataset.title));
            const author = encodeURIComponent(DOMPurify.sanitize(e.target.dataset.author));
            const date = encodeURIComponent(new Date(e.target.dataset.date).toLocaleDateString('fr-FR'));
            const content = encodeURIComponent(DOMPurify.sanitize(e.target.dataset.content));
            const image = encodeURIComponent(DOMPurify.sanitize(e.target.dataset.image));
            const category = encodeURIComponent(DOMPurify.sanitize(e.target.dataset.category));

            const queryParams = `?id=${articleId}&title=${title}&author=${author}&date=${date}&content=${content}&image=${image}&category=${category}`;
            const detailPageUrl = `${DETAIL_PAGE_URL}${queryParams}`;

            window.location.href = detailPageUrl;
        }
    });

    closePdfModalBtn.addEventListener('click', () => {
        pdfCustomizationModal.style.display = 'none';
    });

    cancelPdfModalBtn.addEventListener('click', () => {
        pdfCustomizationModal.style.display = 'none';
    });

    // Filter handling
    const filterSections = document.querySelectorAll('.filter-section');
    filterSections.forEach(section => {
        const header = section.querySelector('.filter-header');
        const options = section.querySelector('.filter-options');
        const expandBtn = header.querySelector('.expand-btn');

        header.addEventListener('click', () => {
            options.classList.toggle('hidden');
            expandBtn.classList.toggle('active');
        });
    });

    // Apply filters and sort
    function applyFiltersAndSort() {
        const filters = {};
        const articleTypes = Array.from(document.querySelectorAll('input[name="articleType"]:checked')).map(input => input.value).filter(val => val !== 'All');
        const readingTimes = Array.from(document.querySelectorAll('input[name="readingTime"]:checked')).map(input => input.value);
        const publicationYears = Array.from(document.querySelectorAll('input[name="publicationYear"]:checked')).map(input => input.value);
        const popularities = Array.from(document.querySelectorAll('input[name="popularity"]:checked')).map(input => input.value);
        const sort = sortSelect.value;

        if (articleTypes.length) filters.articleType = articleTypes.join(',');
        if (readingTimes.length) filters.readingTime = readingTimes.join(',');
        if (publicationYears.length) filters.publicationYear = publicationYears.join(',');
        if (popularities.length) filters.popularity = popularities.join(',');

        console.log('Applying filters:', filters, 'Sort:', sort);
        loadArticles(filters, sort);
    }

    // Event listeners for filter changes
    document.querySelectorAll('input[name="articleType"], input[name="readingTime"], input[name="publicationYear"], input[name="popularity"]').forEach(input => {
        input.addEventListener('change', applyFiltersAndSort);
    });

    sortSelect.addEventListener('change', applyFiltersAndSort);

    resetFiltersBtn.addEventListener('click', () => {
        document.querySelectorAll('input[type="checkbox"]').forEach(input => input.checked = false);
        sortSelect.value = 'created_at';
        console.log('Filters reset');
        applyFiltersAndSort();
    });

    // View toggle (grid/list)
    let isGridView = true;
    articleViewToggle.addEventListener('click', () => {
        isGridView = !isGridView;
        const gridIcon = articleViewToggle.querySelector('.grid-icon');
        const listIcon = articleViewToggle.querySelector('.list-icon');
        if (isGridView) {
            container.classList.remove('vertical-card-list');
            gridIcon.style.display = 'block';
            listIcon.style.display = 'none';
        } else {
            container.classList.add('vertical-card-list');
            gridIcon.style.display = 'none';
            listIcon.style.display = 'block';
        }
    });

    // Chatbot handling
    chatbotButton.addEventListener('click', () => {
        chatbotPopup.classList.toggle('visible');
    });

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (message) {
            const userMessage = document.createElement('div');
            userMessage.className = 'message user-message';
            userMessage.innerHTML = `<div class="message-text">${DOMPurify.sanitize(message)}</div>`;
            chatBody.appendChild(userMessage);
            messageInput.value = '';
            chatBody.scrollTop = chatBody.scrollHeight;

            // Simulate bot response (placeholder)
            setTimeout(() => {
                const botMessage = document.createElement('div');
                botMessage.className = 'message bot-message';
                botMessage.innerHTML = `<div class="message-text">Je ne suis pas encore connecté à une IA, mais je peux afficher votre message : ${DOMPurify.sanitize(message)}</div>`;
                chatBody.appendChild(botMessage);
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 1000);
        }
    });

    // Initial load
    loadArticles();
});