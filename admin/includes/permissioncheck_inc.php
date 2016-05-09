<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 */
function getWriteables()
{
    $cWriteable_arr = array(
        'bilder/brandingbilder',
        'bilder/hersteller/klein',
        'bilder/hersteller/normal',
        'bilder/intern/shoplogo',
        'bilder/intern/trustedshops',
        'bilder/kategorien',
        'bilder/links',
        'bilder/merkmale/klein',
        'bilder/merkmale/normal',
        'bilder/merkmalwerte/klein',
        'bilder/merkmalwerte/normal',
        'bilder/news',
        'bilder/newsletter',
        'bilder/produkte/mini',
        'bilder/produkte/klein',
        'bilder/produkte/normal',
        'bilder/produkte/gross',
        'bilder/suchspecialoverlay/klein',
        'bilder/suchspecialoverlay/normal',
        'bilder/suchspecialoverlay/gross',
        'bilder/variationen/mini',
        'bilder/variationen/normal',
        'bilder/variationen/gross',
        'bilder/suchspecialoverlay/klein',
        'bilder/suchspecialoverlay/normal',
        'bilder/suchspecialoverlay/gross',
        'bilder/konfigurator/klein',
        'mediafiles/Bilder',
        'mediafiles/Musik',
        'mediafiles/Sonstiges',
        'mediafiles/Videos',
        'media/image/product',
        'media/image/storage',
        'export',
        'export/backup',
        'export/yatego',
        'jtllogs',
        'templates_c',
        PFAD_ADMIN . 'templates_c',
        PFAD_ADMIN . 'includes/emailpdfs',
        'dbeS/logs',
        'dbeS/tmp',
        'shopinfo.xml',
        'rss.xml',
        'uploads');

    return $cWriteable_arr;
}

/**
 * @return array
 */
function checkWriteables()
{
    $cCheckAssoc_arr = array();
    $cWriteable_arr  = getWriteables();

    foreach ($cWriteable_arr as $cWriteable) {
        $cCheckAssoc_arr[$cWriteable] = false;
        if (is_writable(PFAD_ROOT . $cWriteable)) {
            $cCheckAssoc_arr[$cWriteable] = true;
        }
    }

    return $cCheckAssoc_arr;
}

/**
 * @param array $cDirAssoc_arr
 * @return stdClass
 */
function getPermissionStats($cDirAssoc_arr)
{
    $oStat                = new stdClass();
    $oStat->nCount        = 0;
    $oStat->nCountInValid = 0;

    if (is_array($cDirAssoc_arr) && count($cDirAssoc_arr) > 0) {
        foreach ($cDirAssoc_arr as $cDir => $isValid) {
            $oStat->nCount++;
            if (!$isValid) {
                $oStat->nCountInValid++;
            }
        }
    }

    return $oStat;
}
