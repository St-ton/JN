<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/syncinclude.php';
// Einstellungen holen
$Einstellungen = Shop::getSettings(array(CONF_BILDER));

if ($Einstellungen['bilder']['bilder_externe_bildschnittstelle'] === 'N') { // Schnittstelle ist deaktiviert
    exit();
} elseif ($Einstellungen['bilder']['bilder_externe_bildschnittstelle'] === 'W') { // Nur Wawi darf zugreifen
    if (!auth()) {
        exit();
    }
}

// Parameter holen
$kArtikel    = verifyGPCDataInteger('a'); // Angeforderter Artikel
$nBildNummer = verifyGPCDataInteger('n'); // Bildnummer
$nURL        = verifyGPCDataInteger('url'); // Soll die URL zum Bild oder das Bild direkt ausgegeben werden
$nSize       = verifyGPCDataInteger('s'); // Bildgröße

if ($kArtikel > 0 && $nBildNummer > 0 && $nSize > 0) {
    // Standardkundengruppe holen
    $oKundengruppe = Shop::DB()->query(
        "SELECT kKundengruppe
            FROM tkundengruppe
            WHERE cStandard = 'Y'", 1
    );
    if (!isset($oKundengruppe->kKundengruppe)) {
        exit();
    }
    $shopURL = Shop::getURL();
    // Alle Bilder?
    if ($kArtikel == $nBildNummer) { // Hole alle Bilder zum Artikel
        $oArtikelPict_arr = Shop::DB()->query(
            "SELECT tartikelpict.cPfad
                FROM tartikelpict
                JOIN tartikel
                    ON tartikel.kArtikel = tartikelpict.kArtikel
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$oKundengruppe->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = " . $kArtikel, 2
        );
        if (is_array($oArtikelPict_arr) && count($oArtikelPict_arr) > 0) {
            foreach ($oArtikelPict_arr as $oArtikelPict) {
                if ($nURL == 1) {
                    echo $shopURL . '/' . gibPfadGroesse($nSize) . $oArtikelPict->cPfad . "\n";
                } else {
                    // Bild ShopURL
                    $cBildpfad = $shopURL. '/' . gibPfadGroesse($nSize) . $oArtikelPict->cPfad;
                    // Format ermitteln
                    $cBildformat = gibBildformat($cBildpfad);
                    // @ToDo - Bild ausgeben
                    if ($cBildformat) {
                        exit();
                    }
                }
            }
        }
    } else { // Hole nur 1 Bild
        $oArtikelPict = Shop::DB()->query(
            "SELECT tartikelpict.cPfad
                FROM tartikelpict
                JOIN tartikel
                    ON tartikel.kArtikel = tartikelpict.kArtikel
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$oKundengruppe->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = " . $kArtikel . "
                    AND tartikelpict.nNr = " . $nBildNummer, 1
        );
        if (strlen($oArtikelPict->cPfad) > 0) {
            if ($nURL == 1) {
                echo $shopURL . '/' . gibPfadGroesse($nSize) . $oArtikelPict->cPfad;
            } else {
                // Bild ShopURL
                $cBildpfad = $shopURL . '/' . gibPfadGroesse($nSize) . $oArtikelPict->cPfad;
                // Format ermitteln
                $cBildformat = gibBildformat($cBildpfad);
                // ToDo - Bild ausgeben
                if ($cBildformat) {
                    $im = ladeBild($cBildpfad);
                    if ($im) {
                        header('Content-type: image/' . $cBildformat);
                        imagepng($im);
                        imagedestroy($im);
                    }
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
function gibPfadGroesse($nSize)
{
    if ($nSize > 0) {
        switch ($nSize) {
            case 1:
                return PFAD_PRODUKTBILDER_GROSS;
                break;

            case 2:
                return PFAD_PRODUKTBILDER_NORMAL;
                break;

            case 3:
                return PFAD_PRODUKTBILDER_KLEIN;
                break;

            case 4:
                return PFAD_PRODUKTBILDER_MINI;
                break;

        }
    }

    return 0;
}

/**
 * @param string $cBildPfad
 * @return bool|string
 */
function gibBildformat($cBildPfad)
{
    $nSize_arr = getimagesize($cBildPfad);
    $nTyp      = $nSize_arr[2];
    switch ($nTyp) {
        case IMAGETYPE_JPEG:
            return 'jpg';
            break;

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

    }

    return false;
}

/**
 * @param string $cBildPfad
 * @return bool|resource
 */
function ladeBild($cBildPfad)
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
