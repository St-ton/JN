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
    return Shop::Container()->getDB()->query(
        'SELECT tsuchspecialoverlay.*, tsuchspecialoverlaysprache.kSprache, 
            tsuchspecialoverlaysprache.cBildPfad, tsuchspecialoverlaysprache.nAktiv,
            tsuchspecialoverlaysprache.nPrio, tsuchspecialoverlaysprache.nMargin,
            tsuchspecialoverlaysprache.nTransparenz,
            tsuchspecialoverlaysprache.nGroesse, tsuchspecialoverlaysprache.nPosition
            FROM tsuchspecialoverlay
            LEFT JOIN tsuchspecialoverlaysprache 
                ON tsuchspecialoverlaysprache.kSuchspecialOverlay = tsuchspecialoverlay.kSuchspecialOverlay
                AND tsuchspecialoverlaysprache.kSprache = ' . (int)$_SESSION['kSprache'] . '
            ORDER BY tsuchspecialoverlay.cSuchspecial',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param int $overlayID
 * @return stdClass|bool
 */
function gibSuchspecialOverlay(int $overlayID)
{
    return Shop::Container()->getDB()->query(
        'SELECT tsuchspecialoverlay.*, tsuchspecialoverlaysprache.kSprache, tsuchspecialoverlaysprache.cBildPfad, 
            tsuchspecialoverlaysprache.nAktiv, tsuchspecialoverlaysprache.nPrio, tsuchspecialoverlaysprache.nMargin, 
            tsuchspecialoverlaysprache.nTransparenz, tsuchspecialoverlaysprache.nGroesse, 
            tsuchspecialoverlaysprache.nPosition
            FROM tsuchspecialoverlay
            LEFT JOIN tsuchspecialoverlaysprache 
                ON tsuchspecialoverlaysprache.kSuchspecialOverlay = tsuchspecialoverlay.kSuchspecialOverlay
                AND tsuchspecialoverlaysprache.kSprache = ' . (int)$_SESSION['kSprache'] . '
            WHERE tsuchspecialoverlay.kSuchspecialOverlay = ' . $overlayID,
        \DB\ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int   $overlayID
 * @param array $post
 * @param array $files
 * @return bool
 */
function speicherEinstellung(int $overlayID, $post, $files)
{
    $overlay                      = new stdClass();
    $overlay->kSuchspecialOverlay = $overlayID;
    $overlay->kSprache            = $_SESSION['kSprache'];
    $overlay->nAktiv              = (int)$post['nAktiv'];
    $overlay->nTransparenz        = (int)$post['nTransparenz'];
    $overlay->nGroesse            = (int)$post['nGroesse'];
    $overlay->nPosition           = isset($post['nPosition'])
        ? (int)$post['nPosition']
        : 0;

    if (!isset($post['nPrio']) || (int)$post['nPrio'] === -1) {
        return false;
    }

    $overlay->nPrio     = (int)$post['nPrio'];
    $overlay->cBildPfad = '';

    if (mb_strlen($files['cSuchspecialOverlayBild']['name']) > 0) {
        $overlay->cBildPfad = 'kSuchspecialOverlay_' . $_SESSION['kSprache'] . '_' .
            $overlayID . mappeFileTyp($files['cSuchspecialOverlayBild']['type']);
    } else {
        $oSuchspecialoverlaySpracheTMP = Shop::Container()->getDB()->select(
            'tsuchspecialoverlaysprache',
            'kSuchspecialOverlay',
            $overlayID,
            'kSprache',
            (int)$_SESSION['kSprache']
        );
        if (isset($oSuchspecialoverlaySpracheTMP->cBildPfad) && mb_strlen($oSuchspecialoverlaySpracheTMP->cBildPfad)) {
            $overlay->cBildPfad = $oSuchspecialoverlaySpracheTMP->cBildPfad;
        }
    }

    if ($overlay->kSuchspecialOverlay > 0) {
        if (mb_strlen($files['cSuchspecialOverlayBild']['name']) > 0) {
            loescheBild($overlay);
            speicherBild($files, $overlay);
        }
        Shop::Container()->getDB()->delete(
            'tsuchspecialoverlaysprache',
            ['kSuchspecialOverlay', 'kSprache'],
            [$overlayID, (int)$_SESSION['kSprache']]
        );
        Shop::Container()->getDB()->insert('tsuchspecialoverlaysprache', $overlay);

        return true;
    }

    return false;
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

    //loop through image pixels and modify alpha for each
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat($src_im, $x, $y);
            $alpha   = ($colorxy >> 24) & 0xFF;
            //calculate new alpha
            if ($minalpha !== 127) {
                $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
            } else {
                $alpha += 127 * $pct;
            }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha(
                $src_im,
                ($colorxy >> 16) & 0xFF,
                ($colorxy >> 8) & 0xFF,
                $colorxy & 0xFF,
                $alpha
            );
            //set pixel with the new color + opacity
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
 * @param string $image
 * @param int    $width
 * @param int    $height
 * @param int    $transparency
 * @return resource
 */
function ladeOverlay($image, $width, $height, $transparency)
{
    $src = imageload_alpha($image, $width, $height);
    if ($transparency > 0) {
        $new = imagecreatetruecolor($width, $height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $width, $height, $transparent);
        imagealphablending($new, true);
        imagesavealpha($new, true);

        imagecopymerge_alpha($new, $src, 0, 0, 0, 0, $width, $height, 100 - $transparency);

        return $new;
    }

    return $src;
}

/**
 * @param resource $im
 * @param string   $extension
 * @param string   $path
 * @param int      $quality
 * @return bool
 */
function speicherOverlay($im, $extension, $path, $quality = 80)
{
    if (!$extension || !$im) {
        return false;
    }
    switch ($extension) {
        case '.jpg':
            if (!function_exists('imagejpeg')) {
                return false;
            }

            return imagejpeg($im, $path, $quality);
            break;
        case '.png':
            if (!function_exists('imagepng')) {
                return false;
            }

            return imagepng($im, $path);
            break;
        case '.gif':
            if (!function_exists('imagegif')) {
                return false;
            }

            return imagegif($im, $path);
            break;
        case '.bmp':
            if (!function_exists('imagewbmp')) {
                return false;
            }

            return imagewbmp($im, $path);
            break;
    }

    return false;
}

/**
 * @deprecated since 4.07
 * @param string $image
 * @param string $width
 * @param string $height
 * @param int    $size
 * @param int    $transparency
 * @param string $extension
 * @param string $path
 */
function erstelleOverlay($image, $width, $height, $size, $transparency, $extension, $path)
{
    $conf = Shop::getSettings([CONF_BILDER]);
    // $bSkalieren    = !($conf['bilder']['bilder_skalieren'] === 'N'); //@todo noch beachten
    $width  = $conf['bilder'][$width];
    $height = $conf['bilder'][$height];

    [$overlayWidth, $overlayHight] = getimagesize($image);

    $nOffX = $nOffY = 1;
    if ($size > 0) {
        $maxWidth  = $width * ($size / 100);
        $maxHeight = $height * ($size / 100);

        $nOffX = $overlayWidth / $maxWidth;
        $nOffY = $overlayHight / $maxHeight;
    }

    if ($nOffY > $nOffX) {
        $overlayWidth = round($overlayWidth * (1 / $nOffY));
        $overlayHight = round($overlayHight * (1 / $nOffY));
    } else {
        $overlayWidth = round($overlayWidth * (1 / $nOffX));
        $overlayHight = round($overlayHight * (1 / $nOffX));
    }

    $im = ladeOverlay($image, $overlayWidth, $overlayHight, $transparency);
    speicherOverlay($im, $extension, $path);
}

/**
 * @param string $image
 * @param int    $size
 * @param int    $transparency
 * @param string $extension
 * @param string $path
 */
function erstelleFixedOverlay($image, $size, $transparency, $extension, $path)
{
//    $conf = Shop::getSettings([CONF_BILDER]);
//    $bSkalieren    = !($conf['bilder']['bilder_skalieren'] === 'N'); //@todo noch beachten

    [$width, $height] = getimagesize($image);
    $factor           = $size / $width;

    $im = ladeOverlay($image, $size, $height * $factor, $transparency);
    speicherOverlay($im, $extension, $path);
}


/**
 * @param array  $files
 * @param object $overlay
 * @return bool
 */
function speicherBild($files, $overlay)
{
    if ($files['cSuchspecialOverlayBild']['type'] === 'image/jpeg'
        || $files['cSuchspecialOverlayBild']['type'] === 'image/pjpeg'
        || $files['cSuchspecialOverlayBild']['type'] === 'image/jpg'
        || $files['cSuchspecialOverlayBild']['type'] === 'image/gif'
        || $files['cSuchspecialOverlayBild']['type'] === 'image/png'
        || $files['cSuchspecialOverlayBild']['type'] === 'image/bmp'
        || $files['cSuchspecialOverlayBild']['type'] === 'image/x-png'
    ) {
        if (empty($files['cSuchspecialOverlayBild']['error'])) {
            $ext      = mappeFileTyp($files['cSuchspecialOverlayBild']['type']);
            $name     = 'kSuchspecialOverlay_' . $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . $ext;
            $original = $files['cSuchspecialOverlayBild']['tmp_name'];
            erstelleFixedOverlay(
                $original,
                $overlay->nGroesse * 4,
                $overlay->nTransparenz,
                $ext,
                PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY_RETINA . $name
            );
            erstelleFixedOverlay(
                $original,
                $overlay->nGroesse * 3,
                $overlay->nTransparenz,
                $ext,
                PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY_GROSS . $name
            );
            erstelleFixedOverlay(
                $original,
                $overlay->nGroesse * 2,
                $overlay->nTransparenz,
                $ext,
                PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY_NORMAL . $name
            );
            erstelleFixedOverlay(
                $original,
                $overlay->nGroesse,
                $overlay->nTransparenz,
                $ext,
                PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY_KLEIN . $name
            );

            return true;
        }
    }

    return false;
}

/**
 * @param object $overlay
 */
function loescheBild($overlay)
{
    if (file_exists(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
        $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . '.jpg')) {
        @unlink(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
            $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . '.jpg');
    } elseif (file_exists(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
        $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . '.png')) {
        @unlink(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
            $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . '.png');
    } elseif (file_exists(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
        $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . '.gif')) {
        @unlink(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
            $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . '.gif');
    } elseif (file_exists(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
        $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . '.bmp')) {
        @unlink(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
            $overlay->kSprache . '_' . $overlay->kSuchspecialOverlay . '.bmp');
    }
}

/**
 * @param string $type
 * @return string
 */
function mappeFileTyp(string $type): string
{
    switch ($type) {
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
