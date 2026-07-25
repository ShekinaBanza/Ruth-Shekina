<?php
require_once __DIR__ . '/../config.php';
session_start();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$pwd = $input['password'] ?? '';

if (hash_equals(ADMIN_PASSWORD, (string)$pwd)) {
    $_SESSION['is_admin'] = true;
    json_out(['success' => true]);
} else {
    json_out(['error' => 'Mot de passe incorrect'], 401);
}
