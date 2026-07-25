<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/InvitationPDF.php';
session_start();
if (empty($_SESSION['is_admin'])) { http_response_code(401); die('Non autorisé'); }

$guests = db()->query("SELECT * FROM guests ORDER BY table_numero ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
if (!$guests) { http_response_code(404); die('Aucun invité enregistré.'); }

$tmpZip = tempnam(sys_get_temp_dir(), 'invitations_') . '.zip';
$zip = new ZipArchive();
$zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

$usedNames = [];
foreach ($guests as $g) {
    $pdf = new InvitationPDF(__DIR__ . '/../assets');
    $pdf->build($g['civilite'], $g['nom'], (int)$g['table_numero'], table_name((int)$g['table_numero']));
    $content = $pdf->Output('S');

    $filename = invitation_filename($g['nom']);
    // Éviter les doublons de noms de fichiers dans le zip
    $base = $filename;
    $n = 1;
    while (in_array($filename, $usedNames, true)) {
        $filename = preg_replace('/\.pdf$/', '', $base) . " ($n).pdf";
        $n++;
    }
    $usedNames[] = $filename;

    $zip->addFromString('Table ' . str_pad($g['table_numero'], 2, '0', STR_PAD_LEFT) . ' - ' . $filename, $content);
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="Invitations - Mariage Shekina & Ruth.zip"');
header('Content-Length: ' . filesize($tmpZip));
readfile($tmpZip);
unlink($tmpZip);
