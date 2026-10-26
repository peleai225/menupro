<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Rendu des QR codes de campagne (bâches, affiches) en haute définition.
 *
 * SVG : simple-qrcode (vectoriel, impression à n'importe quelle taille).
 * PNG : la matrice est calculée par BaconQrCode puis dessinée avec GD — simple-qrcode
 * ne sait produire du PNG qu'avec l'extension imagick, absente du serveur.
 */
class QrCodeImageService
{
    /** Marge blanche autour du code, en modules (la norme recommande 4). */
    public const MARGIN_MODULES = 4;

    public function svg(string $content, int $size = 1200): string
    {
        return (string) QrCode::format('svg')
            ->size($size)
            ->margin(self::MARGIN_MODULES)
            ->errorCorrection('H')
            ->encoding('UTF-8')
            ->generate($content);
    }

    /**
     * PNG noir sur blanc d'au moins $minSize pixels de côté, marge blanche incluse.
     */
    public function png(string $content, int $minSize = 2400): string
    {
        $matrix = Encoder::encode($content, ErrorCorrectionLevel::H(), 'UTF-8')->getMatrix();
        $modules = $matrix->getWidth();
        $total = $modules + 2 * self::MARGIN_MODULES;
        $scale = (int) ceil($minSize / $total);
        $size = $total * $scale;

        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $size - 1, $size - 1, $white);

        $offset = self::MARGIN_MODULES * $scale;
        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $px = $offset + $x * $scale;
                    $py = $offset + $y * $scale;
                    imagefilledrectangle($image, $px, $py, $px + $scale - 1, $py + $scale - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($image, null, 9);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return $png;
    }
}
