<?php
require_once __DIR__ . '/../config.php';

session_start();
if (empty($_SESSION['is_admin'])) {
    json_out(['error' => 'Non autorise'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['error' => 'Methode non autorisee'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($input['id'] ?? 0);
if ($id <= 0) {
    json_out(['error' => 'ID manquant'], 400);
}

$stmt = db()->prepare("DELETE FROM guests WHERE id = ?");
$stmt->execute([$id]);
if ($stmt->rowCount() === 0) {
    json_out(['error' => 'Invite introuvable'], 404);
}

json_out(['success' => true, 'id' => $id, 'tables' => statut_tables()]);
