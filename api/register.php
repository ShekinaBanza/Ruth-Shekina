<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['error' => 'Méthode non autorisée'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$civilite = trim($input['civilite'] ?? '');
$type = trim($input['type'] ?? '');
$nom = trim($input['nom'] ?? '');
$telephone = trim($input['telephone'] ?? '');
$tableNumero = (int)($input['table_numero'] ?? 0);

if ($nom === '' || !in_array($type, ['simple', 'couple'], true) || $tableNumero < 1 || $tableNumero > NB_TABLES) {
    json_out(['error' => 'Champs invalides. Vérifiez le nom, le type et la table sélectionnée.'], 400);
}
if (!in_array($civilite, ['M', 'Mme', 'Couple'], true)) {
    $civilite = $type === 'couple' ? 'Couple' : 'M';
}

$pdo = db();

// Vérifier la place disponible sur la table choisie (protège contre les doubles soumissions concurrentes)
$statuts = statut_tables();
$table = null;
foreach ($statuts as $t) {
    if ($t['numero'] === $tableNumero) { $table = $t; break; }
}
if (!$table) {
    json_out(['error' => 'Table introuvable.'], 404);
}
$besoin = places_occupees($type);
if ($table['places_restantes'] < $besoin) {
    json_out(['error' => 'Cette table n\'a plus assez de places disponibles (' . $table['places_restantes'] . ' restante(s)). Merci de choisir une autre table.'], 409);
}

$stmt = $pdo->prepare("INSERT INTO guests (civilite, type, nom, table_numero, telephone) VALUES (?,?,?,?,?)");
$stmt->execute([$civilite, $type, $nom, $tableNumero, $telephone]);
$id = $pdo->lastInsertId();

json_out([
    'success' => true,
    'id' => (int)$id,
    'table_numero' => $tableNumero,
    'table_nom' => table_name($tableNumero),
    'filename' => invitation_filename($nom),
    'invite_url' => 'api/invite.php?id=' . $id,
]);
