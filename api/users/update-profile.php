<?php
/**
 * api/users/update-profile.php
 * Méthode : POST (multipart/form-data) : nom, prenom, bio, photo (fichier optionnel)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$user = require_auth();

$nom = sanitize($_POST['nom'] ?? $user['nom']);
$prenom = sanitize($_POST['prenom'] ?? $user['prenom']);
$bio = sanitize($_POST['bio'] ?? '');

$photoPath = handle_image_upload('photo', 'profiles');

if ($photoPath) {
    $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, bio = ?, photo = ? WHERE id = ?");
    $stmt->execute([$nom, $prenom, $bio, $photoPath, $user['id']]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, bio = ? WHERE id = ?");
    $stmt->execute([$nom, $prenom, $bio, $user['id']]);
}

json_response(['success' => true, 'message' => 'Profil mis à jour', 'photo' => $photoPath]);
