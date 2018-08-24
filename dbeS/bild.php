<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$Einstellungen = Shop::getSettings([CONF_BILDER]);
if ($Einstellungen['bilder']['bilder_externe_bildschnittstelle'] === 'N') {
    exit();
}
if ($Einstellungen['bilder']['bilder_externe_bildschnittstelle'] === 'W' && !auth()) {
    exit();
}

$kArtikel    = RequestHelper::verifyGPCDataInt('a'); // Angeforderter Artikel
$nBildNummer = RequestHelper::verifyGPCDataInt('n'); // Bildnummer
$nURL        = RequestHelper::verifyGPCDataInt('url'); // Soll die URL zum Bild oder das Bild direkt ausgegeben werden
$nSize       = RequestHelper::verifyGPCDataInt('s'); // Bildgröße

if ($kArtikel > 0 && $nBildNummer > 0 && $nSize > 0) {
    $oKundengruppe = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
    if (!isset($oKundengruppe->kKundengruppe)) {
        exit();
    }
    $shopURL          = Shop::getURL() . '/';
    $qry_bildNr       = $kArtikel === $nBildNummer
        ? ''
        : " AND tartikelpict.nNr = " . $nBildNummer;
    $oArtikelPict_arr = Shop::Container()->getDB()->query(
        'SELECT tartikelpict.cPfad, tartikelpict.kArtikel, tartikel.cSeo, tartikelpict.nNr
                FROM tartikelpict
                JOIN tartikel
                    ON tartikel.kArtikel = tartikelpict.kArtikel
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . (int)$oKundengruppe->kKundengruppe . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = ' . $kArtikel . $qry_bildNr,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oArtikelPict_arr as $oArtikelPict) {
        $image = MediaImage::getThumb(
            Image::TYPE_PRODUCT,
            $oArtikelPict->kArtikel,
            $oArtikelPict,
            gibPfadGroesse($nSize),
            $oArtikelPict->nNr
        );
        if (!file_exists($image)) {
            $req = MediaImage::toRequest($image);
            MediaImage::cacheImage($req);
        }

        if ($nURL === 1) {
            echo $shopURL . $image . "<br/>\n";
        } else {
            // Format ermitteln
            $cBildformat = gibBildformat(PFAD_ROOT . $image);
            // @ToDo - Bilder ausgeben wenn alle angefragt wurden?
            if ($cBildformat && $kArtikel !== $nBildNummer) {
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
 * @param int $nSize
 * @return int|string
 */
function gibPfadGroesse(int $nSize)
{
    switch ($nSize) {
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
 * @param string $cBildPfad
 * @return bool|string
 */
function gibBildformat(string $cBildPfad)
{
    $nSize_arr = getimagesize($cBildPfad);
    $nTyp      = $nSize_arr[2];
    switch ($nTyp) {
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
 * @param string $cBildPfad
 * @return bool|resource
 */
function ladeBild(string $cBildPfad)
{
    $nSize_arr = getimagesize($cBildPfad);
    $nTyp      = $nSize_arr[2];
    switch ($nTyp) {
        case IMAGETYPE_JPEG:
            $im = imagecreatefromjpeg($cBildPfad);
            if ($im) {
                return $im;
            }
            break;

        case IMAGETYPE_PNG:
            if (function_exists('imagecreatefrompng')) {
                $im = imagecreatefrompng($cBildPfad);
                if ($im) {
                    return $im;
                }
            }
            break;

        case IMAGETYPE_GIF:
            if (function_exists('imagecreatefromgif')) {
                $im = imagecreatefromgif($cBildPfad);
                if ($im) {
                    return $im;
                }
            }
            break;

        case IMAGETYPE_BMP:
            if (function_exists('imagecreatefromwbmp')) {
                $im = imagecreatefromwbmp($cBildPfad);
                if ($im) {
                    return $im;
                }
            }
            break;

    }

    return false;
}
