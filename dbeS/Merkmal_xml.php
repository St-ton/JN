<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $zipFile   = checkFile();
    $return    = 2;
    $unzipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $unzipPath);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            $d        = file_get_contents($xmlFile);
            $xml      = XML_unserialize($d);
            $fileName = pathinfo($xmlFile)['basename'];

            if ($fileName === 'del_merkmal.xml' || $fileName === 'del_merkmalwert.xml') {
                bearbeiteDeletes($xml);
            } elseif ($fileName === 'merkmal.xml') {
                bearbeiteInsert($xml);
            }

            removeTemporaryFiles($xmlFile);
        }
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
    }
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    // Merkmal
    if (isset($xml['del_merkmale']['kMerkmal']) && is_array($xml['del_merkmale']['kMerkmal'])) {
        foreach ($xml['del_merkmale']['kMerkmal'] as $kMerkmal) {
            if ((int)$kMerkmal > 0) {
                loescheMerkmal((int)$kMerkmal);
            }
        }
    } elseif (isset($xml['del_merkmale']['kMerkmal']) && (int)$xml['del_merkmale']['kMerkmal'] > 0) {
        loescheMerkmal((int)$xml['del_merkmale']['kMerkmal']);
    }
    // MerkmalWert
    // WIRD ZURZEIT NOCH NICHT GENUTZT WEGEN MOEGLICHER INKONSISTENZ
    if (isset($xml['del_merkmalwerte']['kMerkmalWert']) && is_array($xml['del_merkmalwerte']['kMerkmalWert'])) {
        foreach ($xml['del_merkmalwerte']['kMerkmalWert'] as $kMerkmalWert) {
            if ((int)$kMerkmalWert > 0) {
                loescheMerkmalWert((int)$kMerkmalWert);
            }
        }
    } elseif (isset($xml['del_merkmalwerte']['kMerkmalWert']) && (int)$xml['del_merkmalwerte']['kMerkmalWert'] > 0) {
        loescheMerkmalWert((int)$xml['del_merkmalwerte']['kMerkmalWert']);
    }
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_ATTRIBUTE]);
}

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    $db = Shop::Container()->getDB();
    if (isset($xml['merkmale']['tmerkmal']) && is_array($xml['merkmale']['tmerkmal'])) {
        $oSprachSTD = Sprache::getDefaultLanguage();
        $oMM_arr    = []; // Merkt sich alle MerkmalWerte die von der Wawi geschickt werden
        $attributes = mapArray($xml['merkmale'], 'tmerkmal', $GLOBALS['mMerkmal']);
        $mmCount    = count($attributes);
        for ($i = 0; $i < $mmCount; $i++) {
            if (!isset($oMM_arr[$i]) || $oMM_arr[$i] === null) {
                $oMM_arr[$i] = new stdClass();
            }
            if (isset($attributes[$i]->nMehrfachauswahl)) {
                if ($attributes[$i]->nMehrfachauswahl > 1) {
                    $attributes[$i]->nMehrfachauswahl = 1;
                }
            } else {
                $attributes[$i]->nMehrfachauswahl = 0;
            }
            $oMerkmal                  = merkeBildPfad($attributes[$i]->kMerkmal);
            $attributes[$i]->cBildpfad = $oMerkmal->cBildpfad ?? '';
            $oMM_arr[$i]->oMMW_arr     = [];

            if ($mmCount < 2) {
                $attrValues = mapArray($xml['merkmale']['tmerkmal'], 'tmerkmalwert', $GLOBALS['mMerkmalWert']);
                if (count($attrValues) > 0) {
                    loescheMerkmal($xml['merkmale']['tmerkmal attr']['kMerkmal'], 0);
                } else {
                    loescheNurMerkmal($xml['merkmale']['tmerkmal attr']['kMerkmal']);
                }
                updateXMLinDB(
                    $xml['merkmale']['tmerkmal'],
                    'tmerkmalsprache',
                    $GLOBALS['mMerkmalSprache'],
                    'kMerkmal',
                    'kSprache'
                );
                if (count($attrValues) > 0) {
                    $mmwCountO = count($attrValues);
                    for ($o = 0; $o < $mmwCountO; $o++) {
                        $oMM_arr[$i]->oMMW_arr[$o]->kMerkmalWert = $attrValues[$o]->kMerkmalWert;
                        $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr = [];

                        if (count($attrValues) < 2) {
                            $localized = mapArray(
                                $xml['merkmale']['tmerkmal']['tmerkmalwert'],
                                'tmerkmalwertsprache',
                                $GLOBALS['mMerkmalWertSprache']
                            );
                            $mmwsCount = count($localized);
                            for ($j = 0; $j < $mmwsCount; ++$j) {
                                $db->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [
                                        (int)$localized[$j]->kMerkmalWert,
                                        'kMerkmalWert',
                                        (int)$localized[$j]->kSprache
                                    ]
                                );
                                if (trim($localized[$j]->cSeo)) {
                                    $cSeo = \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cSeo);
                                } else {
                                    $cSeo = \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cWert);
                                }
                                $localized[$j]->cSeo = \JTL\SeoHelper::getSeo($cSeo);
                                $localized[$j]->cSeo = \JTL\SeoHelper::checkSeo($localized[$j]->cSeo);
                                DBUpdateInsert(
                                    'tmerkmalwertsprache',
                                    [$localized[$j]],
                                    'kMerkmalWert',
                                    'kSprache'
                                );
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = $localized[$j]->cSeo;
                                $oSeo->cKey     = 'kMerkmalWert';
                                $oSeo->kKey     = $localized[$j]->kMerkmalWert;
                                $oSeo->kSprache = $localized[$j]->kSprache;
                                $db->insert('tseo', $oSeo);

                                if (!in_array($localized[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache == $oSprachSTD->kSprache) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        } else {
                            $localized  = mapArray(
                                $xml['merkmale']['tmerkmal']['tmerkmalwert'][$o],
                                'tmerkmalwertsprache',
                                $GLOBALS['mMerkmalWertSprache']
                            );
                            $mmwsaCount = count($localized);
                            for ($j = 0; $j < $mmwsaCount; $j++) {
                                $db->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [
                                        (int)$localized[$j]->kMerkmalWert,
                                        'kMerkmalWert',
                                        (int)$localized[$j]->kSprache
                                    ]
                                );
                                if (trim($localized[$j]->cSeo)) {
                                    $cSeo = \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cSeo);
                                } else {
                                    $cSeo = \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cWert);
                                }
                                $localized[$j]->cSeo = \JTL\SeoHelper::getSeo($cSeo);
                                $localized[$j]->cSeo = \JTL\SeoHelper::checkSeo($localized[$j]->cSeo);
                                DBUpdateInsert(
                                    'tmerkmalwertsprache',
                                    [$localized[$j]],
                                    'kMerkmalWert',
                                    'kSprache'
                                );
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = $localized[$j]->cSeo;
                                $oSeo->cKey     = 'kMerkmalWert';
                                $oSeo->kKey     = (int)$localized[$j]->kMerkmalWert;
                                $oSeo->kSprache = (int)$localized[$j]->kSprache;
                                $db->insert('tseo', $oSeo);

                                if (!in_array($localized[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache == $oSprachSTD->kSprache) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        }
                    }
                }
            } else {
                $attrValues = mapArray($xml['merkmale']['tmerkmal'][$i], 'tmerkmalwert', $GLOBALS['mMerkmalWert']);
                if (count($attrValues) > 0) {
                    loescheMerkmal($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal'], 0);
                } else {
                    loescheNurMerkmal($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal']);
                }

                updateXMLinDB(
                    $xml['merkmale']['tmerkmal'][$i],
                    'tmerkmalsprache',
                    $GLOBALS['mMerkmalSprache'],
                    'kMerkmal',
                    'kSprache'
                );
                $mmwCount = count($attrValues);
                if (is_array($attrValues) && $mmwCount > 0) {
                    for ($o = 0; $o < $mmwCount; $o++) {
                        $oMM_arr[$i]->oMMW_arr[$o]->kMerkmalWert = $attrValues[$o]->kMerkmalWert;
                        $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr = [];

                        if (count($attrValues) < 2) {
                            $localized = mapArray(
                                $xml['merkmale']['tmerkmal'][$i]['tmerkmalwert'],
                                'tmerkmalwertsprache',
                                $GLOBALS['mMerkmalWertSprache']
                            );
                            $cnt       = count($localized);
                            for ($j = 0; $j < $cnt; $j++) {
                                $db->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [
                                        (int)$localized[$j]->kMerkmalWert,
                                        'kMerkmalWert',
                                        (int)$localized[$j]->kSprache
                                    ]
                                );
                                $cSeo = trim($localized[$j]->cSeo)
                                    ? \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cSeo)
                                    : \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cWert);

                                $localized[$j]->cSeo = \JTL\SeoHelper::getSeo($cSeo);
                                $localized[$j]->cSeo = \JTL\SeoHelper::checkSeo($localized[$j]->cSeo);
                                DBUpdateInsert(
                                    'tmerkmalwertsprache',
                                    [$localized[$j]],
                                    'kMerkmalWert',
                                    'kSprache'
                                );
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = $localized[$j]->cSeo;
                                $oSeo->cKey     = 'kMerkmalWert';
                                $oSeo->kKey     = (int)$localized[$j]->kMerkmalWert;
                                $oSeo->kSprache = (int)$localized[$j]->kSprache;
                                $db->insert('tseo', $oSeo);

                                if (!in_array($localized[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache == $oSprachSTD->kSprache) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        } else {
                            $localized  = mapArray(
                                $xml['merkmale']['tmerkmal'][$i]['tmerkmalwert'][$o],
                                'tmerkmalwertsprache',
                                $GLOBALS['mMerkmalWertSprache']
                            );
                            $mmwsaCount = count($localized);
                            for ($j = 0; $j < $mmwsaCount; ++$j) {
                                $db->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [
                                        (int)$localized[$j]->kMerkmalWert,
                                        'kMerkmalWert',
                                        (int)$localized[$j]->kSprache
                                    ]
                                );
                                if (trim($localized[$j]->cSeo)) {
                                    $cSeo = \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cSeo);
                                } else {
                                    $cSeo = \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cWert);
                                }

                                $localized[$j]->cSeo = \JTL\SeoHelper::getSeo($cSeo);
                                $localized[$j]->cSeo = \JTL\SeoHelper::checkSeo($localized[$j]->cSeo);
                                DBUpdateInsert(
                                    'tmerkmalwertsprache',
                                    [$localized[$j]],
                                    'kMerkmalWert',
                                    'kSprache'
                                );
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = $localized[$j]->cSeo;
                                $oSeo->cKey     = 'kMerkmalWert';
                                $oSeo->kKey     = $localized[$j]->kMerkmalWert;
                                $oSeo->kSprache = $localized[$j]->kSprache;
                                $db->insert('tseo', $oSeo);

                                if (!in_array($localized[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache == $oSprachSTD->kSprache) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        }
                    }
                }
            }
        }
        DBUpdateInsert('tmerkmal', $attributes, 'kMerkmal');
        fuelleFehlendeMMWInSeo($oMM_arr);
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_ATTRIBUTE]);
    }
    // Kommen nur MerkmalWerte?
    if (!isset($xml['merkmale']['tmerkmalwert']) || !is_array($xml['merkmale']['tmerkmalwert'])) {
        return;
    }
    $attrValues = mapArray($xml['merkmale'], 'tmerkmalwert', $GLOBALS['mMerkmalWert']);
    $i          = 0;

    if (!isset($oMM_arr[$i]) || $oMM_arr[$i] === null) {
        $oMM_arr[$i] = new stdClass();
    }

    $oMM_arr[$i]->oMMW_arr = [];
    $mmwCount              = count($attrValues);
    for ($o = 0; $o < $mmwCount; $o++) {
        loescheMerkmalWert($attrValues[$o]->kMerkmalWert, true);
        $oMM_arr[$i]->oMMW_arr[$o]               = new stdClass();
        $oMM_arr[$i]->oMMW_arr[$o]->kMerkmalWert = $attrValues[$o]->kMerkmalWert;
        $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr = [];

        if (count($attrValues) < 2) {
            $localized = mapArray(
                $xml['merkmale']['tmerkmalwert'],
                'tmerkmalwertsprache',
                $GLOBALS['mMerkmalWertSprache']
            );
        } else {
            $localized = mapArray(
                $xml['merkmale']['tmerkmalwert'][$o],
                'tmerkmalwertsprache',
                $GLOBALS['mMerkmalWertSprache']
            );
        }
        $mmwsaCount = count($localized);
        for ($j = 0; $j < $mmwsaCount; $j++) {
            $db->delete(
                'tseo',
                ['kKey', 'cKey', 'kSprache'],
                [
                    (int)$localized[$j]->kMerkmalWert,
                    'kMerkmalWert',
                    (int)$localized[$j]->kSprache
                ]
            );
            $cSeo = trim($localized[$j]->cSeo)
                ? \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cSeo)
                : \JTL\SeoHelper::getFlatSeoPath($localized[$j]->cWert);

            $localized[$j]->cSeo = \JTL\SeoHelper::getSeo($cSeo);
            $localized[$j]->cSeo = \JTL\SeoHelper::checkSeo($localized[$j]->cSeo);
            DBUpdateInsert('tmerkmalwertsprache', [$localized[$j]], 'kMerkmalWert', 'kSprache');
            $oSeo           = new stdClass();
            $oSeo->cSeo     = $localized[$j]->cSeo;
            $oSeo->cKey     = 'kMerkmalWert';
            $oSeo->kKey     = $localized[$j]->kMerkmalWert;
            $oSeo->kSprache = $localized[$j]->kSprache;
            $db->insert('tseo', $oSeo);

            if (!in_array($localized[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
            }

            if (isset($localized[$j]->kSprache, $oSprachSTD->kSprache)
                && $localized[$j]->kSprache == $oSprachSTD->kSprache
            ) {
                $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
            }
        }
        $kMerkmalWert     = $attrValues[$o]->kMerkmalWert;
        $oMerkmalWertBild = $db->select('tmerkmalwertbild', 'kMerkmalWert', (int)$kMerkmalWert);

        $attrValues[$o]->cBildpfad = $oMerkmalWertBild->cBildpfad ?? '';
        DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
    }
    fuelleFehlendeMMWInSeo($oMM_arr);
}

/**
 * Geht $oMMW_arr durch welches vorher mit den mitgeschickten Merkmalwerten gefüllt wurde
 * und füllt die Seo Tabelle in den Sprachen, die nicht von der Wawi mitgeschickt wurden
 *
 * @param array $oMM_arr
 */
function fuelleFehlendeMMWInSeo($oMM_arr)
{
    if (!is_array($oMM_arr)) {
        return;
    }
    $db           = Shop::Container()->getDB();
    $oSprache_arr = $db->query(
        'SELECT kSprache FROM tsprache ORDER BY kSprache',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oMM_arr as $oMM) {
        foreach ($oMM->oMMW_arr as $oMMW) {
            foreach ($oSprache_arr as $oSprache) {
                $bVorhanden = false;
                foreach ($oMMW->kSprache_arr as $kSprache) {
                    // Laufe alle gefüllten Sprachen durch
                    if ($kSprache == $oSprache->kSprache) {
                        $bVorhanden = true;
                        break;
                    }
                }
                if ($bVorhanden) {
                    continue;
                }
                // Sprache vom Shop wurde nicht von der Wawi mitgeschickt und muss somit in tseo nachgefüllt werden
                $cSeo = isset($oMMW->cNameSTD) ? \JTL\SeoHelper::getSeo($oMMW->cNameSTD) : '';
                $cSeo = \JTL\SeoHelper::checkSeo($cSeo);
                $db->query(
                    "DELETE tmerkmalwertsprache, tseo FROM tmerkmalwertsprache
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kMerkmalWert'
                                AND tseo.kKey = " . (int)$oMMW->kMerkmalWert . '
                                AND tseo.kSprache = ' . (int)$oSprache->kSprache . '
                        WHERE tmerkmalwertsprache.kMerkmalWert = ' . (int)$oMMW->kMerkmalWert . '
                            AND tmerkmalwertsprache.kSprache = ' . (int)$oSprache->kSprache,
                    \DB\ReturnType::DEFAULT
                );
                //@todo: 1062: Duplicate entry '' for key 'PRIMARY'
                if ($cSeo !== '' && $cSeo !== null) {
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = $cSeo;
                    $oSeo->cKey     = 'kMerkmalWert';
                    $oSeo->kKey     = (int)$oMMW->kMerkmalWert;
                    $oSeo->kSprache = (int)$oSprache->kSprache;
                    $db->insert('tseo', $oSeo);
                    $attrLang                   = new stdClass();
                    $attrLang->kMerkmalWert     = (int)$oMMW->kMerkmalWert;
                    $attrLang->kSprache         = (int)$oSprache->kSprache;
                    $attrLang->cWert            = $oMMW->cNameSTD ?? '';
                    $attrLang->cSeo             = $oSeo->cSeo ?? '';
                    $attrLang->cMetaTitle       = $oMMW->cMetaTitleSTD ?? '';
                    $attrLang->cMetaKeywords    = $oMMW->cMetaKeywordsSTD ?? '';
                    $attrLang->cMetaDescription = $oMMW->cMetaDescriptionSTD ?? '';
                    $attrLang->cBeschreibung    = $oMMW->cBeschreibungSTD ?? '';
                    $db->insert('tmerkmalwertsprache', $attrLang);
                }
            }
        }
    }
}

/**
 * @param int $kMerkmal
 * @param int $update
 */
function loescheMerkmal(int $kMerkmal, $update = 1)
{
    if (!($kMerkmal > 0)) {
        return;
    }
    $db = Shop::Container()->getDB();
    $db->query(
        "DELETE tseo
            FROM tseo
            INNER JOIN tmerkmalwert
                ON tmerkmalwert.kMerkmalWert = tseo.kKey
            INNER JOIN tmerkmal
                ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
            WHERE tseo.cKey = 'kMerkmalWert'
                AND tmerkmal.kMerkmal = " . $kMerkmal,
        \DB\ReturnType::DEFAULT
    );

    if ($update) {
        $db->delete('tartikelmerkmal', 'kMerkmal', $kMerkmal);
    }
    $db->delete('tmerkmal', 'kMerkmal', $kMerkmal);
    $db->delete('tmerkmalsprache', 'kMerkmal', $kMerkmal);
    $werte_arr = $db->selectAll('tmerkmalwert', 'kMerkmal', $kMerkmal, 'kMerkmalWert');
    foreach ($werte_arr as $wert) {
        $db->delete('tmerkmalwertsprache', 'kMerkmalWert', (int)$wert->kMerkmalWert);
        $db->delete('tmerkmalwertbild', 'kMerkmalWert', (int)$wert->kMerkmalWert);
    }
    $db->delete('tmerkmalwert', 'kMerkmal', $kMerkmal);
}

/**
 * @param int $kMerkmal
 */
function loescheNurMerkmal(int $kMerkmal)
{
    if (!($kMerkmal > 0)) {
        return;
    }
    Shop::Container()->getDB()->query(
        "DELETE tseo
            FROM tseo
            INNER JOIN tmerkmalwert
                ON tmerkmalwert.kMerkmalWert = tseo.kKey
            INNER JOIN tmerkmal
                ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
            WHERE tseo.cKey = 'kMerkmalWert'
                AND tmerkmal.kMerkmal = " . $kMerkmal,
        \DB\ReturnType::DEFAULT
    );

    Shop::Container()->getDB()->delete('tmerkmal', 'kMerkmal', $kMerkmal);
    Shop::Container()->getDB()->delete('tmerkmalsprache', 'kMerkmal', $kMerkmal);
}

/**
 * WIRD ZURZEIT NOCH NICHT GENUTZT WEGEN MOEGLICHER INKONSISTENZ
 *
 * @param int  $kMerkmalWert
 * @param bool $isInsert
 */
function loescheMerkmalWert(int $kMerkmalWert, $isInsert = false)
{
    if (!($kMerkmalWert > 0)) {
        return;
    }
    Shop::Container()->getDB()->delete('tseo', ['cKey', 'kKey'], ['kMerkmalWert', $kMerkmalWert]);
    // Hat das Merkmal vor dem Loeschen noch mehr als einen Wert?
    // Wenn nein => nach dem Loeschen auch das Merkmal loeschen
    $oAnzahl = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl, kMerkmal
            FROM tmerkmalwert
            WHERE kMerkmal = (
                SELECT kMerkmal
                FROM tmerkmalwert
                WHERE kMerkmalWert = ' . $kMerkmalWert . ')',
        \DB\ReturnType::SINGLE_OBJECT
    );

    Shop::Container()->getDB()->query(
        'DELETE tmerkmalwert, tmerkmalwertsprache
            FROM tmerkmalwert
            JOIN tmerkmalwertsprache
                ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
            WHERE tmerkmalwert.kMerkmalWert = ' . $kMerkmalWert,
        \DB\ReturnType::DEFAULT
    );
    // Das Merkmal hat keine MerkmalWerte mehr => auch loeschen
    if (!$isInsert && (int)$oAnzahl->nAnzahl === 1) {
        loescheMerkmal($oAnzahl->kMerkmal);
    }
}

/**
 * @param int $kMerkmal
 * @return stdClass
 */
function merkeBildPfad(int $kMerkmal)
{
    $oMerkmal                   = new stdClass();
    $oMerkmal->oMerkmalWert_arr = [];
    if ($kMerkmal > 0) {
        $oMerkmalTMP = Shop::Container()->getDB()->select('tmerkmal', 'kMerkmal', $kMerkmal);
        if (isset($oMerkmalTMP->kMerkmal) && $oMerkmalTMP->kMerkmal > 0) {
            $oMerkmal->kMerkmal  = $oMerkmalTMP->kMerkmal;
            $oMerkmal->cBildpfad = $oMerkmalTMP->cBildpfad;
        }
        $oMerkmalWert_arr = Shop::Container()->getDB()->selectAll(
            'tmerkmalwert',
            'kMerkmal',
            $kMerkmal,
            'kMerkmalWert, cBildpfad'
        );
        foreach ($oMerkmalWert_arr as $oMerkmalWert) {
            $oMerkmal->oMerkmalWert_arr[$oMerkmalWert->kMerkmalWert] = $oMerkmalWert->cBildpfad;
        }
    }

    return $oMerkmal;
}
