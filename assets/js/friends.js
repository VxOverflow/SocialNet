/**
 * assets/js/friends.js
 * Logique de vues/clients/amis.html
 */

function renderPersonRow(person) {
  const photo = person.photo ? '../../' + person.photo : 'https://api.dicebear.com/9.x/avataaars/svg?seed=' + encodeURIComponent(person.prenom);
  let actionHtml = '';
  switch (person.statut_relation) {
    case 'accepted':
      actionHtml = `<span class="mini-btn pending">Ami</span>`;
      break;
    case 'pending_sent':
      actionHtml = `<span class="mini-btn pending">Envoyée</span>`;
      break;
    case 'pending_received':
      actionHtml = `<span class="mini-btn pending">À répondre</span>`;
      break;
    default:
      actionHtml = `<button class="mini-btn add" onclick="sendFriendRequest(${person.id}, this)">+ Ajouter</button>`;
  }
  return `<div class="person-row">
    <img class="avatar" src="${photo}" alt="">
    <div class="info"><b><a href="profil.html?id=${person.id}">${escapeHtml(person.prenom)} ${escapeHtml(person.nom)}</a></b><span>${escapeHtml(person.bio || 'Étudiant ESGIS')}</span></div>
    ${actionHtml}
  </div>`;
}

async function searchUsers(query = '') {
  const container = document.getElementById('search-results');
  const res = await apiFetch('/friends/search.php?q=' + encodeURIComponent(query));
  if (!res.success || res.users.length === 0) {
    container.innerHTML = '<div class="empty-state">Aucun utilisateur trouvé.</div>';
    return;
  }
  container.innerHTML = res.users.map(renderPersonRow).join('');
}

async function sendFriendRequest(receiverId, btn) {
  const res = await apiFetch('/friends/send.php', { method: 'POST', body: { receiver_id: receiverId } });
  if (res.success) {
    btn.outerHTML = '<span class="mini-btn pending">Envoyée</span>';
  } else {
    alert(res.message);
  }
}

async function loadFriendsAndRequests() {
  const res = await apiFetch('/friends/list.php');
  if (!res.success) return;

  const reqBox = document.getElementById('friend-requests');
  reqBox.innerHTML = res.demandes_recues.length
    ? res.demandes_recues.map(d => {
        const photo = d.photo ? '../../' + d.photo : 'https://api.dicebear.com/9.x/avataaars/svg?seed=' + encodeURIComponent(d.prenom);
        return `<div class="person-row">
          <img class="avatar" src="${photo}" alt="">
          <div class="info"><b>${escapeHtml(d.prenom)} ${escapeHtml(d.nom)}</b><span>Souhaite devenir ami(e)</span></div>
          <div class="row-gap">
            <button class="mini-btn accept" onclick="respondRequest(${d.request_id}, 'accept', this)">✓</button>
            <button class="mini-btn refuse" onclick="respondRequest(${d.request_id}, 'refuse', this)">✕</button>
          </div>
        </div>`;
      }).join('')
    : '<div class="empty-state">Aucune demande en attente.</div>';

  const friendsBox = document.getElementById('friends-list');
  friendsBox.innerHTML = res.amis.length
    ? res.amis.map(a => {
        const photo = a.photo ? '../../' + a.photo : 'https://api.dicebear.com/9.x/avataaars/svg?seed=' + encodeURIComponent(a.prenom);
        return `<div class="person-row">
          <img class="avatar" src="${photo}" alt="">
          <div class="info"><b>${escapeHtml(a.prenom)} ${escapeHtml(a.nom)}</b></div>
          <a class="mini-btn add" href="chat.html?with=${a.id}">Message</a>
        </div>`;
      }).join('')
    : '<div class="empty-state">Pas encore d\'amis.</div>';
}

async function respondRequest(requestId, action, btn) {
  const res = await apiFetch('/friends/respond.php', { method: 'POST', body: { request_id: requestId, action } });
  if (res.success) {
    btn.closest('.person-row').remove();
    loadFriendsAndRequests();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (!document.getElementById('search-results')) return;
  const initialQuery = new URLSearchParams(window.location.search).get('q') || '';
  document.getElementById('friend-search-input').value = initialQuery;
  searchUsers(initialQuery);
  loadFriendsAndRequests();

  document.getElementById('friend-search-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') searchUsers(e.target.value.trim());
  });
  document.getElementById('btn-search').addEventListener('click', () => {
    searchUsers(document.getElementById('friend-search-input').value.trim());
  });
});
