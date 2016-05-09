<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('MODULE_COMPARELIST_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';

$cHinweis = '';
$cFehler  = '';
$cSetting = '(469, 470)';
// BlaetterNavi Getter / Setter + SQL
$nAnzahlProSeite   = 15;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(1, $nAnzahlProSeite);
// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
// Zeitfilter
if (!isset($_SESSION['Vergleichsliste'])) {
    $_SESSION['Vergleichsliste'] = new stdClass();
}
$_SESSION['Vergleichsliste']->nZeitFilter = 1;
$_SESSION['Vergleichsliste']->nAnzahl     = 10;
if (isset($_POST['zeitfilter']) && intval($_POST['zeitfilter']) === 1) {
    $_SESSION['Vergleichsliste']->nZeitFilter = (isset($_POST['nZeitFilter']) ? intval($_POST['nZeitFilter']) : 0);
    $_SESSION['Vergleichsliste']->nAnzahl     = (isset($_POST['nAnzahl']) ? intval($_POST['nAnzahl']) : 0);
}

if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1 && validateToken()) {
    $oConfig_arr = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE (kEinstellungenConf IN " . $cSetting . " OR kEinstellungenSektion = " . CONF_VERGLEICHSLISTE . ")
                AND cConf = 'Y'
            ORDER BY nSort", 2
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        unset($aktWert);
        $aktWert                        = new stdClass();
        $aktWert->cWert                 = $_POST[$oConfig_arr[$i]->cWertName];
        $aktWert->cName                 = $oConfig_arr[$i]->cWertName;
        $aktWert->kEinstellungenSektion = $oConfig_arr[$i]->kEinstellungenSektion;
        switch ($oConfig_arr[$i]->cInputTyp) {
            case 'kommazahl':
                $aktWert->cWert = floatval($aktWert->cWert);
                break;
            case 'zahl':
            case 'number':
                $aktWert->cWert = intval($aktWert->cWert);
                break;
            case 'text':
                $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                break;
        }
        Shop::DB()->delete('teinstellungen', array('kEinstellungenSektion', 'cName'), array((int)$oConfig_arr[$i]->kEinstellungenSektion, $oConfig_arr[$i]->cWertName));
        Shop::DB()->insert('teinstellungen', $aktWert);
    }

    unset($oConfig_arr);
    $cHinweis .= 'Ihre Einstellungen wurden &uuml;bernommen.';
}

$oConfig_arr = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE (kEinstellungenConf IN " . $cSetting . " OR kEinstellungenSektion = " . CONF_VERGLEICHSLISTE . ")
        ORDER BY nSort", 2
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
                ORDER BY nSort", 2
        );
    }
    $oSetValue = Shop::DB()->query(
        "SELECT cWert
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . (int)$oConfig_arr[$i]->kEinstellungenSektion . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
}

$smarty->assign('oConfig_arr', $oConfig_arr);
// Max Anzahl Vergleiche
$oVergleichAnzahl = Shop::DB()->query(
    "SELECT count(*) AS nAnzahl
        FROM tvergleichsliste", 1
);
// Letzten 20 Vergleiche
$oLetzten20Vergleichsliste_arr = Shop::DB()->query(
    "SELECT kVergleichsliste, DATE_FORMAT(dDate, '%d.%m.%Y  %H:%i') AS Datum
        FROM tvergleichsliste
        ORDER BY dDate DESC " . $oBlaetterNaviConf->cSQL1, 2
);

if (is_array($oLetzten20Vergleichsliste_arr) && count($oLetzten20Vergleichsliste_arr) > 0) {
    $oLetzten20VergleichslistePos_arr = array();

    foreach ($oLetzten20Vergleichsliste_arr as $oLetzten20Vergleichsliste) {
        $oLetzten20VergleichslistePos_arr = Shop::DB()->query(
            "SELECT kArtikel, cArtikelName
                FROM tvergleichslistepos
                WHERE kVergleichsliste = " . (int)$oLetzten20Vergleichsliste->kVergleichsliste, 2
        );

        $oLetzten20Vergleichsliste->oLetzten20VergleichslistePos_arr = $oLetzten20VergleichslistePos_arr;
    }
}
// Top Vergleiche
$oTopVergleichsliste_arr = Shop::DB()->query(
    "SELECT tvergleichsliste.dDate, tvergleichslistepos.kArtikel, tvergleichslistepos.cArtikelName, count(tvergleichslistepos.kArtikel) AS nAnzahl
        FROM tvergleichsliste
        JOIN tvergleichslistepos ON tvergleichsliste.kVergleichsliste = tvergleichslistepos.kVergleichsliste
        WHERE DATE_SUB(now(), INTERVAL " . intval($_SESSION['Vergleichsliste']->nZeitFilter) . " DAY) < tvergleichsliste.dDate
        GROUP BY tvergleichslistepos.kArtikel
        ORDER BY nAnzahl DESC
        LIMIT " . intval($_SESSION['Vergleichsliste']->nAnzahl), 2
);
// Top Vergleiche Graph
if (is_array($oTopVergleichsliste_arr) && count($oTopVergleichsliste_arr) > 0) {
    erstelleDiagrammTopVergleiche($oTopVergleichsliste_arr);
}
$oBlaetterNavi = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, $oVergleichAnzahl->nAnzahl, $nAnzahlProSeite);

$smarty->assign('Letzten20Vergleiche', $oLetzten20Vergleichsliste_arr)
       ->assign('TopVergleiche', $oTopVergleichsliste_arr)
       ->assign('oBlaetterNavi', $oBlaetterNavi)
       ->assign('sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('vergleichsliste.tpl');

/**
 * @param array $oTopVergleichsliste_arr
 */
function erstelleDiagrammTopVergleiche($oTopVergleichsliste_arr)
{
    unset($_SESSION['oGraphData_arr']);
    unset($_SESSION['nYmax']);
    unset($_SESSION['nDiagrammTyp']);

    $oGraphData_arr = array();

    if (is_array($oTopVergleichsliste_arr) && count($oTopVergleichsliste_arr) > 0) {
        $nYmax_arr                = array(); // Y-Achsen Werte um spaeter den Max Wert zu erlangen
        $_SESSION['nDiagrammTyp'] = 4;

        foreach ($oTopVergleichsliste_arr as $i => $oTopVergleichsliste) {
            $oTop               = new stdClass();
            $oTop->nAnzahl      = $oTopVergleichsliste->nAnzahl;
            $oTop->cArtikelName = checkName($oTopVergleichsliste->cArtikelName);
            $oGraphData_arr[]   = $oTop;
            $nYmax_arr[]        = $oTopVergleichsliste->nAnzahl;
            unset($oTop);

            if ($i >= intval($_SESSION['Vergleichsliste']->nAnzahl)) {
                break;
            }
        }
        // Naechst hoehere Zahl berechnen fuer die Y-Balkenbeschriftung
        if (count($nYmax_arr) > 0) {
            $fMax = floatval(max($nYmax_arr));
            if ($fMax > 10) {
                $temp  = pow(10, floor(log10($fMax)));
                $nYmax = ceil($fMax / $temp) * $temp;
            } else {
                $nYmax = 10;
            }

            $_SESSION['nYmax'] = $nYmax;
        }

        $_SESSION['oGraphData_arr'] = $oGraphData_arr;
    }
}

/**
 * Hilfsfunktion zur Regulierung der X-Achsen Werte
 *
 * @param string $cName
 * @return string
 */
function checkName($cName)
{
    $cName = stripslashes(trim(str_replace(array(';', '_', '#', '%', '$', ':', '"'), '', $cName)));

    if (strlen($cName) > 20) {
        // Wenn der String laenger als 20 Zeichen ist
        $cName = substr($cName, 0, 20) . '...';
    }

    return $cName;
}
