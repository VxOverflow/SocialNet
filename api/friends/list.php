<?php
/**
 * api/friends/list.php
 * Méthode : GET
 * Retourne { amis: [...], demandes_recues: [...] }
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$user = require_auth();

// Amis acceptés (dans les deux sens)
$stmt = $pdo->prepare(
    "SELECT u.id, u.nom, u.prenom, u.photo
     FROM friends f
     JOIN users u ON u.id = IF(f.sender_id = ?, f.receiver_id, f.sender_id)
     WHERE (f.sender_id = ? OR f.receiver_id = ?) AND f.status = 'accepted'"
);
$stmt->execute([$user['id'], $user['id'], $user['id']]);
$amis = $stmt->fetchAll();

// Demandes reçues en attente
$stmt = $pdo->prepare(
    "SELECT f.id AS request_id, u.id AS user_id, u.nom, u.prenom, u.photo
     FROM friends f
     JOIN users u ON u.id = f.sender_id
     WHERE f.receiver_id = ? AND f.status = 'pending'"
);
$stmt->execute([$user['id']]);
$demandes = $stmt->fetchAll();

json_response(['success' => true, 'amis' => $amis, 'demandes_recues' => $demandes]);
