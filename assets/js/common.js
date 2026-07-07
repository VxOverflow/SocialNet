

// Chemin de l'API relatif à la page courante (vues/clients/*.html -> ../../api)
const API_BASE = '../../api';

/** Récupère l'utilisateur courant stocké dans sessionStorage (ou null) */
function getCurrentUser() {
  const raw = sessionStorage.getItem('user');
  return raw ? JSON.parse(raw) : null;
}

/** Enregistre l'utilisateur + token dans sessionStorage après connexion */
function setCurrentUser(user, token) {
  sessionStorage.setItem('user', JSON.stringify(user));
  sessionStorage.setItem('token', token);
}

function clearCurrentUser() {
  sessionStorage.removeItem('user');
  sessionStorage.removeItem('token');
}

/**
 * Wrapper fetch : ajoute automatiquement le header Authorization,
 * et gère le cas JSON vs FormData (pour les uploads d'image).
 */
async function apiFetch(path, options = {}) {
  const token = sessionStorage.getItem('token');
  const headers = options.headers || {};

  if (!(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
    if (options.body && typeof options.body !== 'string') {
      options.body = JSON.stringify(options.body);
    }
  }
  if (token) {
    headers['Authorization'] = 'Bearer ' + token;
  }

  const url = path.startsWith('http') ? path : API_BASE + path;
  const response = await fetch(url, { ...options, headers });
  let data;
  try {
    
    data = await response.json();
  } catch (e) {
    data = { success: false, message: 'Réponse invalide du serveur' };
  }

  if (response.status === 401) {
    // Session expirée -> retour à la page de connexion
    clearCurrentUser();
    window.location.href = (window.location.pathname.includes('back-office'))
      ? 'login-admin.html'
      : 'connexion.html';
  }
  return data;
}

/** À placer en haut de chaque page client protégée
verifie si le token est valide pour eviter l'acces aux pages sans athentification*/
function requireAuth() {
  const user = getCurrentUser();
  if (!user) {
    window.location.href = 'connexion.html';
    return null;
  }
  return user;
}

/** À placer en haut de chaque page back-office protégée
meme fonction que la precedente*/
function requireAdminAuth() {
  const user = getCurrentUser();
  if (!user || !['admin', 'moderateur'].includes(user.role)) {
    window.location.href = 'login-admin.html';
    return null;
  }
  return user;
}

/** Injecte la navbar cliente dans <div id="navbar-mount"> et marque la page active */
function mountNavbar(activePage) {
  const mount = document.getElementById('navbar-mount');
  if (!mount) return;
  const user = getCurrentUser();
  const photo = user?.photo ? '../../' + user.photo : 'https://api.dicebear.com/9.x/avataaars/svg?seed=' + encodeURIComponent(user?.prenom || 'User');

  mount.innerHTML = `
    <header class="navbar">
      <div class="brand"><span class="dot"></span><span class="word">Social<b>Net</b></span></div>
      <div class="navsearch">🔍<input id="nav-search-input" placeholder="Rechercher un ami..."></div>
      <div class="navlinks">
        <a href="accueil.html" class="${activePage === 'accueil' ? 'active' : ''}">Accueil</a>
        <a href="amis.html" class="${activePage === 'amis' ? 'active' : ''}">Amis</a>
        <a href="chat.html" class="${activePage === 'chat' ? 'active' : ''}">Messages</a>
        <a href="profil.html" class="${activePage === 'profil' ? 'active' : ''}">Profil</a>
        <div class="nav-avatar">
          <img class="avatar" src="${photo}" alt="">
          <button id="btn-logout" title="Déconnexion">⎋</button>
        </div>
      </div>
    </header>`;
//${activePage === 'profil' ? 'active' : ''} contidition qui marque la page active 
  // fonction de deconnexion 
  //elle supprime la variable user cree lors de la connexion et redirige le user vers la page de connxion 
  document.getElementById('btn-logout').addEventListener('click', async () => {
    await apiFetch('/auth/logout.php', { method: 'POST' });
    clearCurrentUser();
    window.location.href = 'connexion.html';
  });
// recherche d'un amis
  // lors de la l'appui sur la touche entree, on nettoie ce que le user a ecrit pour eviter les injection sql vu 
  // qu'on s'apprete a lancer une requete en base avec cette info
  const searchInput = document.getElementById('nav-search-input');
  if (searchInput) {
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && searchInput.value.trim()) {
        window.location.href = 'amis.html?q=' + encodeURIComponent(searchInput.value.trim());
      }
    });
  }
}

/** Formate une date SQL en "il y a Xh" / "hier" / date courte */
function formatDate(sqlDate) {
  const date = new Date(sqlDate.replace(' ', 'T'));
  const diffMs = Date.now() - date.getTime();
  const diffH = diffMs / 3600000;
  if (diffH < 1) return 'à l\'instant';
  if (diffH < 24) return 'il y a ' + Math.floor(diffH) + 'h';
  if (diffH < 48) return 'hier';
  return date.toLocaleDateString('fr-FR');
}

/** Échappe le HTML d'un texte généré dynamiquement (sécurité d'affichage) */
function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}
