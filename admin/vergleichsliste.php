<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_COMPARELIST_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$cSetting = '(469, 470)';
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
if (!isset($_SESSION['Vergleichsliste'])) {
    $_SESSION['Vergleichsliste'] = new stdClass();
}
$_SESSION['Vergleichsliste']->nZeitFilter = 1;
$_SESSION['Vergleichsliste']->nAnzahl     = 10;
if (isset($_POST['zeitfilter']) && (int)$_POST['zeitfilter'] === 1) {
    $_SESSION['Vergleichsliste']->nZeitFilter = isset($_POST['nZeitFilter'])
        ? (int)$_POST['nZeitFilter']
        : 0;
    $_SESSION['Vergleichsliste']->nAnzahl     = isset($_POST['nAnzahl'])
        ? (int)$_POST['nAnzahl']
        : 0;
}

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1 && FormHelper::validateToken()) {
    $oConfig_arr = Shop::Container()->getDB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE (
                kEinstellungenConf IN " . $cSetting . " 
                OR kEinstellungenSektion = " . CONF_VERGLEICHSLISTE . "
                )
                AND cConf = 'Y'
            ORDER BY nSort",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        $aktWert                        = new stdClass();
        $aktWert->cWert                 = $_POST[$oConfig_arr[$i]->cWertName];
        $aktWert->cName                 = $oConfig_arr[$i]->cWertName;
        $aktWert->kEinstellungenSektion = $oConfig_arr[$i]->kEinstellungenSektion;
        switch ($oConfig_arr[$i]->cInputTyp) {
            case 'kommazahl':
                $aktWert->cWert = (float)$aktWert->cWert;
                break;
            case 'zahl':
            case 'number':
                $aktWert->cWert = (int)$aktWert->cWert;
                break;
            case 'text':
                $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                break;
        }
        Shop::Container()->getDB()->delete(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [(int)$oConfig_arr[$i]->kEinstellungenSektion, $oConfig_arr[$i]->cWertName]
        );
        Shop::Container()->getDB()->insert('teinstellungen', $aktWert);
    }

    $cHinweis .= 'Ihre Einstellungen wurden übernommen.';
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
}

$oConfig_arr = Shop::Container()->getDB()->query(
    'SELECT *
        FROM teinstellungenconf
        WHERE (
                kEinstellungenConf IN ' . $cSetting . ' 
                OR kEinstellungenSektion = ' . CONF_VERGLEICHSLISTE . '
               )
        ORDER BY nSort',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }
    $oSetValue = Shop::Container()->getDB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        (int)$oConfig_arr[$i]->kEinstellungenSektion,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
}

$smarty->assign('oConfig_arr', $oConfig_arr);
// Max Anzahl Vergleiche
$oVergleichAnzahl = Shop::Container()->getDB()->query(
    'SELECT COUNT(*) AS nAnzahl
        FROM tvergleichsliste',
    \DB\ReturnType::SINGLE_OBJECT
);
// Pagination
$oPagination = (new Pagination())
    ->setItemCount($oVergleichAnzahl->nAnzahl)
    ->assemble();
// Letzten 20 Vergleiche
$oLetzten20Vergleichsliste_arr = Shop::Container()->getDB()->query(
    "SELECT kVergleichsliste, DATE_FORMAT(dDate, '%d.%m.%Y  %H:%i') AS Datum
        FROM tvergleichsliste
        ORDER BY dDate DESC
        LIMIT " . $oPagination->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

if (is_array($oLetzten20Vergleichsliste_arr) && count($oLetzten20Vergleichsliste_arr) > 0) {
    $oLetzten20VergleichslistePos_arr = [];
    foreach ($oLetzten20Vergleichsliste_arr as $oLetzten20Vergleichsliste) {
        $oLetzten20VergleichslistePos_arr = Shop::Container()->getDB()->selectAll(
            'tvergleichslistepos',
            'kVergleichsliste',
            (int)$oLetzten20Vergleichsliste->kVergleichsliste,
            'kArtikel, cArtikelName'
        );
        $oLetzten20Vergleichsliste->oLetzten20VergleichslistePos_arr = $oLetzten20VergleichslistePos_arr;
    }
}
// Top Vergleiche
$oTopVergleichsliste_arr = Shop::Container()->getDB()->query(
    'SELECT tvergleichsliste.dDate, tvergleichslistepos.kArtikel, 
        tvergleichslistepos.cArtikelName, COUNT(tvergleichslistepos.kArtikel) AS nAnzahl
        FROM tvergleichsliste
        JOIN tvergleichslistepos 
            ON tvergleichsliste.kVergleichsliste = tvergleichslistepos.kVergleichsliste
        WHERE DATE_SUB(NOW(), INTERVAL ' . (int)$_SESSION['Vergleichsliste']->nZeitFilter . ' DAY) 
            < tvergleichsliste.dDate
        GROUP BY tvergleichslistepos.kArtikel
        ORDER BY nAnzahl DESC
        LIMIT ' . (int)$_SESSION['Vergleichsliste']->nAnzahl,
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
// Top Vergleiche Graph
if (is_array($oTopVergleichsliste_arr) && count($oTopVergleichsliste_arr) > 0) {
    erstelleDiagrammTopVergleiche($oTopVergleichsliste_arr);
}

$smarty->assign('Letzten20Vergleiche', $oLetzten20Vergleichsliste_arr)
    ->assign('TopVergleiche', $oTopVergleichsliste_arr)
    ->assign('oPagination', $oPagination)
    ->assign('sprachen', Sprache::getAllLanguages())
    ->assign('hinweis', $cHinweis)
    ->assign('fehler', $cFehler)
    ->display('vergleichsliste.tpl');

/**
 * @param array $oTopVergleichsliste_arr
 */
function erstelleDiagrammTopVergleiche($oTopVergleichsliste_arr)
{
    unset($_SESSION['oGraphData_arr'], $_SESSION['nYmax'], $_SESSION['nDiagrammTyp']);

    $oGraphData_arr = [];
    if (is_array($oTopVergleichsliste_arr) && count($oTopVergleichsliste_arr) > 0) {
        $nYmax_arr                = []; // Y-Achsen Werte um spaeter den Max Wert zu erlangen
        $_SESSION['nDiagrammTyp'] = 4;

        foreach ($oTopVergleichsliste_arr as $i => $oTopVergleichsliste) {
            $oTop               = new stdClass();
            $oTop->nAnzahl      = $oTopVergleichsliste->nAnzahl;
            $oTop->cArtikelName = checkName($oTopVergleichsliste->cArtikelName);
            $oGraphData_arr[]   = $oTop;
            $nYmax_arr[]        = $oTopVergleichsliste->nAnzahl;
            unset($oTop);

            if ($i >= (int)$_SESSION['Vergleichsliste']->nAnzahl) {
                break;
            }
        }
        // Naechst hoehere Zahl berechnen fuer die Y-Balkenbeschriftung
        if (count($nYmax_arr) > 0) {
            $fMax = (float)max($nYmax_arr);
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
    $cName = stripslashes(trim(str_replace([';', '_', '#', '%', '$', ':', '"'], '', $cName)));

    if (strlen($cName) > 20) {
        // Wenn der String laenger als 20 Zeichen ist
        $cName = substr($cName, 0, 20) . '...';
    }

    return $cName;
}
