<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

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
        $defaultLanguage = Sprache::getDefaultLanguage();
        $attributeValues = []; // Merkt sich alle MerkmalWerte die von der Wawi geschickt werden
        $attributes      = mapArray($xml['merkmale'], 'tmerkmal', Mapper::getMapping('mMerkmal'));
        $mmCount         = count($attributes);
        for ($i = 0; $i < $mmCount; $i++) {
            if (!isset($attributeValues[$i]) || $attributeValues[$i] === null) {
                $attributeValues[$i] = new stdClass();
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
            $attributeValues[$i]->oMMW_arr     = [];

            if ($mmCount < 2) {
                $attrValues = mapArray(
                    $xml['merkmale']['tmerkmal'],
                    'tmerkmalwert',
                    Mapper::getMapping('mMerkmalWert')
                );
                if (count($attrValues) > 0) {
                    loescheMerkmal($xml['merkmale']['tmerkmal attr']['kMerkmal'], 0);
                } else {
                    loescheNurMerkmal($xml['merkmale']['tmerkmal attr']['kMerkmal']);
                }
                updateXMLinDB(
                    $xml['merkmale']['tmerkmal'],
                    'tmerkmalsprache',
                    Mapper::getMapping('mMerkmalSprache'),
                    'kMerkmal',
                    'kSprache'
                );
                if (count($attrValues) > 0) {
                    $mmwCountO = count($attrValues);
                    for ($o = 0; $o < $mmwCountO; $o++) {
                        $attributeValues[$i]->oMMW_arr[$o]->kMerkmalWert = $attrValues[$o]->kMerkmalWert;
                        $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr = [];

                        if (count($attrValues) < 2) {
                            $localized = mapArray(
                                $xml['merkmale']['tmerkmal']['tmerkmalwert'],
                                'tmerkmalwertsprache',
                                Mapper::getMapping('mMerkmalWertSprache')
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

                                if (!in_array($localized[$j]->kSprache, $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache == $defaultLanguage->kSprache) {
                                    $attributeValues[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                                    $attributeValues[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $attributeValues[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        } else {
                            $localized  = mapArray(
                                $xml['merkmale']['tmerkmal']['tmerkmalwert'][$o],
                                'tmerkmalwertsprache',
                                Mapper::getMapping('mMerkmalWertSprache')
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

                                if (!in_array($localized[$j]->kSprache, $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache == $defaultLanguage->kSprache) {
                                    $attributeValues[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                                    $attributeValues[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $attributeValues[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        }
                    }
                }
            } else {
                $attrValues = mapArray(
                    $xml['merkmale']['tmerkmal'][$i],
                    'tmerkmalwert',
                    Mapper::getMapping('mMerkmalWert')
                );
                if (count($attrValues) > 0) {
                    loescheMerkmal($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal'], 0);
                } else {
                    loescheNurMerkmal($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal']);
                }

                updateXMLinDB(
                    $xml['merkmale']['tmerkmal'][$i],
                    'tmerkmalsprache',
                    Mapper::getMapping('mMerkmalSprache'),
                    'kMerkmal',
                    'kSprache'
                );
                $mmwCount = count($attrValues);
                if (is_array($attrValues) && $mmwCount > 0) {
                    for ($o = 0; $o < $mmwCount; $o++) {
                        $attributeValues[$i]->oMMW_arr[$o]->kMerkmalWert = $attrValues[$o]->kMerkmalWert;
                        $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr = [];

                        if (count($attrValues) < 2) {
                            $localized = mapArray(
                                $xml['merkmale']['tmerkmal'][$i]['tmerkmalwert'],
                                'tmerkmalwertsprache',
                                Mapper::getMapping('mMerkmalWertSprache')
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

                                if (!in_array($localized[$j]->kSprache, $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache == $defaultLanguage->kSprache) {
                                    $attributeValues[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                                    $attributeValues[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $attributeValues[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        } else {
                            $localized  = mapArray(
                                $xml['merkmale']['tmerkmal'][$i]['tmerkmalwert'][$o],
                                'tmerkmalwertsprache',
                                Mapper::getMapping('mMerkmalWertSprache')
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

                                if (!in_array($localized[$j]->kSprache, $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache == $defaultLanguage->kSprache) {
                                    $attributeValues[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                                    $attributeValues[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $attributeValues[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $attributeValues[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
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
        fuelleFehlendeMMWInSeo($attributeValues);
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_ATTRIBUTE]);
    }
    // Kommen nur MerkmalWerte?
    if (!isset($xml['merkmale']['tmerkmalwert']) || !is_array($xml['merkmale']['tmerkmalwert'])) {
        return;
    }
    $attrValues = mapArray($xml['merkmale'], 'tmerkmalwert', Mapper::getMapping('mMerkmalWert'));
    $i          = 0;

    if (!isset($attributeValues[$i]) || $attributeValues[$i] === null) {
        $attributeValues[$i] = new stdClass();
    }

    $attributeValues[$i]->oMMW_arr = [];
    $mmwCount                      = count($attrValues);
    for ($o = 0; $o < $mmwCount; $o++) {
        loescheMerkmalWert($attrValues[$o]->kMerkmalWert, true);
        $attributeValues[$i]->oMMW_arr[$o]               = new stdClass();
        $attributeValues[$i]->oMMW_arr[$o]->kMerkmalWert = $attrValues[$o]->kMerkmalWert;
        $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr = [];

        if (count($attrValues) < 2) {
            $localized = mapArray(
                $xml['merkmale']['tmerkmalwert'],
                'tmerkmalwertsprache',
                Mapper::getMapping('mMerkmalWertSprache')
            );
        } else {
            $localized = mapArray(
                $xml['merkmale']['tmerkmalwert'][$o],
                'tmerkmalwertsprache',
                Mapper::getMapping('mMerkmalWertSprache')
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

            if (!in_array($localized[$j]->kSprache, $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr)) {
                $attributeValues[$i]->oMMW_arr[$o]->kSprache_arr[] = $localized[$j]->kSprache;
            }

            if (isset($localized[$j]->kSprache, $defaultLanguage->kSprache)
                && $localized[$j]->kSprache == $defaultLanguage->kSprache
            ) {
                $attributeValues[$i]->oMMW_arr[$o]->cNameSTD            = $localized[$j]->cWert;
                $attributeValues[$i]->oMMW_arr[$o]->cSeoSTD             = $localized[$j]->cSeo;
                $attributeValues[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                $attributeValues[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                $attributeValues[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                $attributeValues[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
            }
        }
        $kMerkmalWert     = $attrValues[$o]->kMerkmalWert;
        $oMerkmalWertBild = $db->select('tmerkmalwertbild', 'kMerkmalWert', (int)$kMerkmalWert);

        $attrValues[$o]->cBildpfad = $oMerkmalWertBild->cBildpfad ?? '';
        DBUpdateInsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
    }
    fuelleFehlendeMMWInSeo($attributeValues);
}

/**
 * Geht $oMMW_arr durch welches vorher mit den mitgeschickten Merkmalwerten gefüllt wurde
 * und füllt die Seo Tabelle in den Sprachen, die nicht von der Wawi mitgeschickt wurden
 *
 * @param array $attributes
 */
function fuelleFehlendeMMWInSeo($attributes)
{
    if (!is_array($attributes)) {
        return;
    }
    $db        = Shop::Container()->getDB();
    $languages = $db->query(
        'SELECT kSprache FROM tsprache ORDER BY kSprache',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($attributes as $attribute) {
        foreach ($attribute->oMMW_arr as $attributeValue) {
            foreach ($languages as $language) {
                $exists = false;
                foreach ($attributeValue->kSprache_arr as $kSprache) {
                    // Laufe alle gefüllten Sprachen durch
                    if ($kSprache == $language->kSprache) {
                        $exists = true;
                        break;
                    }
                }
                if ($exists) {
                    continue;
                }
                // Sprache vom Shop wurde nicht von der Wawi mitgeschickt und muss somit in tseo nachgefüllt werden
                $cSeo = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($attributeValue->cNameSTD ?? ''));
                $db->query(
                    "DELETE tmerkmalwertsprache, tseo FROM tmerkmalwertsprache
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kMerkmalWert'
                                AND tseo.kKey = " . (int)$attributeValue->kMerkmalWert . '
                                AND tseo.kSprache = ' . (int)$language->kSprache . '
                        WHERE tmerkmalwertsprache.kMerkmalWert = ' . (int)$attributeValue->kMerkmalWert . '
                            AND tmerkmalwertsprache.kSprache = ' . (int)$language->kSprache,
                    \DB\ReturnType::DEFAULT
                );
                //@todo: 1062: Duplicate entry '' for key 'PRIMARY'
                if ($cSeo !== '' && $cSeo !== null) {
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = $cSeo;
                    $oSeo->cKey     = 'kMerkmalWert';
                    $oSeo->kKey     = (int)$attributeValue->kMerkmalWert;
                    $oSeo->kSprache = (int)$language->kSprache;
                    $db->insert('tseo', $oSeo);
                    $attrLang                   = new stdClass();
                    $attrLang->kMerkmalWert     = (int)$attributeValue->kMerkmalWert;
                    $attrLang->kSprache         = (int)$language->kSprache;
                    $attrLang->cWert            = $attributeValue->cNameSTD ?? '';
                    $attrLang->cSeo             = $oSeo->cSeo ?? '';
                    $attrLang->cMetaTitle       = $attributeValue->cMetaTitleSTD ?? '';
                    $attrLang->cMetaKeywords    = $attributeValue->cMetaKeywordsSTD ?? '';
                    $attrLang->cMetaDescription = $attributeValue->cMetaDescriptionSTD ?? '';
                    $attrLang->cBeschreibung    = $attributeValue->cBeschreibungSTD ?? '';
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
    foreach ($db->selectAll('tmerkmalwert', 'kMerkmal', $kMerkmal, 'kMerkmalWert') as $wert) {
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
    $count = Shop::Container()->getDB()->query(
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
    if (!$isInsert && (int)$count->nAnzahl === 1) {
        loescheMerkmal($count->kMerkmal);
    }
}

/**
 * @param int $kMerkmal
 * @return stdClass
 */
function merkeBildPfad(int $kMerkmal)
{
    $attribute                   = new stdClass();
    $attribute->oMerkmalWert_arr = [];
    if ($kMerkmal > 0) {
        $oMerkmalTMP = Shop::Container()->getDB()->select('tmerkmal', 'kMerkmal', $kMerkmal);
        if (isset($oMerkmalTMP->kMerkmal) && $oMerkmalTMP->kMerkmal > 0) {
            $attribute->kMerkmal  = $oMerkmalTMP->kMerkmal;
            $attribute->cBildpfad = $oMerkmalTMP->cBildpfad;
        }
        $attributeValues = Shop::Container()->getDB()->selectAll(
            'tmerkmalwert',
            'kMerkmal',
            $kMerkmal,
            'kMerkmalWert, cBildpfad'
        );
        foreach ($attributeValues as $oMerkmalWert) {
            $attribute->oMerkmalWert_arr[$oMerkmalWert->kMerkmalWert] = $oMerkmalWert->cBildpfad;
        }
    }

    return $attribute;
}
