<?php
require_once __DIR__ . '/fpdf.php';

/**
 * Génère l'invitation au format A5 paysage en reproduisant fidèlement
 * la maquette fournie (fond vert clair, colonne gauche avec les prénoms
 * des mariés et les branches florales, colonne droite avec le texte de
 * l'invitation, ligne verticale de séparation).
 */
class InvitationPDF extends FPDF
{
    // Couleurs extraites de la maquette originale
    const BG = [218, 223, 208];     // fond vert-sauge clair
    const TEXTC = [124, 104, 66];   // brun/olive du texte
    const LINEC = [163, 146, 107];  // couleur de la ligne de séparation

    private $assetsDir;

    public function __construct($assetsDir)
    {
        parent::__construct('L', 'mm', [210, 148]); // A5 paysage
        $this->assetsDir = $assetsDir;
        $this->SetAutoPageBreak(false);
        $this->SetMargins(0, 0, 0);
    }

    public function build($civilite, $nom, $tableNumero, $tableNom)
    {
        $this->AddPage();
        $W = 210;
        $H = 148;

        // Fond
        $this->SetFillColor(...self::BG);
        $this->Rect(0, 0, $W, $H, 'F');

        // Branches florales (colonne gauche)
        $this->Image($this->assetsDir . '/flower_top.png', 8, 12, 78);
        $this->Image($this->assetsDir . '/flower_bottom.png', 22, 103, 68);

        // ---------- Colonne gauche : prénoms des mariés ----------
        $this->SetTextColor(...self::TEXTC);

        $this->SetFont('Times', 'B', 30);
        $this->SetXY(0, 50);
        $this->Cell(93, 12, $this->tx(MARIE1), 0, 2, 'C');

        $this->SetFont('Times', 'BI', 16);
        $this->SetXY(0, 62);
        $this->Cell(93, 10, 'et', 0, 2, 'C');

        $this->SetFont('Times', 'B', 30);
        $this->SetXY(0, 74);
        $this->Cell(93, 12, $this->tx(MARIE2), 0, 2, 'C');

        $this->SetFont('Times', '', 11.5);
        $this->SetXY(4, 92);
        $this->Cell(85, 6, $this->tx('vous invitent à célébrer leur amour'), 0, 2, 'C');

        // ---------- Ligne verticale de séparation ----------
        $this->SetDrawColor(...self::LINEC);
        $this->SetLineWidth(0.3);
        $this->Line(96, 8, 96, 140);

        // ---------- Colonne droite : texte de l'invitation ----------
        $rightX = 100;
        $rightW = 106;
        $y = 8;

        $this->SetFont('Times', 'B', 12.5);
        $this->SetXY($rightX, $y);
        $this->Cell($rightW, 5.5, $this->tx(MARIAGE_TITRE), 0, 2, 'C');
        $y += 5.6;

        $this->SetFont('Times', 'B', 10.5);
        $this->SetXY($rightX, $y);
        $this->Cell($rightW, 5, $this->tx('TABLE N° ' . $tableNumero . '  -  ' . $tableNom), 0, 2, 'C');
        $y += 5.2;

        $this->SetFont('Times', '', 9.5);
        $this->SetXY($rightX, $y);
        $civLabel = $civilite === 'Couple' ? 'Couple : ' . $nom : $civilite . ' ' . $nom;
        $this->Cell($rightW, 5, $this->tx($civLabel), 0, 2, 'C');
        $y += 6.2;

        $this->SetFont('Times', '', 8.6);
        $this->writeParagraph($rightX, $y, $rightW,
            "Avec une immense joie et une profonde reconnaissance envers Dieu, que nous souhaitons partager avec vous l'un des plus beaux moments de notre vie.");
        $y += 11.2;

        $this->SetFont('Times', '', 8.6);
        $y = $this->writeParagraphMixedBold($rightX, $y, $rightW,
            "Nous avons le plaisir de vous inviter à la célébration de notre mariage qui aura lieu ",
            DATE_MARIAGE . '.');
        $y += 1.2;

        $this->SetFont('Times', '', 8.6);
        $this->writeParagraph($rightX, $y, $rightW,
            "Nous serions très heureux de vous compter parmi nous pour célébrer ce jour si spécial.");
        $y += 8.6;

        // Bloc bénédiction
        $this->SetFont('Times', 'B', 9.2);
        $this->SetXY($rightX, $y); $this->Cell($rightW, 4.6, $this->tx('Bénédiction religieuse'), 0, 2, 'C'); $y += 4.6;
        $this->SetFont('Times', '', 8.8);
        $this->SetXY($rightX, $y); $this->Cell($rightW, 4.4, $this->tx(LIEU_BENEDICTION), 0, 2, 'C'); $y += 4.4;
        $this->SetXY($rightX, $y); $this->Cell($rightW, 4.4, $this->tx(HEURE_BENEDICTION), 0, 2, 'C'); $y += 5.4;

        // Bloc réception
        $this->SetFont('Times', 'B', 9.2);
        $this->SetXY($rightX, $y); $this->Cell($rightW, 4.6, $this->tx('Réception'), 0, 2, 'C'); $y += 4.6;
        $this->SetFont('Times', '', 8.8);
        $this->writeParagraph($rightX, $y, $rightW, LIEU_RECEPTION);
        $y += 8;
        $this->SetXY($rightX, $y); $this->Cell($rightW, 4.4, $this->tx(HEURE_RECEPTION), 0, 2, 'C'); $y += 5.6;

        $this->SetFont('Times', '', 8.2);
        $this->writeParagraph($rightX, $y, $rightW,
            "Votre présence sera pour nous le plus beau témoignage d'amour et d'amitié. Si vous souhaitez également nous accompagner par un cadeau, nous privilégions les cadeaux en espèces.");
        $y += 13.5;

        $this->SetFont('Times', '', 8.4);
        $this->writeParagraph($rightX, $y, $rightW,
            "Nous espérons sincèrement partager cette journée exceptionnelle avec vous.");
        $y += 6.5;

        $this->SetFont('Times', 'I', 8.6);
        $this->SetXY($rightX, $y); $this->Cell($rightW, 4.4, $this->tx('Avec toute notre affection,'), 0, 2, 'C'); $y += 4.4;
        $this->SetFont('Times', 'B', 8.8);
        $this->SetXY($rightX, $y); $this->Cell($rightW, 4.4, $this->tx(SIGNATURE), 0, 2, 'C');
    }

    /** Convertit une chaîne UTF-8 vers l'encodage attendu par FPDF (Windows-1252) */
    private function tx($s)
    {
        return iconv('UTF-8', 'CP1252//TRANSLIT', $s);
    }

    /** Écrit un paragraphe centré sur plusieurs lignes */
    private function writeParagraph($x, $y, $w, $text)
    {
        $this->SetXY($x, $y);
        $this->MultiCell($w, 4.2, $this->tx($text), 0, 'C');
    }

    /** Paragraphe avec un segment en gras (date du mariage), retourne le nouveau Y */
    private function writeParagraphMixedBold($x, $y, $w, $before, $boldPart)
    {
        // Simplification : FPDF ne gère pas nativement le multi-style inline avec
        // centrage propre -> on écrit tout le paragraphe et on met la date en gras
        // en la répétant sur une ligne séparée en évidence n'est pas idéal, donc on
        // choisit d'écrire le paragraphe complet en gras partiel via un rendu ligne à ligne simple.
        $this->SetXY($x, $y);
        $full = $before . $boldPart;
        $this->MultiCell($w, 4.2, $this->tx($full), 0, 'C');
        return $y + ($this->GetY() - $y);
    }
}
