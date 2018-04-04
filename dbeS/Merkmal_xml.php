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
        if (Jtllog::doLog()) {
            Jtllog::writeLog('Error: Cannot extract zip file.', JTLLOG_LEVEL_ERROR, false, 'Merkmal_xml');
        }
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            $d        = file_get_contents($xmlFile);
            $xml      = XML_unserialize($d);
            $fileName = pathinfo($xmlFile)['basename'];

            if ($fileName === 'del_merkmal.xml' || $fileName === 'del_merkmalwert.xml') {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog(
                        'bearbeite: ' . $xmlFile . ' size: ' . filesize($xmlFile),
                        JTLLOG_LEVEL_DEBUG,
                        false,
                        'Merkmal_xml'
                    );
                }
                bearbeiteDeletes($xml);
            } elseif ($fileName === 'merkmal.xml') {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog(
                        'bearbeite: ' . $xmlFile . ' size: ' . filesize($xmlFile),
                        JTLLOG_LEVEL_DEBUG,
                        false,
                        'Merkmal_xml'
                    );
                }
                bearbeiteInsert($xml);
            }

            removeTemporaryFiles($xmlFile);
        }
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
    }
}

echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Merkmal_xml');
}

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
}

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    if (isset($xml['merkmale']['tmerkmal']) && is_array($xml['merkmale']['tmerkmal'])) {
        // Standardsprache rausholen
        $oSprachSTD = gibStandardsprache();
        $oMM_arr    = []; // Merkt sich alle MerkmalWerte die von der Wawi geschickt werden

        //Merkmal
        $merkmal_arr = mapArray($xml['merkmale'], 'tmerkmal', $GLOBALS['mMerkmal']);
        $mmCount     = count($merkmal_arr);
        for ($i = 0; $i < $mmCount; $i++) {
            if (!isset($oMM_arr[$i]) || $oMM_arr[$i] === null) {
                $oMM_arr[$i] = new stdClass();
            }
            if (isset($merkmal_arr[$i]->nMehrfachauswahl)) {
                if ($merkmal_arr[$i]->nMehrfachauswahl > 1) {
                    $merkmal_arr[$i]->nMehrfachauswahl = 1;
                }
            } else {
                $merkmal_arr[$i]->nMehrfachauswahl = 0;
            }
            $oMerkmal                   = merkeBildPfad($merkmal_arr[$i]->kMerkmal);
            $merkmal_arr[$i]->cBildpfad = $oMerkmal->cBildpfad ?? '';
            $oMM_arr[$i]->oMMW_arr      = [];

            if ($mmCount < 2) {
                $MerkmalWert_arr = mapArray($xml['merkmale']['tmerkmal'], 'tmerkmalwert', $GLOBALS['mMerkmalWert']);
                //lösche Merkmal --> Update = Delete + Insert
                if (is_array($MerkmalWert_arr) && count($MerkmalWert_arr) > 0) {
                    loescheMerkmal($xml['merkmale']['tmerkmal attr']['kMerkmal'], 0);
                } else {
                    loescheNurMerkmal($xml['merkmale']['tmerkmal attr']['kMerkmal']);
                }
                //MerkmalSprache aktualisieren in DB
                updateXMLinDB($xml['merkmale']['tmerkmal'], 'tmerkmalsprache', $GLOBALS['mMerkmalSprache'], 'kMerkmal', 'kSprache');

                if (is_array($MerkmalWert_arr) && count($MerkmalWert_arr) > 0) {
                    $mmwCountO = count($MerkmalWert_arr);
                    for ($o = 0; $o < $mmwCountO; $o++) {
                        $oMM_arr[$i]->oMMW_arr[$o]->kMerkmalWert = $MerkmalWert_arr[$o]->kMerkmalWert;
                        $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr = [];

                        if (count($MerkmalWert_arr) < 2) {
                            $MerkmalWertSprache_arr = mapArray(
                                $xml['merkmale']['tmerkmal']['tmerkmalwert'],
                                'tmerkmalwertsprache',
                                $GLOBALS['mMerkmalWertSprache']
                            );
                            $mmwsCount              = count($MerkmalWertSprache_arr);
                            for ($j = 0; $j < $mmwsCount; ++$j) {
                                Shop::Container()->getDB()->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [(int)$MerkmalWertSprache_arr[$j]->kMerkmalWert, 'kMerkmalWert', (int)$MerkmalWertSprache_arr[$j]->kSprache]
                                );
                                if (trim($MerkmalWertSprache_arr[$j]->cSeo)) {
                                    $cSeo = getFlatSeoPath($MerkmalWertSprache_arr[$j]->cSeo);
                                } else {
                                    $cSeo = getFlatSeoPath($MerkmalWertSprache_arr[$j]->cWert);
                                }
                                $MerkmalWertSprache_arr[$j]->cSeo = getSeo($cSeo);
                                $MerkmalWertSprache_arr[$j]->cSeo = checkSeo($MerkmalWertSprache_arr[$j]->cSeo);
                                DBUpdateInsert('tmerkmalwertsprache', [$MerkmalWertSprache_arr[$j]], 'kMerkmalWert', 'kSprache');
                                //insert in tseo
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = $MerkmalWertSprache_arr[$j]->cSeo;
                                $oSeo->cKey     = 'kMerkmalWert';
                                $oSeo->kKey     = $MerkmalWertSprache_arr[$j]->kMerkmalWert;
                                $oSeo->kSprache = $MerkmalWertSprache_arr[$j]->kSprache;
                                Shop::Container()->getDB()->insert('tseo', $oSeo);

                                if (!in_array($MerkmalWertSprache_arr[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $MerkmalWertSprache_arr[$j]->kSprache;
                                }

                                if ($MerkmalWertSprache_arr[$j]->kSprache == $oSprachSTD->kSprache) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $MerkmalWertSprache_arr[$j]->cWert;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $MerkmalWertSprache_arr[$j]->cSeo;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $MerkmalWertSprache_arr[$j]->cMetaTitle;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $MerkmalWertSprache_arr[$j]->cMetaKeywords;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $MerkmalWertSprache_arr[$j]->cMetaDescription;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $MerkmalWertSprache_arr[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $MerkmalWert_arr[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$MerkmalWert_arr[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$MerkmalWert_arr[$o]], 'kMerkmalWert');
                        } else {
                            $MerkmalWertSprache_arr = mapArray($xml['merkmale']['tmerkmal']['tmerkmalwert'][$o], 'tmerkmalwertsprache', $GLOBALS['mMerkmalWertSprache']);
                            $mmwsaCount             = count($MerkmalWertSprache_arr);
                            for ($j = 0; $j < $mmwsaCount; $j++) {
                                Shop::Container()->getDB()->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [(int)$MerkmalWertSprache_arr[$j]->kMerkmalWert, 'kMerkmalWert', (int)$MerkmalWertSprache_arr[$j]->kSprache]
                                );
                                if (trim($MerkmalWertSprache_arr[$j]->cSeo)) {
                                    $cSeo = getFlatSeoPath($MerkmalWertSprache_arr[$j]->cSeo);
                                } else {
                                    $cSeo = getFlatSeoPath($MerkmalWertSprache_arr[$j]->cWert);
                                }
                                $MerkmalWertSprache_arr[$j]->cSeo = getSeo($cSeo);
                                $MerkmalWertSprache_arr[$j]->cSeo = checkSeo($MerkmalWertSprache_arr[$j]->cSeo);
                                DBUpdateInsert('tmerkmalwertsprache', [$MerkmalWertSprache_arr[$j]], 'kMerkmalWert', 'kSprache');

                                //insert in tseo
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = $MerkmalWertSprache_arr[$j]->cSeo;
                                $oSeo->cKey     = 'kMerkmalWert';
                                $oSeo->kKey     = (int)$MerkmalWertSprache_arr[$j]->kMerkmalWert;
                                $oSeo->kSprache = (int)$MerkmalWertSprache_arr[$j]->kSprache;
                                Shop::Container()->getDB()->insert('tseo', $oSeo);

                                if (!in_array($MerkmalWertSprache_arr[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $MerkmalWertSprache_arr[$j]->kSprache;
                                }

                                if ($MerkmalWertSprache_arr[$j]->kSprache == $oSprachSTD->kSprache) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $MerkmalWertSprache_arr[$j]->cWert;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $MerkmalWertSprache_arr[$j]->cSeo;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $MerkmalWertSprache_arr[$j]->cMetaTitle;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $MerkmalWertSprache_arr[$j]->cMetaKeywords;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $MerkmalWertSprache_arr[$j]->cMetaDescription;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $MerkmalWertSprache_arr[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $MerkmalWert_arr[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$MerkmalWert_arr[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$MerkmalWert_arr[$o]], 'kMerkmalWert');
                        }
                    }
                }
            } else {
                $MerkmalWert_arr = mapArray($xml['merkmale']['tmerkmal'][$i], 'tmerkmalwert', $GLOBALS['mMerkmalWert']);

                if (is_array($MerkmalWert_arr) && count($MerkmalWert_arr) > 0) {
                    loescheMerkmal($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal'], 0);
                } else {
                    loescheNurMerkmal($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal']);
                }

                updateXMLinDB($xml['merkmale']['tmerkmal'][$i], 'tmerkmalsprache', $GLOBALS['mMerkmalSprache'], 'kMerkmal', 'kSprache');
                $mmwCount = count($MerkmalWert_arr);
                if (is_array($MerkmalWert_arr) && $mmwCount > 0) {
                    for ($o = 0; $o < $mmwCount; $o++) {
                        $oMM_arr[$i]->oMMW_arr[$o]->kMerkmalWert = $MerkmalWert_arr[$o]->kMerkmalWert;
                        $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr = [];

                        if (count($MerkmalWert_arr) < 2) {
                            $MerkmalWertSprache_arr = mapArray($xml['merkmale']['tmerkmal'][$i]['tmerkmalwert'], 'tmerkmalwertsprache', $GLOBALS['mMerkmalWertSprache']);
                            $cnt = count($MerkmalWertSprache_arr);
                            for ($j = 0; $j < $cnt; $j++) {
                                Shop::Container()->getDB()->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [(int)$MerkmalWertSprache_arr[$j]->kMerkmalWert, 'kMerkmalWert', (int)$MerkmalWertSprache_arr[$j]->kSprache]
                                );
                                $cSeo = trim($MerkmalWertSprache_arr[$j]->cSeo)
                                    ? getFlatSeoPath($MerkmalWertSprache_arr[$j]->cSeo)
                                    : getFlatSeoPath($MerkmalWertSprache_arr[$j]->cWert);

                                $MerkmalWertSprache_arr[$j]->cSeo = getSeo($cSeo);
                                $MerkmalWertSprache_arr[$j]->cSeo = checkSeo($MerkmalWertSprache_arr[$j]->cSeo);
                                DBUpdateInsert('tmerkmalwertsprache', [$MerkmalWertSprache_arr[$j]], 'kMerkmalWert', 'kSprache');
                                //insert in tseo
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = $MerkmalWertSprache_arr[$j]->cSeo;
                                $oSeo->cKey     = 'kMerkmalWert';
                                $oSeo->kKey     = (int)$MerkmalWertSprache_arr[$j]->kMerkmalWert;
                                $oSeo->kSprache = (int)$MerkmalWertSprache_arr[$j]->kSprache;
                                Shop::Container()->getDB()->insert('tseo', $oSeo);

                                if (!in_array($MerkmalWertSprache_arr[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $MerkmalWertSprache_arr[$j]->kSprache;
                                }

                                if ($MerkmalWertSprache_arr[$j]->kSprache == $oSprachSTD->kSprache) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $MerkmalWertSprache_arr[$j]->cWert;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $MerkmalWertSprache_arr[$j]->cSeo;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $MerkmalWertSprache_arr[$j]->cMetaTitle;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $MerkmalWertSprache_arr[$j]->cMetaKeywords;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $MerkmalWertSprache_arr[$j]->cMetaDescription;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $MerkmalWertSprache_arr[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $MerkmalWert_arr[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$MerkmalWert_arr[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$MerkmalWert_arr[$o]], 'kMerkmalWert');
                        } else {
                            $MerkmalWertSprache_arr = mapArray(
                                $xml['merkmale']['tmerkmal'][$i]['tmerkmalwert'][$o],
                                'tmerkmalwertsprache',
                                $GLOBALS['mMerkmalWertSprache']
                            );
                            $mmwsaCount             = count($MerkmalWertSprache_arr);
                            for ($j = 0; $j < $mmwsaCount; ++$j) {
                                Shop::Container()->getDB()->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [(int)$MerkmalWertSprache_arr[$j]->kMerkmalWert, 'kMerkmalWert', (int)$MerkmalWertSprache_arr[$j]->kSprache]
                                );
                                if (trim($MerkmalWertSprache_arr[$j]->cSeo)) {
                                    $cSeo = getFlatSeoPath($MerkmalWertSprache_arr[$j]->cSeo);
                                } else {
                                    $cSeo = getFlatSeoPath($MerkmalWertSprache_arr[$j]->cWert);
                                }

                                $MerkmalWertSprache_arr[$j]->cSeo = getSeo($cSeo);
                                $MerkmalWertSprache_arr[$j]->cSeo = checkSeo($MerkmalWertSprache_arr[$j]->cSeo);
                                DBUpdateInsert('tmerkmalwertsprache', [$MerkmalWertSprache_arr[$j]], 'kMerkmalWert', 'kSprache');

                                //insert in tseo
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = $MerkmalWertSprache_arr[$j]->cSeo;
                                $oSeo->cKey     = 'kMerkmalWert';
                                $oSeo->kKey     = $MerkmalWertSprache_arr[$j]->kMerkmalWert;
                                $oSeo->kSprache = $MerkmalWertSprache_arr[$j]->kSprache;
                                Shop::Container()->getDB()->insert('tseo', $oSeo);

                                if (!in_array($MerkmalWertSprache_arr[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $MerkmalWertSprache_arr[$j]->kSprache;
                                }

                                if ($MerkmalWertSprache_arr[$j]->kSprache == $oSprachSTD->kSprache) {
                                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $MerkmalWertSprache_arr[$j]->cWert;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $MerkmalWertSprache_arr[$j]->cSeo;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $MerkmalWertSprache_arr[$j]->cMetaTitle;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $MerkmalWertSprache_arr[$j]->cMetaKeywords;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $MerkmalWertSprache_arr[$j]->cMetaDescription;
                                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $MerkmalWertSprache_arr[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $MerkmalWert_arr[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$MerkmalWert_arr[$o]->kMerkmalWert];
                            DBUpdateInsert('tmerkmalwert', [$MerkmalWert_arr[$o]], 'kMerkmalWert');
                        }
                    }
                }
            }
        }
        DBUpdateInsert('tmerkmal', $merkmal_arr, 'kMerkmal');
        fuelleFehlendeMMWInSeo($oMM_arr); // tseo prüfen und falls Seo einer Sprache leer => nachfüllen
    }

    // Kommen nur MerkmalWerte?
    if (isset($xml['merkmale']['tmerkmalwert']) && is_array($xml['merkmale']['tmerkmalwert'])) {
        $MerkmalWert_arr = mapArray($xml['merkmale'], 'tmerkmalwert', $GLOBALS['mMerkmalWert']);
        $i               = 0;

        if (!isset($oMM_arr[$i]) || $oMM_arr[$i] === null) {
            $oMM_arr[$i] = new stdClass();
        }

        $oMM_arr[$i]->oMMW_arr = [];
        $mmwCount              = count($MerkmalWert_arr);
        for ($o = 0; $o < $mmwCount; $o++) {
            loescheMerkmalWert($MerkmalWert_arr[$o]->kMerkmalWert, true);
            $oMM_arr[$i]->oMMW_arr[$o]               = new stdClass();
            $oMM_arr[$i]->oMMW_arr[$o]->kMerkmalWert = $MerkmalWert_arr[$o]->kMerkmalWert;
            $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr = [];

            if (count($MerkmalWert_arr) < 2) {
                $MerkmalWertSprache_arr = mapArray($xml['merkmale']['tmerkmalwert'], 'tmerkmalwertsprache', $GLOBALS['mMerkmalWertSprache']);
            } else {
                $MerkmalWertSprache_arr = mapArray($xml['merkmale']['tmerkmalwert'][$o], 'tmerkmalwertsprache', $GLOBALS['mMerkmalWertSprache']);
            }
            $mmwsaCount = count($MerkmalWertSprache_arr);
            for ($j = 0; $j < $mmwsaCount; $j++) {
                Shop::Container()->getDB()->delete(
                    'tseo',
                    ['kKey', 'cKey', 'kSprache'],
                    [(int)$MerkmalWertSprache_arr[$j]->kMerkmalWert, 'kMerkmalWert', (int)$MerkmalWertSprache_arr[$j]->kSprache]
                );
                if (trim($MerkmalWertSprache_arr[$j]->cSeo)) {
                    $cSeo = getFlatSeoPath($MerkmalWertSprache_arr[$j]->cSeo);
                } else {
                    $cSeo = getFlatSeoPath($MerkmalWertSprache_arr[$j]->cWert);
                }

                $MerkmalWertSprache_arr[$j]->cSeo = getSeo($cSeo);
                $MerkmalWertSprache_arr[$j]->cSeo = checkSeo($MerkmalWertSprache_arr[$j]->cSeo);
                DBUpdateInsert('tmerkmalwertsprache', [$MerkmalWertSprache_arr[$j]], 'kMerkmalWert', 'kSprache');
                //insert in tseo
                $oSeo           = new stdClass();
                $oSeo->cSeo     = $MerkmalWertSprache_arr[$j]->cSeo;
                $oSeo->cKey     = 'kMerkmalWert';
                $oSeo->kKey     = $MerkmalWertSprache_arr[$j]->kMerkmalWert;
                $oSeo->kSprache = $MerkmalWertSprache_arr[$j]->kSprache;
                Shop::Container()->getDB()->insert('tseo', $oSeo);

                if (!in_array($MerkmalWertSprache_arr[$j]->kSprache, $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr)) {
                    $oMM_arr[$i]->oMMW_arr[$o]->kSprache_arr[] = $MerkmalWertSprache_arr[$j]->kSprache;
                }

                if (isset($MerkmalWertSprache_arr[$j]->kSprache, $oSprachSTD->kSprache) &&
                    $MerkmalWertSprache_arr[$j]->kSprache == $oSprachSTD->kSprache
                ) {
                    $oMM_arr[$i]->oMMW_arr[$o]->cNameSTD            = $MerkmalWertSprache_arr[$j]->cWert;
                    $oMM_arr[$i]->oMMW_arr[$o]->cSeoSTD             = $MerkmalWertSprache_arr[$j]->cSeo;
                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaTitleSTD       = $MerkmalWertSprache_arr[$j]->cMetaTitle;
                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaKeywordsSTD    = $MerkmalWertSprache_arr[$j]->cMetaKeywords;
                    $oMM_arr[$i]->oMMW_arr[$o]->cMetaDescriptionSTD = $MerkmalWertSprache_arr[$j]->cMetaDescription;
                    $oMM_arr[$i]->oMMW_arr[$o]->cBeschreibungSTD    = $MerkmalWertSprache_arr[$j]->cBeschreibung;
                }
            }
            //alten Bildpfad nehmen
            // tmerkmalwertbild
            $kMerkmalWert     = $MerkmalWert_arr[$o]->kMerkmalWert;
            $oMerkmalWertBild = Shop::Container()->getDB()->select('tmerkmalwertbild', 'kMerkmalWert', (int)$kMerkmalWert);

            $MerkmalWert_arr[$o]->cBildpfad = $oMerkmalWertBild->cBildpfad ?? '';
            DBUpdateInsert('tmerkmalwert', [$MerkmalWert_arr[$o]], 'kMerkmalWert');
        }
        fuelleFehlendeMMWInSeo($oMM_arr); // tseo prüfen und falls Seo einer Sprache leer => nachfüllen
    }
}

/**
 * Geht $oMMW_arr durch welches vorher mit den mitgeschickten Merkmalwerten gefüllt wurde
 * und füllt die Seo Tabelle in den Sprachen, die nicht von der Wawi mitgeschickt wurden
 *
 * @param array $oMM_arr
 */
function fuelleFehlendeMMWInSeo($oMM_arr)
{
    // Hole alle Sprachen vom Shop
    $oSprache_arr = Shop::Container()->getDB()->query("SELECT kSprache FROM tsprache ORDER BY kSprache", 2);

    if (is_array($oMM_arr) && count($oMM_arr) > 0) {
        foreach ($oMM_arr as $oMM) {
            foreach ($oMM->oMMW_arr as $oMMW) {
                foreach ($oSprache_arr as $oSprache) { // Laufe alle Sprachen vom Shop durch
                    $bVorhanden = false;
                    foreach ($oMMW->kSprache_arr as $kSprache) {
                        // Laufe alle gefüllten Sprachen durch
                        if ($kSprache == $oSprache->kSprache) {
                            $bVorhanden = true;
                            break;
                        }
                    }
                    if (!$bVorhanden) {
                        // Sprache vom Shop wurde nicht von der Wawi mitgeschickt und muss somit in tseo nachgefüllt werden
                        $cSeo = isset($oMMW->cNameSTD) ? getSeo($oMMW->cNameSTD) : '';
                        $cSeo = checkSeo($cSeo);
                        // delete in tseo
                        Shop::Container()->getDB()->query(
                            "DELETE tmerkmalwertsprache, tseo FROM tmerkmalwertsprache
                                LEFT JOIN tseo
                                    ON tseo.cKey = 'kMerkmalWert'
                                        AND tseo.kKey = " . (int)$oMMW->kMerkmalWert . "
                                        AND tseo.kSprache = " . (int)$oSprache->kSprache . "
                                WHERE tmerkmalwertsprache.kMerkmalWert = " . (int)$oMMW->kMerkmalWert . "
                                    AND tmerkmalwertsprache.kSprache = " . (int)$oSprache->kSprache, 4
                        );
                        //insert in tseo
                        //@todo: 1062: Duplicate entry '' for key 'PRIMARY'
                        if ($cSeo !== '' && $cSeo !== null) {
                            $oSeo           = new stdClass();
                            $oSeo->cSeo     = $cSeo;
                            $oSeo->cKey     = 'kMerkmalWert';
                            $oSeo->kKey     = (int)$oMMW->kMerkmalWert;
                            $oSeo->kSprache = (int)$oSprache->kSprache;
                            Shop::Container()->getDB()->insert('tseo', $oSeo);

                            // Insert in tmerkmalwertsprache
                            $oMerkmalWertSprache                   = new stdClass();
                            $oMerkmalWertSprache->kMerkmalWert     = (int)$oMMW->kMerkmalWert;
                            $oMerkmalWertSprache->kSprache         = (int)$oSprache->kSprache;
                            $oMerkmalWertSprache->cWert            = $oMMW->cNameSTD ?? '';
                            $oMerkmalWertSprache->cSeo             = $oSeo->cSeo ?? '';
                            $oMerkmalWertSprache->cMetaTitle       = $oMMW->cMetaTitleSTD ?? '';
                            $oMerkmalWertSprache->cMetaKeywords    = $oMMW->cMetaKeywordsSTD ?? '';
                            $oMerkmalWertSprache->cMetaDescription = $oMMW->cMetaDescriptionSTD ?? '';
                            $oMerkmalWertSprache->cBeschreibung    = $oMMW->cBeschreibungSTD ?? '';
                            Shop::Container()->getDB()->insert('tmerkmalwertsprache', $oMerkmalWertSprache);
                        }
                    }
                }
            }
        }
    }
}

/**
 * @param int $kMerkmal
 * @param int $update
 */
function loescheMerkmal($kMerkmal, $update = 1)
{
    $kMerkmal = (int)$kMerkmal;
    if ($kMerkmal > 0) {
        Shop::Container()->getDB()->query(
            "DELETE tseo
                FROM tseo
                INNER JOIN tmerkmalwert
                    ON tmerkmalwert.kMerkmalWert = tseo.kKey
                INNER JOIN tmerkmal
                    ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                WHERE tseo.cKey = 'kMerkmalWert'
                    AND tmerkmal.kMerkmal = " . $kMerkmal, 4
        );

        if ($update) {
            Shop::Container()->getDB()->delete('tartikelmerkmal', 'kMerkmal', $kMerkmal);
        }
        Shop::Container()->getDB()->delete('tmerkmal', 'kMerkmal', $kMerkmal);
        Shop::Container()->getDB()->delete('tmerkmalsprache', 'kMerkmal', $kMerkmal);
        $werte_arr = Shop::Container()->getDB()->selectAll('tmerkmalwert', 'kMerkmal', $kMerkmal, 'kMerkmalWert');
        if (is_array($werte_arr)) {
            foreach ($werte_arr as $wert) {
                Shop::Container()->getDB()->delete('tmerkmalwertsprache', 'kMerkmalWert', (int)$wert->kMerkmalWert);
                Shop::Container()->getDB()->delete('tmerkmalwertbild', 'kMerkmalWert', (int)$wert->kMerkmalWert);
            }
        }
        Shop::Container()->getDB()->delete('tmerkmalwert', 'kMerkmal', $kMerkmal);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Merkmal geloescht: ' . $kMerkmal, JTLLOG_LEVEL_DEBUG, false, 'Merkmal_xml');
        }
    }
}

/**
 * @param int $kMerkmal
 */
function loescheNurMerkmal($kMerkmal)
{
    $kMerkmal = (int)$kMerkmal;
    if ($kMerkmal > 0) {
        Shop::Container()->getDB()->query(
            "DELETE tseo
                FROM tseo
                INNER JOIN tmerkmalwert
                    ON tmerkmalwert.kMerkmalWert = tseo.kKey
                INNER JOIN tmerkmal
                    ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                WHERE tseo.cKey = 'kMerkmalWert'
                    AND tmerkmal.kMerkmal = " . $kMerkmal, 4
        );

        Shop::Container()->getDB()->delete('tmerkmal', 'kMerkmal', $kMerkmal);
        Shop::Container()->getDB()->delete('tmerkmalsprache', 'kMerkmal', $kMerkmal);
    }
}

/**
 * WIRD ZURZEIT NOCH NICHT GENUTZT WEGEN MOEGLICHER INKONSISTENZ
 *
 * @param int  $kMerkmalWert
 * @param bool $isInsert
 */
function loescheMerkmalWert($kMerkmalWert, $isInsert = false)
{
    $kMerkmalWert = (int)$kMerkmalWert;
    if ($kMerkmalWert > 0) {
        Shop::Container()->getDB()->delete('tseo', ['cKey', 'kKey'], ['kMerkmalWert', $kMerkmalWert]);
        // Hat das Merkmal vor dem Loeschen noch mehr als einen Wert?
        // Wenn nein => nach dem Loeschen auch das Merkmal loeschen
        $oAnzahl = Shop::Container()->getDB()->query(
            "SELECT count(*) AS nAnzahl, kMerkmal
                FROM tmerkmalwert
                WHERE kMerkmal =
                    (
                        SELECT kMerkmal
                        FROM tmerkmalwert
                        WHERE kMerkmalWert = " . $kMerkmalWert . "
                    )", 1
        );

        Shop::Container()->getDB()->query(
            "DELETE tmerkmalwert, tmerkmalwertsprache
                FROM tmerkmalwert
                JOIN tmerkmalwertsprache
                    ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                WHERE tmerkmalwert.kMerkmalWert = " . $kMerkmalWert, 3
        );
        // Das Merkmal hat keine MerkmalWerte mehr => auch loeschen
        if (!$isInsert && $oAnzahl->nAnzahl == 1) {
            loescheMerkmal($oAnzahl->kMerkmal);
        }
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('MerkmalWert geloescht: ' . $kMerkmalWert, JTLLOG_LEVEL_DEBUG, false, 'Merkmal_xml');
        }
    }
}

/**
 * @param int $kMerkmal
 * @return stdClass
 */
function merkeBildPfad($kMerkmal)
{
    $kMerkmal                   = (int)$kMerkmal;
    $oMerkmal                   = new stdClass();
    $oMerkmal->oMerkmalWert_arr = [];
    if ($kMerkmal > 0) {
        $oMerkmalTMP = Shop::Container()->getDB()->select('tmerkmal', 'kMerkmal', $kMerkmal);
        if (isset($oMerkmalTMP->kMerkmal) && $oMerkmalTMP->kMerkmal > 0) {
            $oMerkmal->kMerkmal  = $oMerkmalTMP->kMerkmal;
            $oMerkmal->cBildpfad = $oMerkmalTMP->cBildpfad;
        }
        $oMerkmalWert_arr = Shop::Container()->getDB()->selectAll('tmerkmalwert', 'kMerkmal', $kMerkmal, 'kMerkmalWert, cBildpfad');
        if (count($oMerkmalWert_arr) > 0) {
            foreach ($oMerkmalWert_arr as $oMerkmalWert) {
                $oMerkmal->oMerkmalWert_arr[$oMerkmalWert->kMerkmalWert] = $oMerkmalWert->cBildpfad;
            }
        }
    }

    return $oMerkmal;
}
