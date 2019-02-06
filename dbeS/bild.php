<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;

require_once __DIR__ . '/syncinclude.php';

$conf = Shop::getSettings([CONF_BILDER]);
if ($conf['bilder']['bilder_externe_bildschnittstelle'] === 'N') {
    exit();
}
if ($conf['bilder']['bilder_externe_bildschnittstelle'] === 'W' && !auth()) {
    exit();
}

$productID   = Request::verifyGPCDataInt('a'); // Angeforderter Artikel
$imageNumber = Request::verifyGPCDataInt('n'); // Bildnummer
$url         = Request::verifyGPCDataInt('url'); // Soll die URL zum Bild oder das Bild direkt ausgegeben werden
$size        = Request::verifyGPCDataInt('s'); // Bildgröße

if ($productID > 0 && $imageNumber > 0 && $size > 0) {
    $customerGroup = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
    if (!isset($customerGroup->kKundengruppe)) {
        exit();
    }
    $shopURL       = Shop::getURL() . '/';
    $sql           = $productID === $imageNumber
        ? ''
        : ' AND tartikelpict.nNr = ' . $imageNumber;
    $productImages = Shop::Container()->getDB()->query(
        'SELECT tartikelpict.cPfad, tartikelpict.kArtikel, tartikel.cSeo, tartikelpict.nNr
                FROM tartikelpict
                JOIN tartikel
                    ON tartikel.kArtikel = tartikelpict.kArtikel
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . (int)$customerGroup->kKundengruppe . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = ' . $productID . $sql,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($productImages as $productImage) {
        $image = MediaImage::getThumb(
            Image::TYPE_PRODUCT,
            $productImage->kArtikel,
            $productImage,
            gibPfadGroesse($size),
            $productImage->nNr
        );
        if (!file_exists($image)) {
            $req = MediaImage::toRequest($image);
            MediaImage::cacheImage($req);
        }

        if ($url === 1) {
            echo $shopURL . $image . "<br/>\n";
        } else {
            // Format ermitteln
            $cBildformat = gibBildformat(PFAD_ROOT . $image);
            // @ToDo - Bilder ausgeben wenn alle angefragt wurden?
            if ($cBildformat && $productID !== $imageNumber) {
                $im = ladeBild(PFAD_ROOT . $image);
                if ($im) {
                    header('Content-type: image/' . $cBildformat);
                    imagepng($im);
                    imagedestroy($im);
                }
            }
        }
    }
} else {
    exit();
}

/**
 * @param int $size
 * @return int|string
 */
function gibPfadGroesse(int $size)
{
    switch ($size) {
        case 1:
            return Image::SIZE_LG;
        case 2:
            return Image::SIZE_MD;
        case 3:
            return Image::SIZE_SM;
        case 4:
            return Image::SIZE_XS;
        default:
            return 0;
    }
}

/**
 * @param string $path
 * @return bool|string
 */
function gibBildformat(string $path)
{
    $info = getimagesize($path);
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            return 'jpg';
        case IMAGETYPE_PNG:
            if (function_exists('imagecreatefrompng')) {
                return 'png';
            }
            break;

        case IMAGETYPE_GIF:
            if (function_exists('imagecreatefromgif')) {
                return 'gif';
            }
            break;

        case IMAGETYPE_BMP:
            if (function_exists('imagecreatefromwbmp')) {
                return 'bmp';
            }
            break;
        default:
            return false;
    }

    return false;
}

/**
 * @param string $path
 * @return bool|resource
 */
function ladeBild(string $path)
{
    $info = getimagesize($path);
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $im = imagecreatefromjpeg($path);
            if ($im) {
                return $im;
            }
            break;

        case IMAGETYPE_PNG:
            if (function_exists('imagecreatefrompng')) {
                $im = imagecreatefrompng($path);
                if ($im) {
                    return $im;
                }
            }
            break;

        case IMAGETYPE_GIF:
            if (function_exists('imagecreatefromgif')) {
                $im = imagecreatefromgif($path);
                if ($im) {
                    return $im;
                }
            }
            break;

        case IMAGETYPE_BMP:
            if (function_exists('imagecreatefromwbmp')) {
                $im = imagecreatefromwbmp($path);
                if ($im) {
                    return $im;
                }
            }
            break;
    }

    return false;
}
