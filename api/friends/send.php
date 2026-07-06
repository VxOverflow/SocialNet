<?php
/**
 * api/friends/send.php
 * Méthode : POST (JSON) { receiver_id }
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$user = require_auth();
$data = get_json_body();
$receiverId = (int) ($data['receiver_id'] ?? 0);

if (!$receiverId || $receiverId == $user['id']) {
    json_response(['success' => false, 'message' => 'Destinataire invalide'], 400);
}

$stmt = $pdo->prepare(
    "SELECT id FROM friends WHERE (sender_id = ? AND receiver_id = ?)"
);
$stmt->execute([$user['id'], $receiverId, $receiverId, $user['id']]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'message' => 'Une relation existe déjà avec cet utilisateur'], 409);
}

$stmt = $pdo->prepare(
    "INSERT INTO friends (sender_id, receiver_id, status, date_creation) VALUES (?, ?, 'pending', NOW())"
);
$stmt->execute([$user['id'], $receiverId]);

json_response(['success' => true, 'message' => 'Invitation envoyée']);
