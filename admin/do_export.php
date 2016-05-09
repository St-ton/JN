<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Preise.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Eigenschaft.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.EigenschaftWert.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kategorie.php';

if (!ini_get('safe_mode')) {
    @ini_set('max_execution_time', 0);
}

if (!isset($_GET['e']) || !intval($_GET['e']) > 0 || !validateToken()) {
    die('0');
}

$queue = Shop::DB()->select('texportqueue', 'kExportqueue', (int)$_GET['e']);
if ($queue === false || !isset($queue->kExportformat) || !$queue->kExportformat || !$queue->nLimit_m) {
    die('1');
}

$exportformat = Shop::DB()->query(
    "SELECT texportformat.*, tkampagne.cParameter AS tkampagne_cParameter, tkampagne.cWert AS tkampagne_cWert
       FROM texportformat
       LEFT JOIN tkampagne ON tkampagne.kKampagne = texportformat.kKampagne
          AND tkampagne.nAktiv = 1
       WHERE texportformat.kExportformat = " . (int)$queue->kExportformat, 1
);

if ($exportformat === false || !$exportformat->kExportformat) {
    die('2');
}

if (!isset($exportformat->kKundengruppe) || !$exportformat->kKundengruppe) {
    $exportformat->kKundengruppe = Kundengruppe::getDefaultGroupID();
}

$ExportEinstellungen = getEinstellungenExport($exportformat->kExportformat);
$Waehrung            = Shop::DB()->select('twaehrung', 'kWaehrung', (int)$exportformat->kWaehrung);
$smarty              = new JTLSmarty(true, false, false);
$smarty->setCaching(0)
       ->setTemplateDir(PFAD_TEMPLATES)
       ->setConfigDir($smarty->getTemplateDir($smarty->context) . 'lang/')
       ->registerResource('db', array('db_get_template', 'db_get_timestamp', 'db_get_secure', 'db_get_trusted'));

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

    Shop::DB()->delete('texportqueue', 'kExportqueue', (int)$queue->kExportqueue);
    if ($_GET['back'] === 'admin') {
        header('Location: exportformate.php?action=exported&token=' . $_SESSION['jtl_token'] . '&kExportformat=' . (int)$queue->kExportformat);
        exit;
    }
    exit;
}
//falls datei existiert, loeschen
if ($queue->nLimit_n == 0 && file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname)) {
    unlink(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname);
}

$datei = fopen(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname, 'a');
// Kopfzeile schreiben
if ($queue->nLimit_n == 0) {
    schreibeKopfzeile($datei, $exportformat->cKopfzeile, $exportformat->cKodierung);
}
$cSQL_arr = baueArtikelExportSQL($exportformat);
$sql      = 'AND NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))';
$conf     = Shop::getSettings(array(CONF_GLOBAL));
if (isset($conf['global']['global_erscheinende_kaeuflich']) && $conf['global']['global_erscheinende_kaeuflich'] === 'Y') {
    $sql = 'AND (
                NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))
                OR  (
                        DATE(tartikel.dErscheinungsdatum) > DATE(NOW())
                        AND (tartikel.cLagerBeachten = "N" OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = "Y")
                    )
            )';
}

$res = Shop::DB()->query(
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
        LIMIT " . $queue->nLimit_n . ", " . $queue->nLimit_m, 2
);
$shopURL    = Shop::getURL();
$find       = array('<br />', '<br>', '</');
$replace    = array(' ', ' ', ' </');
$findTwo    = array("\r\n", "\r", "\n", "\x0B", "\x0");
$replaceTwo = array(' ', ' ', ' ', ' ', '');

if (isset($ExportEinstellungen['exportformate_quot']) && $ExportEinstellungen['exportformate_quot'] !== 'N') {
    $findTwo[] = '"';
    if ($ExportEinstellungen['exportformate_quot'] === 'bq') {
        $replaceTwo[] = '\"';
    } elseif ($ExportEinstellungen['exportformate_quot'] === 'qq') {
        $replaceTwo[] = '""';
    } else {
        $replaceTwo[] = $ExportEinstellungen['exportformate_quot'];
    }
}
if (isset($ExportEinstellungen['exportformate_quot']) && $ExportEinstellungen['exportformate_equot'] !== 'N') {
    $findTwo[] = "'";
    if ($ExportEinstellungen['exportformate_equot'] === 'q') {
        $replaceTwo[] = '"';
    } else {
        $replaceTwo[] = $ExportEinstellungen['exportformate_equot'];
    }
}
if (isset($ExportEinstellungen['exportformate_semikolon']) && $ExportEinstellungen['exportformate_semikolon'] !== 'N') {
    $findTwo[]    = ';';
    $replaceTwo[] = $ExportEinstellungen['exportformate_semikolon'];
}
$waehrung = (isset($_SESSION['Waehrung']->kWaehrung)) ? $_SESSION['Waehrung'] : Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);

$oArtikelOptionen                            = new stdClass();
$oArtikelOptionen->nMerkmale                 = 1;
$oArtikelOptionen->nAttribute                = 1;
$oArtikelOptionen->nArtikelAttribute         = 1;
$oArtikelOptionen->nKategorie                = 1;
$oArtikelOptionen->nKeinLagerbestandBeachten = 1;
$oArtikelOptionen->nMedienDatei              = 1;
foreach ($res as $tartikel) {
    $Artikel = new Artikel();
    $Artikel->fuelleArtikel($tartikel->kArtikel, $oArtikelOptionen, $exportformat->kKundengruppe, $exportformat->kSprache, true);
    if ($Artikel->kArtikel > 0) {
        $Artikel->cBeschreibungHTML     = str_replace('"', '&quot;', $Artikel->cBeschreibung);
        $Artikel->cKurzBeschreibungHTML = str_replace('"', '&quot;', $Artikel->cKurzBeschreibung);
        $Artikel->cName                 = StringHandler::removeWhitespace(str_replace($findTwo, $replaceTwo,
            StringHandler::unhtmlentities(strip_tags(str_replace($find, $replace, $Artikel->cName)))));
        $Artikel->cBeschreibung = StringHandler::removeWhitespace(str_replace($findTwo, $replaceTwo,
            StringHandler::unhtmlentities(strip_tags(str_replace($find, $replace, $Artikel->cBeschreibung)))));
        $Artikel->cKurzBeschreibung = StringHandler::removeWhitespace(str_replace($findTwo, $replaceTwo,
            StringHandler::unhtmlentities(strip_tags(str_replace($find, $replace, $Artikel->cKurzBeschreibung)))));
        $Artikel->cBeschreibungHTML     = StringHandler::removeWhitespace(str_replace($findTwo, $replaceTwo, $Artikel->cBeschreibungHTML));
        $Artikel->cKurzBeschreibungHTML = StringHandler::removeWhitespace(str_replace($findTwo, $replaceTwo, $Artikel->cKurzBeschreibungHTML));
        $Artikel->fUst                  = gibUst($Artikel->kSteuerklasse);
        $Artikel->Preise->fVKBrutto     = berechneBrutto($Artikel->Preise->fVKNetto * $waehrung->fFaktor, $Artikel->fUst);
        $Artikel->Preise->fVKNetto      = round($Artikel->Preise->fVKNetto, 2);
        //Cache loeschen
        unset($_SESSION['ks']);
        unset($_SESSION['oKategorie_arr']);
        unset($_SESSION['oKategorie_arr_new']);
        unset($_SESSION['kKategorieVonUnterkategorien_arr']);
        //Kategoriepfad
        $Artikel->Kategorie     = new Kategorie($Artikel->gibKategorie(), $exportformat->kSprache, $exportformat->kKundengruppe);
        $Artikel->Kategoriepfad = gibKategoriepfad($Artikel->Kategorie, $exportformat->kKundengruppe, $exportformat->kSprache);
        $Artikel->Versandkosten = gibGuenstigsteVersandkosten(
            (isset($ExportEinstellungen['exportformate_lieferland'])) ? $ExportEinstellungen['exportformate_lieferland'] : null,
            $Artikel,
            0,
            $exportformat->kKundengruppe
        );
        if ($Artikel->Versandkosten !== -1) {
            $price = convertCurrency($Artikel->Versandkosten, null, $exportformat->kWaehrung);
            if ($price !== false) {
                $Artikel->Versandkosten = $price;
            }
        }
        // Kampagne URL
        if (isset($exportformat->tkampagne_cParameter)) {
            $cSep = (strpos($Artikel->cURL, '.php') !== false) ? '&' : '?';
            $Artikel->cURL .= $cSep . $exportformat->tkampagne_cParameter . '=' . $exportformat->tkampagne_cWert;
        }

        $Artikel->cDeeplink             = $shopURL . '/' . $Artikel->cURL;
        $Artikel->Artikelbild           = ($Artikel->Bilder[0]->cPfadGross) ? $shopURL . '/' . $Artikel->Bilder[0]->cPfadGross : '';
        $Artikel->Lieferbar             = ($Artikel->fLagerbestand <= 0) ? 'N' : 'Y';
        $Artikel->Lieferbar_01          = ($Artikel->fLagerbestand <= 0) ? 0 : 1;
        $Artikel->Verfuegbarkeit_kelkoo = ($Artikel->fLagerbestand > 0) ? '001' : '003';

        $smarty->assign('Artikel', $Artikel)
               ->assign('Waehrung', $waehrung)
               ->assign('Einstellungen', $ExportEinstellungen);

        $cOutput = $smarty->fetch('db:' . $exportformat->kExportformat);

        executeHook(HOOK_DO_EXPORT_OUTPUT_FETCHED);

        if (strlen($cOutput) > 0) {
            fwrite($datei, (($exportformat->cKodierung === 'UTF-8' || $exportformat->cKodierung === 'UTF-8noBOM') ? (utf8_encode($cOutput . "\n")) : ($cOutput . "\n")));
        }
    }
}

$max_artikel = holeMaxExportArtikelAnzahl($exportformat);
if ($max_artikel->nAnzahl > $queue->nLimit_n + $queue->nLimit_m) {
    Shop::DB()->query("UPDATE texportqueue SET nLimit_n = nLimit_n+" . $queue->nLimit_m . " WHERE kExportqueue = " . (int)$queue->kExportqueue, 4);

    $protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || function_exists('pruefeSSL') && pruefeSSL() === 2) ?
        'https://' :
        'http://';
    if (isset($_GET['ajax'])) {
        $oCallback                = new stdClass();
        $oCallback->kExportformat = $queue->kExportformat;
        $oCallback->kExportqueue  = $queue->kExportqueue;
        $oCallback->nMax          = $max_artikel->nAnzahl;
        $oCallback->nCurrent      = $queue->nLimit_n + $queue->nLimit_m;
        $oCallback->bFinished     = false;
        $oCallback->bFirst        = ($queue->nLimit_n == 0);
        $oCallback->cURL          = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        echo json_encode($oCallback);
        exit;
    } else {
        $cURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?e=' . (int)$queue->kExportqueue . '&back=admin&token=' . $_SESSION['jtl_token'];
        header('Location: ' . $cURL);
        exit;
    }
} else {
    // Versucht (falls so eingestellt) die erstellte Exportdatei in mehrere Dateien zu splitten
    splitteExportDatei($exportformat);

    Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt = now() WHERE kExportformat = " . (int)$queue->kExportformat, 4);
    Shop::DB()->delete('texportqueue', 'kExportqueue', (int)$queue->kExportqueue);

    schreibeFusszeile($datei, $exportformat->cFusszeile, $exportformat->cKodierung);
    fclose($datei);

    if (isset($_GET['back']) && $_GET['back'] === 'admin') {
        if (isset($_GET['ajax'])) {
            $oCallback                = new stdClass();
            $oCallback->kExportformat = $queue->kExportformat;
            $oCallback->nMax          = $max_artikel->nAnzahl;
            $oCallback->nCurrent      = $queue->nLimit_n;
            $oCallback->bFinished     = true;

            echo json_encode($oCallback);
            exit;
        } else {
            header('Location: exportformate.php?action=exported&token=' . $_SESSION['jtl_token'] . '&kExportformat=' . (int)$queue->kExportformat);
            exit;
        }
    }
}
/**
 * @param string    $tpl_name
 * @param mixed     $tpl_source
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_template($tpl_name, &$tpl_source, $smarty)
{
    $exportformat = Shop::DB()->select('texportformat', 'kExportformat', (int)$tpl_name);
    if (empty($exportformat->kExportformat) || $exportformat->kExportformat <= 0) {
        return false;
    }
    $tpl_source = $exportformat->cContent;

    return true;
}

/**
 * @param string    $tpl_name
 * @param int       $tpl_timestamp
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_timestamp($tpl_name, &$tpl_timestamp, $smarty)
{
    $tpl_timestamp = time();

    return true;
}

/**
 * @param string    $tpl_name
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_secure($tpl_name, $smarty)
{
    return true;
}

/**
 * @param string    $tpl_name
 * @param JTLSmarty $smarty
 */
function db_get_trusted($tpl_name, $smarty)
{
}
