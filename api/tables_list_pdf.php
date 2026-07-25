<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/fpdf.php';

class TablesListPDF extends FPDF
{
    const BG = [218, 223, 208];
    const TEXT = [84, 71, 49];
    const ACCENT = [143, 119, 72];
    const LINE = [163, 146, 107];

    private $assetsDir;

    public function __construct($assetsDir)
    {
        parent::__construct('L', 'mm', 'A4');
        $this->assetsDir = $assetsDir;
        $this->SetAutoPageBreak(false);
        $this->SetMargins(0, 0, 0);
    }

    public function tx($s)
    {
        $converted = iconv('UTF-8', 'CP1252//TRANSLIT', (string)$s);
        return $converted !== false ? $converted : (string)$s;
    }

    public function build()
    {
        foreach (tables_actives() as $numero => $nom) {
            $this->drawTablePage($numero, $nom);
        }
    }

    private function drawTablePage($numero, $nom)
    {
        $this->AddPage();
        $this->SetFillColor(...self::BG);
        $this->Rect(0, 0, 297, 210, 'F');

        $this->SetDrawColor(...self::LINE);
        $this->SetLineWidth(0.6);
        $this->Rect(10, 10, 277, 190);

        $this->SetTextColor(...self::ACCENT);
        $this->SetFont('Times', 'B', 16);
        $this->SetXY(0, 16);
        $this->Cell(297, 8, $this->tx('Mariage Shekina & Ruth'), 0, 0, 'C');

        $imagePath = $this->tableImagePath($numero);
        if ($imagePath) {
            $this->drawImagePanel($imagePath);
            $textX = 126;
            $textW = 150;
        } else {
            $this->drawDecorativeFlowers();
            $textX = 28;
            $textW = 241;
        }

        $this->SetTextColor(...self::TEXT);
        $this->SetFont('Times', 'B', 28);
        $this->SetXY($textX, 58);
        $this->Cell($textW, 12, $this->tx('TABLE ' . $numero), 0, 0, 'C');

        $this->SetFont('Times', 'B', $this->fitFontSize($nom, 44, 28, $textW - 8));
        $this->SetXY($textX, 82);
        $this->MultiCell($textW, 16, $this->tx($this->upper($nom)), 0, 'C');

        $prayerY = max(136, $this->GetY() + 13);
        $this->SetTextColor(...self::ACCENT);
        $this->SetFont('Times', 'BI', 34);
        $this->SetXY($textX, $prayerY);
        $this->MultiCell($textW, 13, $this->tx('PRIÈRE POUR NOUS'), 0, 'C');

        $this->SetDrawColor(...self::LINE);
        $this->SetLineWidth(0.35);
        $lineX1 = $textX + 20;
        $lineX2 = $textX + $textW - 20;
        $this->Line($lineX1, 128, $lineX2, 128);
        $this->Line($lineX1, 171, $lineX2, 171);
    }

    private function drawImagePanel($imagePath)
    {
        $x = 20;
        $y = 30;
        $w = 90;
        $h = 150;

        $this->SetFillColor(244, 245, 240);
        $this->SetDrawColor(...self::LINE);
        $this->SetLineWidth(0.4);
        $this->Rect($x, $y, $w, $h, 'FD');
        $this->drawFittedImage($imagePath, $x + 5, $y + 5, $w - 10, $h - 10);
    }

    private function drawDecorativeFlowers()
    {
        $top = $this->assetsDir . '/flower_top.png';
        $bottom = $this->assetsDir . '/flower_bottom.png';
        if (is_file($top)) {
            $this->Image($top, 15, 22, 65);
        }
        if (is_file($bottom)) {
            $this->Image($bottom, 218, 133, 58);
        }
    }

    private function drawFittedImage($file, $x, $y, $maxW, $maxH)
    {
        $size = @getimagesize($file);
        if (!$size || $size[0] <= 0 || $size[1] <= 0) {
            return;
        }

        $ratio = min($maxW / $size[0], $maxH / $size[1]);
        $w = $size[0] * $ratio;
        $h = $size[1] * $ratio;
        $this->Image($file, $x + (($maxW - $w) / 2), $y + (($maxH - $h) / 2), $w, $h);
    }

    private function tableImagePath($numero)
    {
        $dir = $this->assetsDir . '/table_images';
        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
            $path = $dir . '/table_' . str_pad((string)$numero, 2, '0', STR_PAD_LEFT) . '.' . $ext;
            if (is_file($path)) {
                return $path;
            }
        }
        return null;
    }

    private function fitFontSize($text, $maxSize, $minSize, $maxWidth)
    {
        for ($size = $maxSize; $size >= $minSize; $size--) {
            $this->SetFont('Times', 'B', $size);
            if ($this->GetStringWidth($this->tx($this->upper($text))) <= $maxWidth) {
                return $size;
            }
        }
        return $minSize;
    }

    private function upper($text)
    {
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper((string)$text, 'UTF-8');
        }
        return strtoupper((string)$text);
    }
}

function build_tables_list_pdf()
{
    $pdf = new TablesListPDF(__DIR__ . '/../assets');
    $pdf->build();
    return $pdf;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    $pdf = build_tables_list_pdf();
    $pdf->Output('D', 'Tables - Priere pour nous.pdf');
}
