<?php
/**
 * Configuration générale de l'application "Mariage Shekina & Ruth"
 */

// ---- Mot de passe pour l'espace administrateur ----
// !! Changez ce mot de passe avant de mettre le site en ligne !!
$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    require_once $localConfig;
}
if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'ChangeMe2026!');
}

// ---- Infos du mariage (utilisées sur l'invitation PDF) ----
define('MARIAGE_TITRE', 'INVITATION AU MARIAGE');
define('MARIE1', 'SHEKINA');
define('MARIE2', 'RUTH');
define('DATE_MARIAGE', 'le vendredi 29 août 2026');
define('LIEU_BENEDICTION', 'Paroisse Saint Paul Carrefour');
define('HEURE_BENEDICTION', 'À partir de 14h00');
define('LIEU_RECEPTION', 'Salle : La Contemplation (Route kasapa ref arrêt changalele)');
define('HEURE_RECEPTION', 'Heure : 18h30');
define('SIGNATURE', 'Shekina BANZA & Ruth KASONGO');

// ---- Capacité des tables ----
define('PLACES_PAR_TABLE', 10);
define('NB_TABLES', 25); // 25 tables x 10 places = 250 invités

// Liste complète des 50 noms (Vierge Marie, Anges, Apôtres, grands protecteurs et
// grandes saintes). Seuls les NB_TABLES premiers noms sont utilisés comme noms de
// table (vous pouvez augmenter NB_TABLES jusqu'à 50 si vous ajoutez des tables).
function saints_list() {
    return [
        "Sainte Marie, Mère de Dieu",
        "Saint Michel Archange",
        "Saint Gabriel Archange",
        "Saint Raphaël Archange",
        "Le Saint Ange Gardien",
        "Saint Pierre",
        "Saint Paul",
        "Saint Jean",
        "Saint Jacques le Majeur",
        "Saint André",
        "Saint Jude Thaddée",
        "Saint Thomas",
        "Saint Jacques le Mineur",
        "Saint Philippe",
        "Saint Barthélemy",
        "Saint Matthieu",
        "Saint Simon le Zélote",
        "Saint Matthias",
        "Saint Joseph",
        "Saint Benoît de Nursie",
        "Saint Padre Pio",
        "Saint Antoine le Grand",
        "Saint Jean-Baptiste",
        "Saint Charbel Makhlouf",
        "Saint Jean Bosco",
        "Saint Vincent de Paul",
        "Saint Curé d'Ars (Jean-Marie Vianney)",
        "Saint François d'Assise",
        "Saint Ignace de Loyola",
        "Saint Antoine de Padoue",
        "Saint Augustin",
        "Saint Dominique",
        "Saint Martin de Tours",
        "Saint Georges",
        "Saint Nicolas",
        "Sainte Jeanne d'Arc",
        "Sainte Thérèse d'Avila",
        "Sainte Catherine de Sienne",
        "Sainte Rita de Cascia",
        "Sainte Faustine Kowalska",
        "Sainte Mère Teresa",
        "Sainte Thérèse de Lisieux",
        "Sainte Bernadette Soubirous",
        "Sainte Philomène",
        "Sainte Geneviève",
        "Sainte Claire d'Assise",
        "Sainte Monique",
        "Sainte Cécile",
        "Sainte Agathe",
        "Sainte Blandine",
    ];
}

function table_name($numero) {
    $saints = saints_list();
    $idx = $numero - 1;
    return $saints[$idx] ?? ("Table " . $numero);
}

function tables_actives() {
    $out = [];
    for ($i = 1; $i <= NB_TABLES; $i++) {
        $out[$i] = table_name($i);
    }
    return $out;
}

// ---- Connexion base de données SQLite ----
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dbFile = __DIR__ . '/data/mariage.db';
        $isNew = !file_exists($dbFile);
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE IF NOT EXISTS guests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            civilite TEXT NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('simple','couple')),
            nom TEXT NOT NULL,
            table_numero INTEGER NOT NULL,
            present INTEGER NOT NULL DEFAULT 0,
            telephone TEXT DEFAULT '',
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");
    }
    return $pdo;
}

// Nombre de places occupées par un invité selon son type
function places_occupees($type) {
    return $type === 'couple' ? 2 : 1;
}

function clean_download_filename($filename) {
    $filename = preg_replace('/[\/\\\\:*?"<>|]/', '', $filename);
    $filename = trim($filename);
    return $filename !== '' ? $filename : 'Invitation de invite.pdf';
}

function invitation_filename($nom) {
    $nom = trim((string)$nom);
    if ($nom === '') {
        $nom = 'invite';
    }
    return clean_download_filename('Invitation de ' . $nom . '.pdf');
}

// Calcule les places restantes pour chaque table
function statut_tables() {
    $pdo = db();
    $occ = [];
    foreach ($pdo->query("SELECT table_numero, type, COUNT(*) as n FROM guests GROUP BY table_numero, type") as $row) {
        $t = (int)$row['table_numero'];
        $occ[$t] = ($occ[$t] ?? 0) + $row['n'] * places_occupees($row['type']);
    }
    $out = [];
    foreach (tables_actives() as $num => $nom) {
        $prises = $occ[$num] ?? 0;
        $out[] = [
            'numero' => $num,
            'nom' => $nom,
            'places_prises' => $prises,
            'places_restantes' => max(0, PLACES_PAR_TABLE - $prises),
            'complet' => $prises >= PLACES_PAR_TABLE,
        ];
    }
    return $out;
}

function json_out($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function slugify($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    return $text ?: 'invite';
}
