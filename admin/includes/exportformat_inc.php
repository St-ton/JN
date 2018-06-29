<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @deprecated since 4.05
 * @param array $cDateinameSplit_arr
 * @param int   $nDateiZaehler
 * @return string
 */
function gibDateiname($cDateinameSplit_arr, $nDateiZaehler)
{
    if (is_array($cDateinameSplit_arr) && count($cDateinameSplit_arr) > 1) {
        return $cDateinameSplit_arr[0] . $nDateiZaehler . $cDateinameSplit_arr[1];
    }

    return $cDateinameSplit_arr[0] . $nDateiZaehler;
}

/**
 * @deprecated since 4.05
 * @param array $cDateinameSplit_arr
 * @param int   $nDateiZaehler
 * @return string
 */
function gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler)
{
    return PFAD_ROOT . PFAD_EXPORT . gibDateiname($cDateinameSplit_arr, $nDateiZaehler);
}

/**
 * @deprecated since 4.05
 * @return array
 */
function pruefeExportformat()
{
    $cPlausiValue_arr = [];
    // Name
    if (!isset($_POST['cName']) || strlen($_POST['cName']) === 0) {
        $cPlausiValue_arr['cName'] = 1;
    }
    // Dateiname
    if (!isset($_POST['cDateiname']) || strlen($_POST['cDateiname']) === 0) {
        $cPlausiValue_arr['cDateiname'] = 1;
    }
    // Dateiname Endung fehlt
    if (strpos($_POST['cDateiname'], '.') === false) {
        $cPlausiValue_arr['cDateiname'] = 2;
    }
    // Content
    if (!isset($_POST['cContent']) || strlen($_POST['cContent']) === 0) {
        $cPlausiValue_arr['cContent'] = 1;
    }
    // Sprache
    if (!isset($_POST['kSprache']) || (int)$_POST['kSprache'] === 0) {
        $cPlausiValue_arr['kSprache'] = 1;
    }
    // Sprache
    if (!isset($_POST['kWaehrung']) || (int)$_POST['kWaehrung'] === 0) {
        $cPlausiValue_arr['kWaehrung'] = 1;
    }
    // Kundengruppe
    if (!isset($_POST['kKundengruppe']) || (int)$_POST['kKundengruppe'] === 0) {
        $cPlausiValue_arr['kKundengruppe'] = 1;
    }

    return $cPlausiValue_arr;
}

/**
 * Falls eingestellt, wird die Exportdatei in mehrere Dateien gesplittet
 *
 * @deprecated since 4.05
 * @param object $oExportformat
 */
function splitteExportDatei($oExportformat)
{
    if (isset($oExportformat->nSplitgroesse) &&
        (int)$oExportformat->nSplitgroesse > 0 &&
        file_exists(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname)
    ) {
        $nDateiZaehler       = 1;
        $cDateinameSplit_arr = [];
        $nFileTypePos        = strrpos($oExportformat->cDateiname, '.');
        // Dateiname splitten nach Name + Typ
        if ($nFileTypePos === false) {
            $cDateinameSplit_arr[0] = $oExportformat->cDateiname;
        } else {
            $cDateinameSplit_arr[0] = substr($oExportformat->cDateiname, 0, $nFileTypePos);
            $cDateinameSplit_arr[1] = substr($oExportformat->cDateiname, $nFileTypePos);
        }
        // Ist die angelegte Datei größer als die Einstellung im Exportformat?
        clearstatcache();
        if (filesize(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname) >=
            ($oExportformat->nSplitgroesse * 1024 * 1024 - 102400)) {
            sleep(2);
            loescheExportDateien($oExportformat->cDateiname, $cDateinameSplit_arr[0]);
            $handle     = fopen(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname, 'r');
            $nZeile     = 1;
            $new_handle = fopen(gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler), 'w');
            $nSizeDatei = 0;
            while (($cContent = fgets($handle)) !== false) {
                if ($nZeile > 1) {
                    $nSizeZeile = strlen($cContent) + 2;
                    //Schwelle erreicht?
                    if ($nSizeDatei <= ($oExportformat->nSplitgroesse * 1024 * 1024 - 102400)) {
                        // Schreibe Content
                        fwrite($new_handle, $cContent);
                        $nSizeDatei += $nSizeZeile;
                    } else {
                        //neue Datei
                        schreibeFusszeile($new_handle, $oExportformat->cFusszeile, $oExportformat->cKodierung);
                        fclose($new_handle);
                        $nDateiZaehler++;
                        $new_handle = fopen(gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler), 'w');
                        schreibeKopfzeile($new_handle, $oExportformat->cKopfzeile, $oExportformat->cKodierung);
                        // Schreibe Content
                        fwrite($new_handle, $cContent);
                        $nSizeDatei = $nSizeZeile;
                    }
                } elseif ($nZeile === 1) {
                    schreibeKopfzeile($new_handle, $oExportformat->cKopfzeile, $oExportformat->cKodierung);
                }
                $nZeile++;
            }
            fclose($new_handle);
            fclose($handle);
            unlink(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname);
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
    //export begin
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
    if (strlen($cFusszeile) > 0) {
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
                if ($cDatei !== $cDateiname && strpos($cDatei, $cDateinameSplit) !== false) {
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
        $einst = Shop::Container()->getDB()->selectAll(
            'texportformateinstellungen',
            'kExportformat',
            $kExportformat,
            'cName, cWert'
        );
        foreach ($einst as $eins) {
            if ($eins->cName) {
                $ret[$eins->cName] = $eins->cWert;
            }
        }
    }

    return $ret;
}

/**
 * @deprecated since 4.05
 * @param object $oExportformat
 * @return array
 */
function baueArtikelExportSQL(&$oExportformat)
{
    $cSQL_arr          = [];
    $cSQL_arr['Where'] = '';
    $cSQL_arr['Join']  = '';

    if (empty($oExportformat->kExportformat)) {
        return $cSQL_arr;
    }
    $cExportEinstellungAssoc_arr = getEinstellungenExport($oExportformat->kExportformat);

    switch ($oExportformat->nVarKombiOption) {
        case 2:
            $cSQL_arr['Where'] = " AND kVaterArtikel = 0";
            break;
        case 3:
            $cSQL_arr['Where'] = " AND (tartikel.nIstVater != 1 OR tartikel.kEigenschaftKombi > 0)";
            break;
    }
    if (isset($cExportEinstellungAssoc_arr['exportformate_lager_ueber_null'])
        && $cExportEinstellungAssoc_arr['exportformate_lager_ueber_null'] === 'Y'
    ) {
        $cSQL_arr['Where'] .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y'))";
    } elseif (isset($cExportEinstellungAssoc_arr['exportformate_lager_ueber_null'])
        && $cExportEinstellungAssoc_arr['exportformate_lager_ueber_null'] === 'O'
    ) {
        $cSQL_arr['Where'] .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                                    OR tartikel.cLagerKleinerNull = 'Y')";
    }

    if (isset($cExportEinstellungAssoc_arr['exportformate_preis_ueber_null'])
        && $cExportEinstellungAssoc_arr['exportformate_preis_ueber_null'] === 'Y'
    ) {
        $cSQL_arr['Join'] .= " JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                                AND tpreise.kKundengruppe = " . (int)$oExportformat->kKundengruppe . "
                                AND tpreise.fVKNetto > 0";
    }

    if (isset($cExportEinstellungAssoc_arr['exportformate_beschreibung'])
        && $cExportEinstellungAssoc_arr['exportformate_beschreibung'] === 'Y'
    ) {
        $cSQL_arr['Where'] .= " AND tartikel.cBeschreibung != ''";
    }

    return $cSQL_arr;
}

/**
 * @deprecated since 4.05
 * @param object $oExportformat
 * @return mixed
 */
function holeMaxExportArtikelAnzahl(&$oExportformat)
{
    $cSQL_arr = baueArtikelExportSQL($oExportformat);
    $conf     = Shop::getSettings([CONF_GLOBAL]);
    $sql      = 'AND NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))';
    if (isset($conf['global']['global_erscheinende_kaeuflich']) &&
        $conf['global']['global_erscheinende_kaeuflich'] === 'Y') {
        $sql = 'AND (
                    NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))
                    OR  (
                            DATE(tartikel.dErscheinungsdatum) > DATE(NOW())
                            AND (tartikel.cLagerBeachten = "N" 
                                OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = "Y")
                        )
                )';
    }
    $cid = 'xp_' . md5(json_encode($cSQL_arr) . $sql);
    if (($count = Shop::Cache()->get($cid)) !== false) {
        return $count;
    }

    $count = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tartikel
            LEFT JOIN tartikelattribut 
                ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . (int)$oExportformat->kKundengruppe . "
            " . $cSQL_arr['Join'] . "
            WHERE tartikelattribut.kArtikelAttribut IS NULL" . $cSQL_arr['Where'] . "
                AND tartikelsichtbarkeit.kArtikel IS NULL
                {$sql}",
        \DB\ReturnType::SINGLE_OBJECT
    );
    Shop::Cache()->set($cid, $count, [CACHING_GROUP_CORE], 120);

    return $count;
}
