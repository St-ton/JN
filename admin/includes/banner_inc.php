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
    return Shop::DB()->select('textensionpoint', 'cClass', 'ImageMap', 'kInitial', (int)$kImageMap);
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
