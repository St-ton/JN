<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return mixed
 */
function holeAlleBanner()
{
    $oBanner = new ImageMap();

    return $oBanner->fetchAll();
}

/**
 * @param int  $kImageMap
 * @param bool $fill
 * @return mixed
 */
function holeBanner($kImageMap, $fill = true)
{
    $oBanner = new ImageMap();

    return $oBanner->fetch($kImageMap, true, $fill);
}

/**
 * @param int $kImageMap
 * @return mixed
 */
function holeExtension($kImageMap)
{
    return Shop::DB()->query("SELECT * FROM textensionpoint WHERE cClass = 'ImageMap' AND kInitial = " . (int)$kImageMap . " LIMIT 1", 1);
}

/**
 * @param int $kImageMap
 * @return mixed
 */
function entferneBanner($kImageMap)
{
    $kImageMap = (int)$kImageMap;
    $oBanner   = new ImageMap();
    Shop::DB()->delete('textensionpoint', array('cClass', 'kInitial'), array('ImageMap', $kImageMap));

    return $oBanner->delete($kImageMap);
}

/**
 * @return array
 */
function holeBannerDateien()
{
    $cBannerFile_arr = array();
    if ($nHandle = opendir(PFAD_ROOT . PFAD_BILDER_BANNER)) {
        while (false !== ($cFile = readdir($nHandle))) {
            if ($cFile !== '.' && $cFile !== '..' && $cFile[0] !== '.') {
                $cBannerFile_arr[] = $cFile;
            }
        }
        closedir($nHandle);
    }

    return $cBannerFile_arr;
}

/**
 * @param string $size_str
 * @return mixed
 */
function return_bytes($size_str)
{
    switch (substr($size_str, -1)) {
        case 'M': case 'm': return (int)$size_str * 1048576;
        case 'K': case 'k': return (int)$size_str * 1024;
        case 'G': case 'g': return (int)$size_str * 1073741824;
        default: return $size_str;
    }
}
