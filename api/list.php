<?php
require_once __DIR__ . '/../config.php';
session_start();
if (empty($_SESSION['is_admin'])) { json_out(['error' => 'Non autorisé'], 401); }

$rows = db()->query("SELECT * FROM guests ORDER BY table_numero ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as &$r) {
    $r['table_nom'] = table_name((int)$r['table_numero']);
    $r['places'] = places_occupees($r['type']);
    $r['filename'] = invitation_filename($r['nom']);
}
json_out(['guests' => $rows, 'tables' => statut_tables()]);
