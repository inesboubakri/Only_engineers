(() => {
  const API_NEWS    = 'http://localhost/projet_web/back_office/controllers/newsController.php';
  const API_USER    = 'http://localhost/projet_web/back_office/controllers/userController.php';
  const API_COMMENT = 'http://localhost/projet_web/back_office/controllers/commentController.php?action=addCommentaire';

  // Charger et afficher la liste des news
  function loadNews() {
    fetch(API_NEWS)
      .then(res => res.json())
      .then(json => {
        const container = document.getElementById('news-container');
        container.innerHTML = json.data.map(n => `
          <div class="news-card">
            <h3>${n.title}</h3>
            <p>${n.full_name}</p>
            <p>${new Date(n.created_at).toLocaleDateString()}</p>
            <button data-id="${n.idnews}">Lire</button>
          </div>
        `).join('');
        // Attacher événements
        container.querySelectorAll('button').forEach(btn => {
          btn.addEventListener('click', () => openArticleModal(btn.dataset.id));
        });
      });
  }

  // Ouvrir modale article + charger contenu + commentaires
  window.openArticleModal = id => {
    document.getElementById('articleModal').style.display = 'flex';
    document.getElementById('articleId').value = id;

    // Charger article
    fetch(`${API_NEWS}?action=getArticle&articleId=${id}`)
      .then(res => res.json())
      .then(json => {
        const a = json.data;
        document.getElementById('articleTitle').textContent    = a.title;
        document.getElementById('articleAuthor').textContent   = a.full_name;
        document.getElementById('articleDate').textContent     = new Date(a.created_at).toLocaleDateString();
        document.getElementById('articleCategory').textContent = a.category;
        document.getElementById('articleContent').innerHTML    = a.content;
        document.getElementById('articleImage').src            = a.image_url
          || 'http://localhost/projet_web/default_image.png';
      });
    loadComments(id);
  };

  // Fermer modale
  document.getElementById('closeArticleModalBtn')
    .addEventListener('click', () => {
      document.getElementById('articleModal').style.display = 'none';
      document.getElementById('commentsContainer').innerHTML = '';
      document.getElementById('commentForm').reset();
    });

  // Charger commentaires existants
  function loadComments(articleId) {
    fetch(`${API_COMMENT.replace('addCommentaire','getCommentaires')}&idnews=${articleId}`, {
      credentials: 'include'
    })
      .then(res => res.json())
      .then(js => {
        const c = document.getElementById('commentsContainer');
        c.innerHTML = js.data.map(cmt => `
          <div class="comment-item">
            <div class="meta"><strong>${cmt.username}</strong> - ${new Date(cmt.created_date).toLocaleString()}</div>
            <div>${cmt.content}</div>
          </div>
        `).join('');
      });
  }

  // Soumettre nouveau commentaire
  document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const content = document.getElementById('commentContent').value.trim();
    const idnews  = document.getElementById('articleId').value;
    if (!content) return alert('Commentaire vide !');

    // Récupérer user_id
    fetch(API_USER, { credentials: 'include' })
      .then(res => res.json())
      .then(u => {
        if (!u.success) throw new Error(u.message);
        const fd = new FormData();
        fd.append('content', content);
        fd.append('idnews', idnews);
        fd.append('user_id', u.user_id);
        return fetch(API_COMMENT, {
          method: 'POST',
          credentials: 'include',
          body: fd
        });
      })
      .then(res => res.json())
      .then(r => {
        if (!r.success) throw new Error(r.message);
        this.reset();
        loadComments(idnews);
      })
      .catch(err => alert('Erreur : ' + err.message));
  });

  // Initialisation
  document.addEventListener('DOMContentLoaded', loadNews);
})();
