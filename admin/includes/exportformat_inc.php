<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @deprecated since 4.05
 * @param array $splits
 * @param int   $fileCounter
 * @return string
 */
function gibDateiname($splits, $fileCounter)
{
    if (is_array($splits) && count($splits) > 1) {
        return $splits[0] . $fileCounter . $splits[1];
    }

    return $splits[0] . $fileCounter;
}

/**
 * @deprecated since 4.05
 * @param array $splits
 * @param int   $fileCounter
 * @return string
 */
function gibDateiPfad($splits, $fileCounter)
{
    return PFAD_ROOT . PFAD_EXPORT . gibDateiname($splits, $fileCounter);
}

/**
 * @deprecated since 4.05
 * @return array
 */
function pruefeExportformat()
{
    $checks = [];
    // Name
    if (!isset($_POST['cName']) || mb_strlen($_POST['cName']) === 0) {
        $checks['cName'] = 1;
    }
    // Dateiname
    if (!isset($_POST['cDateiname']) || mb_strlen($_POST['cDateiname']) === 0) {
        $checks['cDateiname'] = 1;
    }
    // Dateiname Endung fehlt
    if (mb_strpos($_POST['cDateiname'], '.') === false) {
        $checks['cDateiname'] = 2;
    }
    // Content
    if (!isset($_POST['cContent']) || mb_strlen($_POST['cContent']) === 0) {
        $checks['cContent'] = 1;
    }
    // Sprache
    if (!isset($_POST['kSprache']) || (int)$_POST['kSprache'] === 0) {
        $checks['kSprache'] = 1;
    }
    // Sprache
    if (!isset($_POST['kWaehrung']) || (int)$_POST['kWaehrung'] === 0) {
        $checks['kWaehrung'] = 1;
    }
    // Kundengruppe
    if (!isset($_POST['kKundengruppe']) || (int)$_POST['kKundengruppe'] === 0) {
        $checks['kKundengruppe'] = 1;
    }

    return $checks;
}

/**
 * Falls eingestellt, wird die Exportdatei in mehrere Dateien gesplittet
 *
 * @deprecated since 4.05
 * @param object $export
 */
function splitteExportDatei($export)
{
    if (isset($export->nSplitgroesse)
        && (int)$export->nSplitgroesse > 0
        && file_exists(PFAD_ROOT . PFAD_EXPORT . $export->cDateiname)
    ) {
        $fileCounter = 1;
        $splits      = [];
        $extIndex    = mb_strrpos($export->cDateiname, '.');
        // Dateiname splitten nach Name + Typ
        if ($extIndex === false) {
            $splits[0] = $export->cDateiname;
        } else {
            $splits[0] = mb_substr($export->cDateiname, 0, $extIndex);
            $splits[1] = mb_substr($export->cDateiname, $extIndex);
        }
        // Ist die angelegte Datei größer als die Einstellung im Exportformat?
        clearstatcache();
        if (filesize(PFAD_ROOT . PFAD_EXPORT . $export->cDateiname) >=
            ($export->nSplitgroesse * 1024 * 1024 - 102400)) {
            sleep(2);
            loescheExportDateien($export->cDateiname, $splits[0]);
            $handle    = fopen(PFAD_ROOT . PFAD_EXPORT . $export->cDateiname, 'r');
            $row       = 1;
            $newHandle = fopen(gibDateiPfad($splits, $fileCounter), 'w');
            $fileSize  = 0;
            while (($cContent = fgets($handle)) !== false) {
                if ($row > 1) {
                    $rowSize = mb_strlen($cContent) + 2;
                    //Schwelle erreicht?
                    if ($fileSize <= ($export->nSplitgroesse * 1024 * 1024 - 102400)) {
                        // Schreibe Content
                        fwrite($newHandle, $cContent);
                        $fileSize += $rowSize;
                    } else {
                        //neue Datei
                        schreibeFusszeile($newHandle, $export->cFusszeile, $export->cKodierung);
                        fclose($newHandle);
                        $fileCounter++;
                        $newHandle = fopen(gibDateiPfad($splits, $fileCounter), 'w');
                        schreibeKopfzeile($newHandle, $export->cKopfzeile, $export->cKodierung);
                        // Schreibe Content
                        fwrite($newHandle, $cContent);
                        $fileSize = $rowSize;
                    }
                } elseif ($row === 1) {
                    schreibeKopfzeile($newHandle, $export->cKopfzeile, $export->cKodierung);
                }
                $row++;
            }
            fclose($newHandle);
            fclose($handle);
            unlink(PFAD_ROOT . PFAD_EXPORT . $export->cDateiname);
        }
    }
}

/**
 * @deprecated since 4.05
 * @param resource $dateiHandle
 * @param string   $cKopfzeile
 * @param string   $cKodierung
 */
function schreibeKopfzeile($dateiHandle, $cKopfzeile, $cKodierung)
{
    if ($cKopfzeile) {
        if ($cKodierung === 'UTF-8' || $cKodierung === 'UTF-8noBOM') {
            if ($cKodierung === 'UTF-8') {
                fwrite($dateiHandle, "\xEF\xBB\xBF");
            }
            fwrite($dateiHandle, $cKopfzeile . "\n");
        } else {
            fwrite($dateiHandle, StringHandler::convertISO($cKopfzeile . "\n"));
        }
    }
}

/**
 * @deprecated since 4.05
 * @param resource $dateiHandle
 * @param string   $cFusszeile
 * @param string   $cKodierung
 */
function schreibeFusszeile($dateiHandle, $cFusszeile, $cKodierung)
{
    if (mb_strlen($cFusszeile) > 0) {
        if ($cKodierung === 'UTF-8' || $cKodierung === 'UTF-8noBOM') {
            fwrite($dateiHandle, $cFusszeile);
        } else {
            fwrite($dateiHandle, StringHandler::convertISO($cFusszeile));
        }
    }
}

/**
 * @deprecated since 4.05
 * @param string $cDateiname
 * @param string $cDateinameSplit
 */
function loescheExportDateien($cDateiname, $cDateinameSplit)
{
    if (is_dir(PFAD_ROOT . PFAD_EXPORT)) {
        $dir = opendir(PFAD_ROOT . PFAD_EXPORT);
        if ($dir !== false) {
            while (($cDatei = readdir($dir)) !== false) {
                if ($cDatei !== $cDateiname && mb_strpos($cDatei, $cDateinameSplit) !== false) {
                    @unlink(PFAD_ROOT . PFAD_EXPORT . $cDatei);
                }
            }
            closedir($dir);
        }
    }
}

/**
 * @param int $kExportformat
 * @return array
 */
function getEinstellungenExport($kExportformat)
{
    $kExportformat = (int)$kExportformat;
    $ret           = [];
    if ($kExportformat > 0) {
        $conf = Shop::Container()->getDB()->selectAll(
            'texportformateinstellungen',
            'kExportformat',
            $kExportformat,
            'cName, cWert'
        );
        foreach ($conf as $eins) {
            if ($eins->cName) {
                $ret[$eins->cName] = $eins->cWert;
            }
        }
    }

    return $ret;
}

/**
 * @deprecated since 4.05
 * @param object $export
 * @return array
 */
function baueArtikelExportSQL(&$export)
{
    $sql          = [];
    $sql['Where'] = '';
    $sql['Join']  = '';

    if (empty($export->kExportformat)) {
        return $sql;
    }
    $conf = getEinstellungenExport($export->kExportformat);

    switch ($export->nVarKombiOption) {
        case 2:
            $sql['Where'] = ' AND kVaterArtikel = 0';
            break;
        case 3:
            $sql['Where'] = ' AND (tartikel.nIstVater != 1 OR tartikel.kEigenschaftKombi > 0)';
            break;
    }
    if (isset($conf['exportformate_lager_ueber_null']) && $conf['exportformate_lager_ueber_null'] === 'Y') {
        $sql['Where'] .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y'))";
    } elseif (isset($conf['exportformate_lager_ueber_null']) && $conf['exportformate_lager_ueber_null'] === 'O') {
        $sql['Where'] .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                                    OR tartikel.cLagerKleinerNull = 'Y')";
    }

    if (isset($conf['exportformate_preis_ueber_null']) && $conf['exportformate_preis_ueber_null'] === 'Y') {
        $sql['Join'] .= ' JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                                AND tpreise.kKundengruppe = ' . (int)$export->kKundengruppe . '
                                AND tpreise.fVKNetto > 0';
    }

    if (isset($conf['exportformate_beschreibung']) && $conf['exportformate_beschreibung'] === 'Y'
    ) {
        $sql['Where'] .= " AND tartikel.cBeschreibung != ''";
    }

    return $sql;
}

/**
 * @deprecated since 4.05
 * @param object $export
 * @return mixed
 */
function holeMaxExportArtikelAnzahl(&$export)
{
    $data = baueArtikelExportSQL($export);
    $conf = Shop::getSettings([CONF_GLOBAL]);
    $sql  = 'AND tartikel.dErscheinungsdatum IS NULL OR (DATE(tartikel.dErscheinungsdatum) <= CURDATE())';
    if (isset($conf['global']['global_erscheinende_kaeuflich'])
        && $conf['global']['global_erscheinende_kaeuflich'] === 'Y'
    ) {
        $sql = "AND (
                    tartikel.dErscheinungsdatum IS NULL 
                    OR (DATE(tartikel.dErscheinungsdatum) <= CURDATE())
                    OR (
                        DATE(tartikel.dErscheinungsdatum) > CURDATE()
                        AND (tartikel.cLagerBeachten = 'N' 
                            OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = 'Y')
                    )
                )";
    }
    $cid = 'xp_' . md5(json_encode($data) . $sql);
    if (($count = Shop::Container()->getCache()->get($cid)) !== false) {
        return $count;
    }

    $count = Shop::Container()->getDB()->query(
        "SELECT COUNT(*) AS nAnzahl
            FROM tartikel
            LEFT JOIN tartikelattribut 
                ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . (int)$export->kKundengruppe . '
            ' . $data['Join'] . '
            WHERE tartikelattribut.kArtikelAttribut IS NULL' . $data['Where'] . '
                AND tartikelsichtbarkeit.kArtikel IS NULL ' . $sql,
        \DB\ReturnType::SINGLE_OBJECT
    );
    Shop::Container()->getCache()->set($cid, $count, [CACHING_GROUP_CORE], 120);

    return $count;
}
