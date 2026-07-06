/**
 * assets/js/profile.js
 * Logique de vues/clients/profil.html
 */

async function loadProfile() {
  const targetId = new URLSearchParams(window.location.search).get('id');
  const url = targetId ? '/users/profile.php?id=' + targetId : '/users/profile.php';
  const res = await apiFetch(url);
  if (!res.success) return;

  const p = res.profil;
  const photo = p.photo ? '../../' + p.photo : 'https://api.dicebear.com/9.x/avataaars/svg?seed=' + encodeURIComponent(p.prenom);

  document.getElementById('profile-photo').src = photo;
  document.getElementById('profile-name').textContent = `${p.prenom} ${p.nom}`;
  document.getElementById('profile-bio').textContent = p.bio || 'Étudiant(e) ESGIS';
  document.getElementById('profile-nb-posts').textContent = p.nb_publications;
  document.getElementById('profile-nb-amis').textContent = p.nb_amis;
  document.getElementById('profile-edit-btn').classList.toggle('hidden', !p.est_moi);

  loadFeed(p.id);

  if (p.est_moi) {
    document.getElementById('edit-nom').value = p.nom;
    document.getElementById('edit-prenom').value = p.prenom;
    document.getElementById('edit-bio').value = p.bio || '';
  }
}

function toggleEditForm() {
  document.getElementById('edit-form-card').classList.toggle('hidden');
}

async function submitProfileEdit(e) {
  e.preventDefault();
  const formData = new FormData();
  formData.append('nom', document.getElementById('edit-nom').value.trim());
  formData.append('prenom', document.getElementById('edit-prenom').value.trim());
  formData.append('bio', document.getElementById('edit-bio').value.trim());
  const fileInput = document.getElementById('edit-photo');
  if (fileInput.files[0]) formData.append('photo', fileInput.files[0]);

  const res = await apiFetch('/users/update-profile.php', { method: 'POST', body: formData });
  if (res.success) {
    const user = getCurrentUser();
    user.nom = document.getElementById('edit-nom').value.trim();
    user.prenom = document.getElementById('edit-prenom').value.trim();
    if (res.photo) user.photo = res.photo;
    setCurrentUser(user, sessionStorage.getItem('token'));
    location.reload();
  } else {
    alert(res.message);
  }
}

async function submitPasswordChange(e) {
  e.preventDefault();
  const ancien_mot_de_passe = document.getElementById('pwd-ancien').value;
  const nouveau_mot_de_passe = document.getElementById('pwd-nouveau').value;
  const confirmation = document.getElementById('pwd-confirm').value;

  const res = await apiFetch('/users/change-password.php', {
    method: 'POST',
    body: { ancien_mot_de_passe, nouveau_mot_de_passe, confirmation }
  });
  showFormMsg('pwd-msg', res.message, res.success ? 'success' : 'error');
  if (res.success) e.target.reset();
}

document.addEventListener('DOMContentLoaded', () => {
  // Charger le profil si nous sommes sur profil.html
  if (document.getElementById('profile-name')) {
    loadProfile();
    document.getElementById('form-edit-profile')?.addEventListener('submit', submitProfileEdit);
  }
  
  // Attacher le listener de changement de mot de passe (indépendant)
  document.getElementById('form-change-password')?.addEventListener('submit', submitPasswordChange);
});
