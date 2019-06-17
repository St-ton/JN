<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

Shop::Container()->getGetText()->loadConfigLocales(true, true);

$oAccount->permission('MODULE_COMPARELIST_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$cSetting    = '(469, 470)';
$alertHelper = Shop::Container()->getAlertService();
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
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

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1 && Form::validateToken()) {
    $oConfig_arr = Shop::Container()->getDB()->query(
        'SELECT *
            FROM teinstellungenconf
            WHERE (
                kEinstellungenConf IN ' . $cSetting . ' 
                OR kEinstellungenSektion = ' . CONF_VERGLEICHSLISTE . "
                )
                AND cConf = 'Y'
            ORDER BY nSort",
        ReturnType::ARRAY_OF_OBJECTS
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
                $aktWert->cWert = mb_substr($aktWert->cWert, 0, 255);
                break;
        }
        Shop::Container()->getDB()->delete(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [(int)$oConfig_arr[$i]->kEinstellungenSektion, $oConfig_arr[$i]->cWertName]
        );
        Shop::Container()->getDB()->insert('teinstellungen', $aktWert);
    }

    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
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
    ReturnType::ARRAY_OF_OBJECTS
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
        Shop::Container()->getGetText()->localizeConfigValues($oConfig_arr[$i], $oConfig_arr[$i]->ConfWerte);
    }
    $oSetValue                      = Shop::Container()->getDB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        (int)$oConfig_arr[$i]->kEinstellungenSektion,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
    Shop::Container()->getGetText()->localizeConfig($oConfig_arr[$i]);
}

$oVergleichAnzahl = Shop::Container()->getDB()->query(
    'SELECT COUNT(*) AS nAnzahl
        FROM tvergleichsliste',
    ReturnType::SINGLE_OBJECT
);
$oPagination      = (new Pagination())
    ->setItemCount($oVergleichAnzahl->nAnzahl)
    ->assemble();
$last20           = Shop::Container()->getDB()->query(
    "SELECT kVergleichsliste, DATE_FORMAT(dDate, '%d.%m.%Y  %H:%i') AS Datum
        FROM tvergleichsliste
        ORDER BY dDate DESC
        LIMIT " . $oPagination->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);

if (is_array($last20) && count($last20) > 0) {
    $positions = [];
    foreach ($last20 as $oLetzten20Vergleichsliste) {
        $positions                                                   = Shop::Container()->getDB()->selectAll(
            'tvergleichslistepos',
            'kVergleichsliste',
            (int)$oLetzten20Vergleichsliste->kVergleichsliste,
            'kArtikel, cArtikelName'
        );
        $oLetzten20Vergleichsliste->oLetzten20VergleichslistePos_arr = $positions;
    }
}
$topComparisons = Shop::Container()->getDB()->query(
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
    ReturnType::ARRAY_OF_OBJECTS
);
if (is_array($topComparisons) && count($topComparisons) > 0) {
    erstelleDiagrammTopVergleiche($topComparisons);
}

$smarty->assign('Letzten20Vergleiche', $last20)
       ->assign('TopVergleiche', $topComparisons)
       ->assign('oPagination', $oPagination)
       ->assign('oConfig_arr', $oConfig_arr)
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
                $temp  = 10 ** floor(log10($fMax));
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

    if (mb_strlen($cName) > 20) {
        // Wenn der String laenger als 20 Zeichen ist
        $cName = mb_substr($cName, 0, 20) . '...';
    }

    return $cName;
}
