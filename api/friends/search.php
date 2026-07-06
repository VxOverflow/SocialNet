<?php
/**
 * api/friends/search.php
 * Méthode : GET ?q=texte
 * Liste les utilisateurs (hors moi-même) avec leur statut de relation :
 * none | pending_sent | pending_received | accepted
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$user = require_auth();
$q = trim($_GET['q'] ?? '');

$sql = "SELECT id, nom, prenom, photo, bio FROM users WHERE id != :me";
$params = [':me' => $user['id']];

if ($q !== '') {
    $sql .= " AND (nom LIKE :q OR prenom LIKE :q OR email LIKE :q)";
    $params[':q'] = "%$q%";
}
$sql .= " ORDER BY nom ASC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Récupère toutes les relations impliquant l'utilisateur courat
$stmt = $pdo->prepare("SELECT * FROM friends WHERE sender_id = ? OR receiver_id = ?");
$stmt->execute([$user['id'], $user['id']]);
$relations = $stmt->fetchAll();

$relMap = [];
foreach ($relations as $r) {
    $otherId = $r['sender_id'] == $user['id'] ? $r['receiver_id'] : $r['sender_id'];
    if ($r['status'] === 'accepted') {
        $relMap[$otherId] = 'accepted';
    } elseif ($r['status'] === 'pending') {
        $relMap[$otherId] = $r['sender_id'] == $user['id'] ? 'pending_sent' : 'pending_received';
    }
}

foreach ($users as &$u) {
    $u['statut_relation'] = $relMap[$u['id']] ?? 'none';
}

json_response(['success' => true, 'users' => $users]);
