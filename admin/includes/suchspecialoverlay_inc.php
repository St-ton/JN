<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 */
function gibAlleSuchspecialOverlays()
{
    $overlays                  = [];
    $searchspecialOverlayTypes = Shop::Container()->getDB()->query(
        'SELECT kSuchspecialOverlay
            FROM tsuchspecialoverlay',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($searchspecialOverlayTypes as $searchspecialOverlayType) {
        $overlays[] = Overlay::getInstance($searchspecialOverlayType->kSuchspecialOverlay, (int)$_SESSION['kSprache']);
    }

    return $overlays;
}

/**
 * @param int $kSuchspecialOverlay
 * @return mixed
 */
function gibSuchspecialOverlay(int $kSuchspecialOverlay)
{
    return Overlay::getInstance($kSuchspecialOverlay, (int)$_SESSION['kSprache']);
}

/**
 * @param int $kSuchspecialOverlay
 * @param array $cPost_arr
 * @param array $cFiles_arr
 * @param int|null $lang
 * @param string|null $template
 * @return bool
 */
function speicherEinstellung(
    int $kSuchspecialOverlay,
    array $cPost_arr,
    array $cFiles_arr,
    int $lang = null,
    string $template = null
): bool {
    $overlay = Overlay::getInstance($kSuchspecialOverlay, $lang ?? (int)$_SESSION['kSprache'], $template, false);

    if ($overlay->getType() <= 0) {
        return false;
    }
    $overlay->setActive((int)$cPost_arr['nAktiv'])
            ->setTransparence((int)$cPost_arr['nTransparenz'])
            ->setSize((int)$cPost_arr['nGroesse'])
            ->setPosition((int)($cPost_arr['nPosition'] ?? 0))
            ->setPriority((int)$cPost_arr['nPrio']);

    if (strlen($cFiles_arr['name']) > 0) {
        loescheBild($overlay);
        $overlay->setImageName(Overlay::IMAGENAME_TEMPLATE . '_' .
            $overlay->getLanguage() . '_' . $overlay->getType()
                . mappeFileTyp($cFiles_arr['type']));
        speicherBild($cFiles_arr, $overlay);
    }
    $overlay->save();

    return true;
}

/**
 * @param resource $dst_im
 * @param resource $src_im
 * @param int      $dst_x
 * @param int      $dst_y
 * @param int      $src_x
 * @param int      $src_y
 * @param int      $src_w
 * @param int      $src_h
 * @param int      $pct
 * @return bool
 */
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
{
    if ($pct === null) {
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx($src_im);
    $h = imagesy($src_im);
    // Turn alpha blending off
    imagealphablending($src_im, false);

    $minalpha = 0;

    // loop through image pixels and modify alpha for each
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            // get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat($src_im, $x, $y);
            $alpha   = ($colorxy >> 24) & 0xFF;
            // calculate new alpha
            if ($minalpha !== 127) {
                $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
            } else {
                $alpha += 127 * $pct;
            }
            // get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha(
                $src_im,
                ($colorxy >> 16) & 0xFF,
                ($colorxy >> 8) & 0xFF,
                $colorxy & 0xFF,
                $alpha
            );
            // set pixel with the new color + opacity
            if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                return false;
            }
        }
    }

    return imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}

/**
 * @param string $img
 * @param int    $width
 * @param int    $height
 * @return resource|null
 */
function imageload_alpha($img, $width, $height)
{
    $imgInfo = getimagesize($img);
    switch ($imgInfo[2]) {
        case 1:
            $im = imagecreatefromgif($img);
            break;
        case 2:
            $im = imagecreatefromjpeg($img);
            break;
        case 3:
            $im = imagecreatefrompng($img);
            break;
        default:
            return null;
    }

    $new = imagecreatetruecolor($width, $height);

    if ($imgInfo[2] == 1 || $imgInfo[2] == 3) {
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $width, $height, $transparent);
    }

    imagecopyresampled($new, $im, 0, 0, 0, 0, $width, $height, $imgInfo[0], $imgInfo[1]);

    return $new;
}

/**
 * @param string $cBild
 * @param int    $nBreite
 * @param int    $nHoehe
 * @param int    $nTransparenz
 * @return resource
 */
function ladeOverlay($cBild, $nBreite, $nHoehe, $nTransparenz)
{
    $img_src = imageload_alpha($cBild, $nBreite, $nHoehe);

    if ($nTransparenz > 0) {
        $new = imagecreatetruecolor($nBreite, $nHoehe);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $nBreite, $nHoehe, $transparent);
        imagealphablending($new, true);
        imagesavealpha($new, true);

        imagecopymerge_alpha($new, $img_src, 0, 0, 0, 0, $nBreite, $nHoehe, 100 - $nTransparenz);

        return $new;
    }

    return $img_src;
}

/**
 * @param resource $im
 * @param string   $cFormat
 * @param string   $cPfad
 * @param int      $nQuali
 * @return bool
 */
function speicherOverlay($im, $cFormat, $cPfad, $nQuali = 80)
{
    if (!$cFormat || !$im) {
        return false;
    }
    switch ($cFormat) {
        case '.jpg':
            if (!function_exists('imagejpeg')) {
                return false;
            }

            return imagejpeg($im, $cPfad, $nQuali);
            break;
        case '.png':
            if (!function_exists('imagepng')) {
                return false;
            }

            return imagepng($im, $cPfad);
            break;
        case '.gif':
            if (!function_exists('imagegif')) {
                return false;
            }

            return imagegif($im, $cPfad);
            break;
        case '.bmp':
            if (!function_exists('imagewbmp')) {
                return false;
            }

            return imagewbmp($im, $cPfad);
            break;
    }

    return false;
}

/**
 * @deprecated since 4.07
 * @param string $cBild
 * @param string $cBreite
 * @param string $cHoehe
 * @param int    $nGroesse
 * @param int    $nTransparenz
 * @param string $cFormat
 * @param string $cPfad
 */
function erstelleOverlay($cBild, $cBreite, $cHoehe, $nGroesse, $nTransparenz, $cFormat, $cPfad)
{
    $Einstellungen = Shop::getSettings([CONF_BILDER]);
    $bSkalieren    = !($Einstellungen['bilder']['bilder_skalieren'] === 'N'); //@todo noch beachten

    $nBreite = $Einstellungen['bilder'][$cBreite];
    $nHoehe  = $Einstellungen['bilder'][$cHoehe];

    list($nOverlayBreite, $nOverlayHoehe) = getimagesize($cBild);

    $nOffX = $nOffY = 1;
    if ($nGroesse > 0) {
        $nMaxBreite = $nBreite * ($nGroesse / 100);
        $nMaxHoehe  = $nHoehe * ($nGroesse / 100);

        $nOffX = $nOverlayBreite / $nMaxBreite;
        $nOffY = $nOverlayHoehe / $nMaxHoehe;
    }

    if ($nOffY > $nOffX) {
        $nOverlayBreite = round($nOverlayBreite * (1 / $nOffY));
        $nOverlayHoehe  = round($nOverlayHoehe * (1 / $nOffY));
    } else {
        $nOverlayBreite = round($nOverlayBreite * (1 / $nOffX));
        $nOverlayHoehe  = round($nOverlayHoehe * (1 / $nOffX));
    }

    $im = ladeOverlay($cBild, $nOverlayBreite, $nOverlayHoehe, $nTransparenz);
    speicherOverlay($im, $cFormat, $cPfad);
}

/**
 * @param string $cBild
 * @param int    $nGroesse
 * @param int    $nTransparenz
 * @param string $cFormat
 * @param string $cPfad
 */
function erstelleFixedOverlay(string $cBild, int $nGroesse, int $nTransparenz, string $cFormat, string $cPfad): void
{
//    $Einstellungen = Shop::getSettings([CONF_BILDER]);
//    $bSkalieren    = !($Einstellungen['bilder']['bilder_skalieren'] === 'N'); //@todo noch beachten

    [$nBreite, $nHoehe] = getimagesize($cBild);
    $factor             = $nGroesse/$nBreite;

    $im = ladeOverlay($cBild, $nGroesse, $nHoehe*$factor, $nTransparenz);
    speicherOverlay($im, $cFormat, $cPfad);
}


/**
 * @param array $cFiles_arr
 * @param Overlay $overlay
 * @return bool
 */
function speicherBild(array $cFiles_arr, Overlay $overlay): bool
{
    if ($cFiles_arr['type'] === 'image/jpeg'
        || $cFiles_arr['type'] === 'image/pjpeg'
        || $cFiles_arr['type'] === 'image/jpg'
        || $cFiles_arr['type'] === 'image/gif'
        || $cFiles_arr['type'] === 'image/png'
        || $cFiles_arr['type'] === 'image/bmp'
        || $cFiles_arr['type'] === 'image/x-png'
    ) {
        if (empty($cFiles_arr['error'])) {
            $cFormat   = mappeFileTyp($cFiles_arr['type']);
            $cOriginal = $cFiles_arr['tmp_name'];

            $sizesToCreate = [
                ['size' => 'klein',  'factor' => 1],
                ['size' => 'normal', 'factor' => 2],
                ['size' => 'gross',  'factor' => 3],
                ['size' => 'retina', 'factor' => 4],
            ];

            foreach ($sizesToCreate as $sizeToCreate) {
                if (!is_dir(PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']))) {
                    mkdir(PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']), 0755, true);
                }
                erstelleFixedOverlay(
                    $cOriginal,
                    $overlay->getSize() * $sizeToCreate['factor'],
                    $overlay->getTransparance(),
                    $cFormat,
                    PFAD_ROOT . $overlay->getPathSize($sizeToCreate['size']) . $overlay->getImageName()
                );
            }

            return true;
        }
    }

    return false;
}

/**
 * @param Overlay $overlay
 */
function loescheBild(Overlay $overlay): void
{
    foreach ($overlay->getPathSizes() as $path) {
        $path = PFAD_ROOT . $path . $overlay->getImageName();
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

/**
 * @param string $cTyp
 * @return string
 */
function mappeFileTyp(string $cTyp): string
{
    switch ($cTyp) {
        case 'image/jpeg':
            return '.jpg';
        case 'image/pjpeg':
            return '.jpg';
        case 'image/gif':
            return '.gif';
        case 'image/png':
            return '.png';
        case 'image/bmp':
            return '.bmp';
        // Adding MIME types that Internet Explorer returns
        case 'image/x-png':
            return '.png';
        case 'image/jpg':
            return '.jpg';
        //default jpg
        default:
            return '.jpg';
    }
}
