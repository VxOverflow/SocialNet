<?php
/**
 * api/users/profile.php
 * Méthode : GET, optionnel ?id=  (sinon profil de l'utilisateur connecté)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$currentUser = require_auth();
$targetId = isset($_GET['id']) ? (int) $_GET['id'] : $currentUser['id'];

$stmt = $pdo->prepare("SELECT id, nom, prenom, email, photo, bio, role, date_creation FROM users WHERE id = ?");
$stmt->execute([$targetId]);
$profil = $stmt->fetch();

if (!$profil) {
    json_response(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
}

$stmt = $pdo->prepare("SELECT COUNT(*) AS nb FROM posts WHERE user_id = ?");
$stmt->execute([$targetId]);
$nbPosts = $stmt->fetch()['nb'];

$stmt = $pdo->prepare(
    "SELECT COUNT(*) AS nb FROM friends WHERE (sender_id = ? OR receiver_id = ?) AND status = 'accepted'"
);
$stmt->execute([$targetId, $targetId]);
$nbAmis = $stmt->fetch()['nb'];

$profil['nb_publications'] = (int) $nbPosts;
$profil['nb_amis'] = (int) $nbAmis;
$profil['est_moi'] = $targetId === (int) $currentUser['id'];

json_response(['success' => true, 'profil' => $profil]);
