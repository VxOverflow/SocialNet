<?php
/**
 * api/friends/respond.php
 * Méthode : POST (JSON) { request_id, action }  -- action = 'accept' ou 'refuse'
 * Seul le destinataire (receiver_id) de la demande peut répondre.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$user = require_auth();
$data = get_json_body();

$requestId = (int) ($data['request_id'] ?? 0);
$action = $data['action'] ?? '';

if (!$requestId || !in_array($action, ['accept', 'refuse'], true)) {
    json_response(['success' => false, 'message' => 'request_id et action (accept/refuse) requis'], 400);
}

$stmt = $pdo->prepare("SELECT * FROM friends WHERE id = ?");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request) {
    json_response(['success' => false, 'message' => 'Demande introuvable'], 404);
}
if ($request['receiver_id'] != $user['id']) {
    json_response(['success' => false, 'message' => 'Action non autorisée'], 403);
}

$newStatus = $action === 'accept' ? 'accepted' : 'refused';
$stmt = $pdo->prepare("UPDATE friends SET status = ? WHERE id = ?");
$stmt->execute([$newStatus, $requestId]);

json_response(['success' => true, 'message' => $action === 'accept' ? 'Invitation accepté' : 'Invitation refusée']);
