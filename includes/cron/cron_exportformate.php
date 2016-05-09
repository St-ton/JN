<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kategorie.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';

/**
 * @return JTLSmarty
 */
function getSmarty()
{
    $smarty = new JTLSmarty(true, false, false, 'cron');
    $smarty->setCaching(0)
           ->setDebugging(0)
           ->setTemplateDir(PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES)
           ->setCompileDir(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR)
           ->setConfigDir($smarty->getTemplateDir($smarty->context) . 'lang/')
           ->register_resource('db', array('db_get_template', 'db_get_timestamp', 'db_get_secure', 'db_get_trusted'));

    return $smarty;
}

/**
 * @param JobQueue $oJobQueue
 */
function bearbeiteExportformate($oJobQueue)
{
    $smarty               = getSmarty();
    $oJobQueue->nInArbeit = 1;
    $oExportformat        = $oJobQueue->holeJobArt();
    // Kampagne
    if (isset($oExportformat->kKampagne) && $oExportformat->kKampagne > 0) {
        $oKampagne = Shop::DB()->query(
            "SELECT kKampagne, cParameter, cWert
                FROM tkampagne
                WHERE kKampagne = " . intval($oExportformat->kKampagne) . "
                    AND nAktiv = 1", 1
        );
        if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
            $oExportformat->tkampagne_cParameter = $oKampagne->cParameter;
            $oExportformat->tkampagne_cWert      = $oKampagne->cWert;
        }
    }
    $exportformat = $oExportformat;
    // Temp Datei
    $cTMPDatei           = 'tmp_' . $exportformat->cDateiname;
    $ExportEinstellungen = getEinstellungenExport($exportformat->kExportformat);
    // Special Export?
    if ($oExportformat->nSpecial == SPECIAL_EXPORTFORMAT_YATEGO) {
        gibYategoExport($exportformat, $oJobQueue, $ExportEinstellungen);
    } else {
        if ($exportformat->kWaehrung > 0) {
            $Waehrung = Shop::DB()->select('twaehrung', 'kWaehrung', (int)$exportformat->kWaehrung);
        }
        setzeSteuersaetze();
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = new stdClass();
        }
        $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
        $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
        $_SESSION['kSprache']                                 = $exportformat->kSprache;
        $_SESSION['kKundengruppe']                            = $exportformat->kKundengruppe;
        $_SESSION['Kundengruppe']->kKundengruppe              = $exportformat->kKundengruppe;
        $_SESSION['Sprachen']                                 = Shop::DB()->query("SELECT * FROM tsprache", 2);
        $_SESSION['Waehrung']                                 = $Waehrung;

        // Plugin?
        if ($exportformat->kPlugin > 0 && strpos($exportformat->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            $oPlugin = new Plugin($exportformat->kPlugin);
            include $oPlugin->cAdminmenuPfad . PFAD_PLUGIN_EXPORTFORMAT . str_replace(PLUGIN_EXPORTFORMAT_CONTENTFILE, '', $exportformat->cContent);

            return;
        }
        //falls datei existiert, löschen
        if ($oJobQueue->nLimitN == 0 && file_exists(PFAD_ROOT . PFAD_EXPORT . $cTMPDatei)) {
            unlink(PFAD_ROOT . PFAD_EXPORT . $cTMPDatei);
        }
        $datei = fopen(PFAD_ROOT . PFAD_EXPORT . $cTMPDatei, 'a');
        // Kopfzeile schreiben
        if ($oJobQueue->nLimitN == 0) {
            schreibeKopfzeile($datei, $exportformat->cKopfzeile, $exportformat->cKodierung);
        }
        $sql  = 'AND NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))';
        $conf = Shop::getSettings(array(CONF_GLOBAL));
        if (isset($conf['global']['global_erscheinende_kaeuflich']) && $conf['global']['global_erscheinende_kaeuflich'] === 'Y') {
            $sql = 'AND (
                        NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))
                        OR  (
                                DATE(tartikel.dErscheinungsdatum) > DATE(NOW())
                                AND (tartikel.cLagerBeachten = "N" OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = "Y")
                            )
                    )';
        }

        $cSQL_arr     = baueArtikelExportSQL($exportformat);
        $oArtikel_arr = Shop::DB()->query(
            "SELECT tartikel.kArtikel
                FROM tartikel
                LEFT JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
                    AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
                " . $cSQL_arr['Join'] . "
                LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $exportformat->kKundengruppe . "
                WHERE tartikelattribut.kArtikelAttribut IS NULL" . $cSQL_arr['Where'] . "
                    AND tartikelsichtbarkeit.kArtikel IS NULL
                    {$sql}
                ORDER BY kArtikel
                LIMIT " . $oJobQueue->nLimitN . ", " . $oJobQueue->nLimitM, 2
        );
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            foreach ($oArtikel_arr as $tartikel) {
                $Artikel                                     = new Artikel();
                $oArtikelOptionen                            = new stdClass();
                $oArtikelOptionen->nMerkmale                 = 1;
                $oArtikelOptionen->nAttribute                = 1;
                $oArtikelOptionen->nArtikelAttribute         = 1;
                $oArtikelOptionen->nKategorie                = 1;
                $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
                $oArtikelOptionen->nMedienDatei              = 1;
                $Artikel->fuelleArtikel($tartikel->kArtikel, $oArtikelOptionen, $exportformat->kKundengruppe, $exportformat->kSprache);

                if ($Artikel->kArtikel > 0) {
                    $Artikel->cBeschreibungHTML     = str_replace('"', '&quot;', $Artikel->cBeschreibung);
                    $Artikel->cKurzBeschreibungHTML = str_replace('"', '&quot;', $Artikel->cKurzBeschreibung);

                    $find    = array('<br />', '<br>', '</');
                    $replace = array(' ', ' ', ' </');

                    $Artikel->cName             = str_replace($find, $replace, $Artikel->cName);
                    $Artikel->cBeschreibung     = str_replace($find, $replace, $Artikel->cBeschreibung);
                    $Artikel->cKurzBeschreibung = str_replace($find, $replace, $Artikel->cKurzBeschreibung);
                    $Artikel->cName             = strip_tags($Artikel->cName);
                    $Artikel->cBeschreibung     = strip_tags($Artikel->cBeschreibung);
                    $Artikel->cKurzBeschreibung = strip_tags($Artikel->cKurzBeschreibung);

                    $find    = array("\r\n", "\r", "\n", "\x0B", "\x0");
                    $replace = array(' ', ' ', ' ', ' ', '');

                    if (isset($ExportEinstellungen['exportformate_quot']) && $ExportEinstellungen['exportformate_quot'] !== 'N') {
                        $find[] = '"';
                        if ($ExportEinstellungen['exportformate_quot'] === 'bq') {
                            $replace[] = '\"';
                        } elseif ($ExportEinstellungen['exportformate_quot'] === 'qq') {
                            $replace[] = '""';
                        } else {
                            $replace[] = $ExportEinstellungen['exportformate_quot'];
                        }
                    }
                    if (isset($ExportEinstellungen['exportformate_equot']) && $ExportEinstellungen['exportformate_equot'] !== 'N') {
                        $find[] = "'";
                        if ($ExportEinstellungen['exportformate_equot'] === 'q') {
                            $replace[] = '"';
                        } else {
                            $replace[] = $ExportEinstellungen['exportformate_equot'];
                        }
                    }
                    if (isset($ExportEinstellungen['exportformate_semikolon']) && $ExportEinstellungen['exportformate_semikolon'] !== 'N') {
                        $find[]    = ';';
                        $replace[] = $ExportEinstellungen['exportformate_semikolon'];
                    }
                    $Artikel->cName                 = StringHandler::unhtmlentities($Artikel->cName);
                    $Artikel->cBeschreibung         = StringHandler::unhtmlentities($Artikel->cBeschreibung);
                    $Artikel->cKurzBeschreibung     = StringHandler::unhtmlentities($Artikel->cKurzBeschreibung);
                    $Artikel->cName                 = StringHandler::removeWhitespace(str_replace($find, $replace, $Artikel->cName));
                    $Artikel->cBeschreibung         = StringHandler::removeWhitespace(str_replace($find, $replace, $Artikel->cBeschreibung));
                    $Artikel->cKurzBeschreibung     = StringHandler::removeWhitespace(str_replace($find, $replace, $Artikel->cKurzBeschreibung));
                    $Artikel->cBeschreibungHTML     = StringHandler::removeWhitespace(str_replace($find, $replace, $Artikel->cBeschreibungHTML));
                    $Artikel->cKurzBeschreibungHTML = StringHandler::removeWhitespace(str_replace($find, $replace, $Artikel->cKurzBeschreibungHTML));

                    $waehrung = $_SESSION['Waehrung'];
                    if (!isset($waehrung->kWaehrung)) {
                        $waehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);
                    }

                    $Artikel->fUst              = gibUst($Artikel->kSteuerklasse);
                    $Artikel->Preise->fVKBrutto = berechneBrutto($Artikel->Preise->fVKNetto * $waehrung->fFaktor, $Artikel->fUst);
                    $Artikel->Preise->fVKNetto  = round($Artikel->Preise->fVKNetto, 2);
                    //Cache loeschen
                    unset($_SESSION['ks']);
                    unset($_SESSION['oKategorie_arr']);
                    unset($_SESSION['oKategorie_arr_new']);
                    unset($_SESSION['kKategorieVonUnterkategorien_arr']);
                    //Kategoriepfad
                    $iso                    = (isset($ExportEinstellungen['exportformate_lieferland'])) ? $ExportEinstellungen['exportformate_lieferland'] : '';
                    $Artikel->Kategorie     = new Kategorie($Artikel->gibKategorie(), $exportformat->kSprache, $exportformat->kKundengruppe);
                    $Artikel->Kategoriepfad = gibKategoriepfad($Artikel->Kategorie, $exportformat->kKundengruppe, $exportformat->kSprache);
                    $Artikel->Versandkosten = gibGuenstigsteVersandkosten($iso, $Artikel, 0, $exportformat->kKundengruppe);
                    if ($Artikel->Versandkosten != -1) {
                        $price = convertCurrency($Artikel->Versandkosten, null, $exportformat->kWaehrung);
                        if ($price !== false) {
                            $Artikel->Versandkosten = $price;
                        }
                    }
                    // Kampagne URL
                    if (isset($exportformat->tkampagne_cParameter)) {
                        $cSep = '?';
                        if (strpos($Artikel->cURL, '.php') !== false) {
                            $cSep = '&';
                        }
                        $Artikel->cURL .= $cSep . $exportformat->tkampagne_cParameter . '=' . $exportformat->tkampagne_cWert;
                    }
                    $Artikel->cDeeplink   = Shop::getURL() . '/' . $Artikel->cURL;
                    $Artikel->Artikelbild = '';
                    if ($Artikel->Bilder[0]->cPfadGross) {
                        $Artikel->Artikelbild = Shop::getURL() . '/' . $Artikel->Bilder[0]->cPfadGross;
                    }
                    $Artikel->Lieferbar = 'Y';
                    if ($Artikel->fLagerbestand <= 0) {
                        $Artikel->Lieferbar = 'N';
                    }
                    $Artikel->Lieferbar_01 = 1;
                    if ($Artikel->fLagerbestand <= 0) {
                        $Artikel->Lieferbar_01 = 0;
                    }
                    $Artikel->Verfuegbarkeit_kelkoo = '003';
                    if ($Artikel->fLagerbestand > 0) {
                        $Artikel->Verfuegbarkeit_kelkoo = "001";
                    }
                    $smarty->assign('Artikel', $Artikel);
                    $smarty->assign('URL_SHOP', Shop::getURL());
                    $smarty->assign('Waehrung', $Waehrung);
                    $smarty->assign('Einstellungen', $ExportEinstellungen);

                    $cOutput = $smarty->fetch('db:' . $exportformat->kExportformat);

                    executeHook(HOOK_CRON_EXPORTFORMATE_OUTPUT_FETCHED);

                    if (strlen($cOutput) > 0) {
                        if ($exportformat->cKodierung == 'UTF-8') {
                            fwrite($datei, utf8_encode($cOutput . "\n"));
                        } else {
                            fwrite($datei, $cOutput . "\n");
                        }
                    }
                }
                $oJobQueue->nLimitN += 1;
                $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
                $oJobQueue->updateJobInDB();
            }
            fclose($datei);

            updateExportformatQueueBearbeitet($oJobQueue);
            $oJobQueue->nInArbeit = 0;
            $oJobQueue->updateJobInDB();
        } else {
            Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt=now() WHERE kExportformat = " . (int)$oJobQueue->kKey, 4);
            $oJobQueue->deleteJobInDB();

            if (file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname)) {
                unlink(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname);
            }
            if (file_exists(PFAD_ROOT . PFAD_EXPORT . $cTMPDatei)) {
                // Schreibe Fusszeile
                $datei = fopen(PFAD_ROOT . PFAD_EXPORT . $cTMPDatei, 'a');
                schreibeFusszeile($datei, $exportformat->cFusszeile, $exportformat->cKodierung);
                fclose($datei);
                if (copy(PFAD_ROOT . PFAD_EXPORT . $cTMPDatei, PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname)) {
                    unlink(PFAD_ROOT . PFAD_EXPORT . $cTMPDatei);
                }
            }
            // Versucht (falls so eingestellt) die erstellte Exportdatei zu in mehrere Dateien zu splitten
            splitteExportDatei($exportformat);

            unset($oJobQueue);
        }
    }
}

/**
 * @param $oJobQueue
 * @return bool
 */
function updateExportformatQueueBearbeitet($oJobQueue)
{
    if ($oJobQueue->kJobQueue > 0) {
        Shop::DB()->query(
            "DELETE FROM texportformatqueuebearbeitet
                WHERE kJobQueue = " . $oJobQueue->kJobQueue, 4
        );

        $oExportformatQueueBearbeitet                   = new stdClass();
        $oExportformatQueueBearbeitet->kJobQueue        = $oJobQueue->kJobQueue;
        $oExportformatQueueBearbeitet->kExportformat    = $oJobQueue->kKey;
        $oExportformatQueueBearbeitet->nLimitN          = $oJobQueue->nLimitN;
        $oExportformatQueueBearbeitet->nLimitM          = $oJobQueue->nLimitM;
        $oExportformatQueueBearbeitet->nInArbeit        = $oJobQueue->nInArbeit;
        $oExportformatQueueBearbeitet->dStartZeit       = $oJobQueue->dStartZeit;
        $oExportformatQueueBearbeitet->dZuletztGelaufen = $oJobQueue->dZuletztGelaufen;

        Shop::DB()->insert('texportformatqueuebearbeitet', $oExportformatQueueBearbeitet);

        return true;
    }

    return false;
}

/**
 * @param $n
 * @return mixed
 */
function getNum($n)
{
    return str_replace('.', ',', $n);
}

/**
 * @param string $img
 * @return string
 */
function getURL($img)
{
    return ($img) ? Shop::getURL() . '/' . $img : '';
}

/**
 * @param $file
 * @param $data
 */
function writeFile($file, $data)
{
    $handle = fopen($file, 'a');
    fwrite($handle, $data);
    fclose($handle);
}

/**
 * @param array $cGlobalAssoc_arr
 * @param int   $nLimitN
 * @return string
 */
function makecsv($cGlobalAssoc_arr, $nLimitN = 0)
{
    global $queue;
    $out = '';
    if (isset($queue->nLimit_n)) {
        $nLimitN = $queue->nLimit_n;
    }
    if (is_array($cGlobalAssoc_arr) && count($cGlobalAssoc_arr) > 0) {
        if ($nLimitN == 0) {
            $fieldnames = array_keys($cGlobalAssoc_arr[0]);
            $out        = ESC . implode(ESC . DELIMITER . ESC, $fieldnames) . ESC . CRLF;
        }
        foreach ($cGlobalAssoc_arr as $cGlobalAssoc) {
            $out .= ESC . implode(ESC . DELIMITER . ESC, $cGlobalAssoc) . ESC . CRLF;
        }
    }

    return $out;
}

/**
 * @param string $tpl_name
 * @param string $tpl_source
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_template($tpl_name, &$tpl_source, $smarty)
{
    $exportformat = Shop::DB()->query("SELECT * FROM texportformat WHERE kExportformat=" . $tpl_name, 1);

    if (!$exportformat->kExportformat > 0) {
        return false;
    }
    $tpl_source = $exportformat->cContent;

    return true;
}

/**
 * @param string $tpl_name
 * @param string $tpl_timestamp
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_timestamp($tpl_name, &$tpl_timestamp, $smarty)
{
    $tpl_timestamp = time();

    return true;
}

/**
 * @param string $tpl_name
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_secure($tpl_name, $smarty)
{
    return true;
}

/**
 * @param string $tpl_name
 * @param JTLSmarty $smarty
 */
function db_get_trusted($tpl_name, $smarty)
{
}

/**
 * @param array $catlist
 * @return array
 */
function getCats($catlist)
{
    $cats     = array();
    $shopcats = array();
    $res      = Shop::DB()->query("SELECT kKategorie, cName, kOberKategorie, nSort FROM tkategorie", 10);
    while ($row = $res->fetch_assoc()) {
        $cats[array_shift($row)] = $row;
    }
    foreach ($catlist as $cat_id) {
        $this_cat = $cat_id;
        $catdir   = array();
        while ($this_cat > 0) {
            array_unshift($catdir, array($this_cat, $cats[$this_cat]['cName']));
            $this_cat = $cats[$this_cat]['kOberKategorie'];
        }
        $shopcats[] = array(
            'foreign_id_h' => $catdir[0][0],
            'foreign_id_m' => $catdir[1][0],
            'foreign_id_l' => $catdir[2][0],
            'title_h'      => $catdir[0][1],
            'title_m'      => $catdir[1][1],
            'title_l'      => $catdir[2][1],
            'sorting'      => $cats[$cat_id]['nSort']);
    }

    return $shopcats;
}

/**
 * @param string $entry
 */
function writeLogTMP($entry)
{
    $logfile = fopen(PFAD_LOGFILES . 'exportformat.log', 'a');
    fwrite($logfile, "\n[" . date('m.d.y H:i:s') . " " . microtime() . "] " . $_SERVER['SCRIPT_NAME'] . "\n" . $entry);
    fclose($logfile);
}

/**
 * @param object $exportformat
 * @param object $oJobQueue
 * @param array  $ExportEinstellungen
 * @return bool
 */
function gibYategoExport($exportformat, $oJobQueue, $ExportEinstellungen)
{
    $smarty = getSmarty();

    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

    define('DELIMITER', ';');
    define('ESC', '"');
    define('CRLF', "\n");
    define('PATH', PFAD_ROOT . PFAD_EXPORT_YATEGO);
    define('DESCRIPTION_TAGS', '<a><b><i><u><p><br><hr><h1><h2><h3><h4><h5><h6><ul><ol><li><span><font><table><colgroup>');

    if (!pruefeYategoExportPfad()) {
        Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt=now() WHERE kExportformat = " . (int)$oJobQueue->kKey, 4);
        $oJobQueue->deleteJobInDB();
        unset($oJobQueue);

        return false;
    }
    //falls dateien existieren, löschen
    if ($oJobQueue->nLimitN == 0 && file_exists(PATH . 'varianten.csv')) {
        unlink(PATH . 'varianten.csv');
    }
    if ($oJobQueue->nLimitN == 0 && file_exists(PATH . 'artikel.csv')) {
        unlink(PATH . 'artikel.csv');
    }
    if ($oJobQueue->nLimitN == 0 && file_exists(PATH . 'shopkategorien.csv')) {
        unlink(PATH . 'shopkategorien.csv');
    }
    if ($oJobQueue->nLimitN == 0 && file_exists(PATH . 'lager.csv')) {
        unlink(PATH . 'lager.csv');
    }
    // Global Array
    $oGlobal_arr          = array();
    $oGlobal_arr['lager'] = array();

    setzeSteuersaetze();
    $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
    $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
    $_SESSION['kSprache']                                 = $exportformat->kSprache;
    $_SESSION['kKundengruppe']                            = $exportformat->kKundengruppe;
    $_SESSION['Kundengruppe']->kKundengruppe              = $exportformat->kKundengruppe;

    $KategorieListe = array();
    $oArtikel_arr   = Shop::DB()->query(
        "SELECT tartikel.kArtikel
            FROM tartikel
            JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
            WHERE tartikelattribut.cName='yategokat'
                AND tartikel.kVaterArtikel = 0
            ORDER BY tartikel.kArtikel
            LIMIT " . $oJobQueue->nLimitN . ", " . $oJobQueue->nLimitM, 2
    );

    if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
        foreach ($oArtikel_arr as $i => $tartikel) {
            $Artikel = new Artikel();
            $Artikel->fuelleArtikel($tartikel->kArtikel, Artikel::getDefaultOptions(), $exportformat->kKundengruppe, $exportformat->kSprache);

            verarbeiteYategoExport($Artikel, $exportformat, $ExportEinstellungen, $KategorieListe, $oGlobal_arr);

            $oJobQueue->nLimitN += 1;
        }

        $KategorieListe                = array_keys($KategorieListe);
        $oGlobal_arr['shopkategorien'] = getCats($KategorieListe);

        if ($exportformat->cKodierung === 'UTF-8') {
            $cHeader = "\xEF\xBB\xBF";
            writeFile(PATH . 'varianten.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['varianten'], $oJobQueue->nLimitN) . CRLF . makecsv($oGlobal_arr['variantenwerte'], $oJobQueue->nLimitN)));
            writeFile(PATH . 'artikel.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['artikel'], $oJobQueue->nLimitN)));
            writeFile(PATH . 'shopkategorien.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['shopkategorien'], $oJobQueue->nLimitN)));
            writeFile(PATH . 'lager.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['lager'], $oJobQueue->nLimitN)));
        } else {
            writeFile(PATH . 'varianten.csv', makecsv($oGlobal_arr['varianten'], $oJobQueue->nLimitN) . CRLF . makecsv($oGlobal_arr['variantenwerte'], $oJobQueue->nLimitN));
            writeFile(PATH . 'artikel.csv', makecsv($oGlobal_arr['artikel'], $oJobQueue->nLimitN));
            writeFile(PATH . 'shopkategorien.csv', makecsv($oGlobal_arr['shopkategorien'], $oJobQueue->nLimitN));
            writeFile(PATH . 'lager.csv', makecsv($oGlobal_arr['lager'], $oJobQueue->nLimitN));
        }

        $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
        $oJobQueue->nInArbeit        = 0;
        $oJobQueue->updateJobInDB();
        updateExportformatQueueBearbeitet($oJobQueue);
    } else {
        Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt=now() WHERE kExportformat = " . (int)$oJobQueue->kKey, 4);
        $oJobQueue->deleteJobInDB();
        unset($oJobQueue);
    }

    return true;
}
