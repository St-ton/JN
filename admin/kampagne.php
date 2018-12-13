<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\DateHelper;
use Helpers\FormHelper;
use Helpers\RequestHelper;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('STATS_CAMPAIGN_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kampagne_inc.php';

$cHinweis     = '';
$cFehler      = '';
$kKampagne    = 0;
$kKampagneDef = 0;
$cStamp       = '';
$step         = 'kampagne_uebersicht';

// Zeitraum
// 1 = Monat
// 2 = Woche
// 3 = Tag
if (!isset($_SESSION['Kampagne'])) {
    $_SESSION['Kampagne'] = new stdClass();
}
if (!isset($_SESSION['Kampagne']->nAnsicht)) {
    $_SESSION['Kampagne']->nAnsicht = 1;
}
if (!isset($_SESSION['Kampagne']->cStamp)) {
    $_SESSION['Kampagne']->cStamp = date('Y-m-d H:i:s');
}
if (!isset($_SESSION['Kampagne']->nSort)) {
    $_SESSION['Kampagne']->nSort = 0;
}
if (!isset($_SESSION['Kampagne']->cSort)) {
    $_SESSION['Kampagne']->cSort = 'DESC';
}

$cDatumNow_arr = DateHelper::getDateParts(date('Y-m-d H:i:s'));
// Tab
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
if (RequestHelper::verifyGPCDataInt('neu') === 1 && FormHelper::validateToken()) {
    $step = 'kampagne_erstellen';
} elseif (RequestHelper::verifyGPCDataInt('editieren') === 1
    && RequestHelper::verifyGPCDataInt('kKampagne') > 0
    && FormHelper::validateToken()
) {
    // Editieren
    $step      = 'kampagne_erstellen';
    $kKampagne = RequestHelper::verifyGPCDataInt('kKampagne');
} elseif (RequestHelper::verifyGPCDataInt('detail') === 1
    && RequestHelper::verifyGPCDataInt('kKampagne') > 0
    && FormHelper::validateToken()
) {
    // Detail
    $step      = 'kampagne_detail';
    $kKampagne = RequestHelper::verifyGPCDataInt('kKampagne');
    // Zeitraum / Ansicht
    setzeDetailZeitraum($cDatumNow_arr);
} elseif (RequestHelper::verifyGPCDataInt('defdetail') === 1
    && RequestHelper::verifyGPCDataInt('kKampagne') > 0
    && RequestHelper::verifyGPCDataInt('kKampagneDef') > 0
    && FormHelper::validateToken()
) { // Def Detail
    $step         = 'kampagne_defdetail';
    $kKampagne    = RequestHelper::verifyGPCDataInt('kKampagne');
    $kKampagneDef = RequestHelper::verifyGPCDataInt('kKampagneDef');
    $cStamp       = RequestHelper::verifyGPDataString('cStamp');
} elseif (RequestHelper::verifyGPCDataInt('erstellen_speichern') === 1 && FormHelper::validateToken()) {
    // Speichern / Editieren
    $oKampagne             = new Kampagne();
    $oKampagne->cName      = $_POST['cName'];
    $oKampagne->cParameter = $_POST['cParameter'];
    $oKampagne->cWert      = $_POST['cWert'];
    $oKampagne->nDynamisch = $_POST['nDynamisch'];
    $oKampagne->nAktiv     = $_POST['nAktiv'];
    $oKampagne->dErstellt  = 'NOW()';

    // Editieren
    if (RequestHelper::verifyGPCDataInt('kKampagne') > 0) {
        $oKampagne->kKampagne = RequestHelper::verifyGPCDataInt('kKampagne');
    }

    $nReturnValue = speicherKampagne($oKampagne);

    if ($nReturnValue === 1) {
        $cHinweis = 'Ihre Kampagne wurde erfolgreich gespeichert.';
    } else {
        $cFehler = mappeFehlerCodeSpeichern($nReturnValue);
        $smarty->assign('oKampagne', $oKampagne);
        $step = 'kampagne_erstellen';
    }
} elseif (RequestHelper::verifyGPCDataInt('delete') === 1 && FormHelper::validateToken()) {
    // Loeschen
    if (isset($_POST['kKampagne']) && is_array($_POST['kKampagne']) && count($_POST['kKampagne']) > 0) {
        $nReturnValue = loescheGewaehlteKampagnen($_POST['kKampagne']);

        if ($nReturnValue == 1) {
            $cHinweis = 'Ihre ausgewählten Kampagnen wurden erfolgreich gelöscht.';
        }
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens eine Kampagne.';
    }
} elseif (RequestHelper::verifyGPCDataInt('nAnsicht') > 0) { // Ansicht
    $_SESSION['Kampagne']->nAnsicht = RequestHelper::verifyGPCDataInt('nAnsicht');
} elseif (RequestHelper::verifyGPCDataInt('nStamp') === -1 || RequestHelper::verifyGPCDataInt('nStamp') === 1) {
    // Vergangenheit
    if (RequestHelper::verifyGPCDataInt('nStamp') === -1) {
        $_SESSION['Kampagne']->cStamp = gibStamp($_SESSION['Kampagne']->cStamp, -1, $_SESSION['Kampagne']->nAnsicht);
    } elseif (RequestHelper::verifyGPCDataInt('nStamp') === 1) {
        // Zukunft
        $_SESSION['Kampagne']->cStamp = gibStamp($_SESSION['Kampagne']->cStamp, 1, $_SESSION['Kampagne']->nAnsicht);
    }
} elseif (RequestHelper::verifyGPCDataInt('nSort') > 0) { // Sortierung
    // ASC / DESC
    if ($_SESSION['Kampagne']->nSort == RequestHelper::verifyGPCDataInt('nSort')) {
        if ($_SESSION['Kampagne']->cSort === 'ASC') {
            $_SESSION['Kampagne']->cSort = 'DESC';
        } else {
            $_SESSION['Kampagne']->cSort = 'ASC';
        }
    }

    $_SESSION['Kampagne']->nSort = RequestHelper::verifyGPCDataInt('nSort');
}
if ($step === 'kampagne_uebersicht') {
    $oKampagne_arr    = holeAlleKampagnen(true, false);
    $oKampagneDef_arr = holeAlleKampagnenDefinitionen();

    $nGroessterKey = 0;
    if (is_array($oKampagne_arr) && count($oKampagne_arr) > 0) {
        $cMemeber_arr  = array_keys($oKampagne_arr);
        $nGroessterKey = $cMemeber_arr[count($cMemeber_arr) - 1];
    }

    $smarty->assign('nGroessterKey', $nGroessterKey)
           ->assign('oKampagne_arr', $oKampagne_arr)
           ->assign('oKampagneDef_arr', $oKampagneDef_arr)
           ->assign('oKampagneStat_arr', holeKampagneGesamtStats($oKampagne_arr, $oKampagneDef_arr));
} elseif ($step === 'kampagne_erstellen') { // Erstellen / Editieren
    if ($kKampagne > 0) {
        $smarty->assign('oKampagne', holeKampagne($kKampagne));
    }
} elseif ($step === 'kampagne_detail') { // Detailseite
    if ($kKampagne > 0) {
        $oKampagne_arr    = holeAlleKampagnen(true, false);
        $oKampagneDef_arr = holeAlleKampagnenDefinitionen();
        if (!isset($_SESSION['Kampagne']->oKampagneDetailGraph)) {
            $_SESSION['Kampagne']->oKampagneDetailGraph = new stdClass();
        }
        $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDef_arr = $oKampagneDef_arr;
        $_SESSION['nDiagrammTyp']                                     = 5;

        $Stats = holeKampagneDetailStats($kKampagne, $oKampagneDef_arr);
        // Highchart
        $Charts = [];
        for ($i = 1; $i <= 10; $i++) {
            $Charts[$i] = PrepareLineChartKamp($Stats, $i);
        }

        $smarty->assign('TypeNames', GetTypes())
               ->assign('Charts', $Charts)
               ->assign('oKampagne', holeKampagne($kKampagne))
               ->assign('oKampagneStat_arr', $Stats)
               ->assign('oKampagne_arr', $oKampagne_arr)
               ->assign('oKampagneDef_arr', $oKampagneDef_arr)
               ->assign('nRand', time());
    }
} elseif ($step === 'kampagne_defdetail') { // DefDetailseite
    if (strlen($cStamp) === 0) {
        $cStamp = checkGesamtStatZeitParam();
    }

    if ($kKampagne > 0 && $kKampagneDef > 0 && strlen($cStamp) > 0) {
        $oKampagneDef = holeKampagneDef($kKampagneDef);
        $cMember_arr  = [];
        $cStampText   = '';
        $cSQLSELECT   = '';
        $cSQLWHERE    = '';
        baueDefDetailSELECTWHERE($cSQLSELECT, $cSQLWHERE, $cStamp);

        $oStats_arr = Shop::Container()->getDB()->query(
            'SELECT kKampagne, kKampagneDef, kKey ' . $cSQLSELECT . '
                FROM tkampagnevorgang
                ' . $cSQLWHERE . '
                    AND kKampagne = ' . (int)$kKampagne . '
                    AND kKampagneDef = ' . (int)$oKampagneDef->kKampagneDef,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        $oPagiDefDetail    = (new Pagination('defdetail'))
            ->setItemCount(count($oStats_arr))
            ->assemble();
        $oKampagneStat_arr = holeKampagneDefDetailStats(
            $kKampagne,
            $oKampagneDef,
            $cStamp,
            $cStampText,
            $cMember_arr,
            ' LIMIT ' . $oPagiDefDetail->getLimitSQL()
        );

        $smarty->assign('oPagiDefDetail', $oPagiDefDetail)
               ->assign('oKampagne', holeKampagne($kKampagne))
               ->assign('oKampagneStat_arr', $oKampagneStat_arr)
               ->assign('oKampagneDef', $oKampagneDef)
               ->assign('cMember_arr', $cMember_arr)
               ->assign('cStampText', $cStampText)
               ->assign('cStamp', $cStamp)
               ->assign('nGesamtAnzahlDefDetail', count($oStats_arr));
    }
}

$dates = DateHelper::getDateParts($_SESSION['Kampagne']->cStamp);
switch ((int)$_SESSION['Kampagne']->nAnsicht) {
    case 1:    // Monat
        $cZeitraum   = '01.' . $dates['cMonat'] . '.' . $dates['cJahr'] . ' - ' .
            date('t', mktime(0, 0, 0, (int)$dates['cMonat'], 1, (int)$dates['cJahr'])) .
            '.' . $dates['cMonat'] . '.' . $dates['cJahr'];
        $bGreaterNow = (int)$cDatumNow_arr['cMonat'] === (int)$dates['cMonat']
            && (int)$cDatumNow_arr['cJahr'] === (int)$dates['cJahr'];
        $smarty->assign('cZeitraum', $cZeitraum)
               ->assign('cZeitraumParam', base64_encode($cZeitraum))
               ->assign('bGreaterNow', $bGreaterNow);
        break;
    case 2:    // Woche
        $cDate_arr   = ermittleDatumWoche($dates['cJahr'] . '-' . $dates['cMonat'] . '-' . $dates['cTag']);
        $cZeitraum   = date('d.m.Y', $cDate_arr[0]) . ' - ' . date('d.m.Y', $cDate_arr[1]);
        $bGreaterNow = date('Y-m-d', $cDate_arr[1]) >= $cDatumNow_arr['cDatum'];
        $smarty->assign('cZeitraum', $cZeitraum)
               ->assign('cZeitraumParam', base64_encode($cZeitraum))
               ->assign('bGreaterNow', $bGreaterNow);
        break;
    case 3:    // Tag
        $cZeitraum   = $dates['cTag'] . '.' . $dates['cMonat'] . '.' . $dates['cJahr'];
        $bGreaterNow = (int)$cDatumNow_arr['cTag'] === (int)$dates['cTag']
            && (int)$cDatumNow_arr['cMonat'] === (int)$dates['cMonat']
            && (int)$cDatumNow_arr['cJahr'] === (int)$dates['cJahr'];
        $smarty->assign('cZeitraum', $cZeitraum)
               ->assign('cZeitraumParam', base64_encode($cZeitraum))
               ->assign('bGreaterNow', $bGreaterNow);
        break;
}

$smarty->assign('PFAD_ADMIN', PFAD_ADMIN)
       ->assign('PFAD_TEMPLATES', PFAD_TEMPLATES)
       ->assign('PFAD_GFX', PFAD_GFX)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('kampagne.tpl');
