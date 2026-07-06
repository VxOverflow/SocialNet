<?php
/**
 * api/users/change-password.php
 * Méthode : POST (JSON) { ancien_mot_de_passe, nouveau_mot_de_passe, confirmation }
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$user = require_auth();
$data = get_json_body();

$ancien = $data['ancien_mot_de_passe'] ?? '';
$nouveau = $data['nouveau_mot_de_passe'] ?? '';
$confirmation = $data['confirmation'] ?? '';

if (!$ancien || !$nouveau || !$confirmation) {
    json_response(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
}
if ($nouveau !== $confirmation) {
    json_response(['success' => false, 'message' => 'Les mots de passe ne correspondent pas'], 400);
}
if (strlen($nouveau) < 6) {
    json_response(['success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 6 caractères'], 400);
}

$stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$row = $stmt->fetch();

if (!password_verify($ancien, $row['mot_de_passe'])) {
    json_response(['success' => false, 'message' => 'Ancien mot de passe incorrect'], 401);
}

$hash = password_hash($nouveau, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
$stmt->execute([$hash, $user['id']]);

json_response(['success' => true, 'message' => 'Mot de passe modifié avec succès']);
