<?php
/**
 * api/posts/create.php
 * Crée une publication. Envoyée en multipart/form-data (pour l'image).
 * Champs : contenu (texte), image (fichier, optionnel)
 * Header requis : Authorization: Bearer <token>
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Méthode non autorisée'], 405);
}

$user = require_auth();

$contenu = sanitize($_POST['contenu'] ?? '');
if (!$contenu) {
    json_response(['success' => false, 'message' => 'Le contenu de la publication est requis'], 400);
}

$imagePath = handle_image_upload('image', 'posts');

$stmt = $pdo->prepare(
    "INSERT INTO posts (user_id, contenu, image, date_publication) VALUES (?, ?, ?, NOW())"
);
$stmt->execute([$user['id'], $contenu, $imagePath]);

json_response(['success' => true, 'message' => 'Publication créée', 'post_id' => $pdo->lastInsertId()]);
