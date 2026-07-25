<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/InvitationPDF.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); die('ID manquant'); }

$stmt = db()->prepare("SELECT * FROM guests WHERE id = ?");
$stmt->execute([$id]);
$g = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$g) { http_response_code(404); die('Invité introuvable'); }

$pdf = new InvitationPDF(__DIR__ . '/../assets');
$pdf->build($g['civilite'], $g['nom'], (int)$g['table_numero'], table_name((int)$g['table_numero']));

$filename = invitation_filename($g['nom']);

$download = isset($_GET['dl']); // sinon on affiche inline dans le navigateur
$pdf->Output($download ? 'D' : 'I', $filename);
