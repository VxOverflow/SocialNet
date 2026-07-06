<?php
/**
 * api/messages/send.php
 * Méthode : POST (multipart/form-data) : receiver_id, message (texte), image (fichier optionnel)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$user = require_auth();

$receiverId = (int) ($_POST['receiver_id'] ?? 0);
$message = sanitize($_POST['message'] ?? '');
$imagePath = handle_image_upload('image', 'messages');

if (!$receiverId) {
    json_response(['success' => false, 'message' => 'receiver_id requis'], 400);
}
if (!$message && !$imagePath) {
    json_response(['success' => false, 'message' => 'Le message ne peut pas être vide'], 400);
}

$stmt = $pdo->prepare(
    "INSERT INTO messages (sender_id, receiver_id, message, image, date_message) VALUES (?, ?, ?, ?, NOW())"
);
$stmt->execute([$user['id'], $receiverId, $message, $imagePath]);

json_response([
    'success' => true,
    'message_envoye' => [
        'id' => $pdo->lastInsertId(),
        'sender_id' => $user['id'],
        'receiver_id' => $receiverId,
        'message' => $message,
        'image' => $imagePath,
        'date_message' => date('Y-m-d H:i:s')
    ]
]);
