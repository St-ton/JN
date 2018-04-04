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
    return Shop::Container()->getDB()->select('textensionpoint', 'cClass', 'ImageMap', 'kInitial', (int)$kImageMap);
}

/**
 * @param int $kImageMap
 * @return mixed
 */
function entferneBanner($kImageMap)
{
    $kImageMap = (int)$kImageMap;
    $oBanner   = new ImageMap();
    Shop::Container()->getDB()->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $kImageMap]);

    return $oBanner->delete($kImageMap);
}

/**
 * @return array
 */
function holeBannerDateien()
{
    $cBannerFile_arr = [];
    if (($nHandle = opendir(PFAD_ROOT . PFAD_BILDER_BANNER)) !== false) {
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
 * @param $cData
 * @return IOResponse
 */
function saveBannerAreasIO($cData)
{
    $oBanner  = new ImageMap();
    $response = new IOResponse();
    $oData    = json_decode($cData);

    foreach ($oData->oArea_arr as $oArea) {
        $oArea->kArtikel      = (int)$oArea->kArtikel;
        $oArea->kImageMap     = (int)$oArea->kImageMap;
        $oArea->kImageMapArea = (int)$oArea->kImageMapArea;
    }

    $oBanner->saveAreas($oData);

    return $response;
}
