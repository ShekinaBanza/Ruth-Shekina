<?php
require_once __DIR__ . '/../config.php';
session_start();
if (empty($_SESSION['is_admin'])) { json_out(['error' => 'Non autorisé'], 401); }

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($input['id'] ?? 0);
if ($id <= 0) json_out(['error' => 'ID manquant'], 400);

$pdo = db();
$stmt = $pdo->prepare("SELECT present FROM guests WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) json_out(['error' => 'Introuvable'], 404);

$new = $row['present'] ? 0 : 1;
$pdo->prepare("UPDATE guests SET present = ? WHERE id = ?")->execute([$new, $id]);

json_out(['success' => true, 'present' => (bool)$new]);
