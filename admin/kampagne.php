<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Kampagne;
use JTL\Shop;
use JTL\Pagination\Pagination;
use JTL\DB\ReturnType;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('STATS_CAMPAIGN_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kampagne_inc.php';

$kKampagne    = 0;
$kKampagneDef = 0;
$cStamp       = '';
$step         = 'kampagne_uebersicht';
$alertHelper  = Shop::Container()->getAlertService();

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

$cDatumNow_arr = Date::getDateParts(date('Y-m-d H:i:s'));
// Tab
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
if (Request::verifyGPCDataInt('neu') === 1 && Form::validateToken()) {
    $step = 'kampagne_erstellen';
} elseif (Request::verifyGPCDataInt('editieren') === 1
    && Request::verifyGPCDataInt('kKampagne') > 0
    && Form::validateToken()
) {
    // Editieren
    $step      = 'kampagne_erstellen';
    $kKampagne = Request::verifyGPCDataInt('kKampagne');
} elseif (Request::verifyGPCDataInt('detail') === 1
    && Request::verifyGPCDataInt('kKampagne') > 0
    && Form::validateToken()
) {
    // Detail
    $step      = 'kampagne_detail';
    $kKampagne = Request::verifyGPCDataInt('kKampagne');
    // Zeitraum / Ansicht
    setzeDetailZeitraum($cDatumNow_arr);
} elseif (Request::verifyGPCDataInt('defdetail') === 1
    && Request::verifyGPCDataInt('kKampagne') > 0
    && Request::verifyGPCDataInt('kKampagneDef') > 0
    && Form::validateToken()
) { // Def Detail
    $step         = 'kampagne_defdetail';
    $kKampagne    = Request::verifyGPCDataInt('kKampagne');
    $kKampagneDef = Request::verifyGPCDataInt('kKampagneDef');
    $cStamp       = Request::verifyGPDataString('cStamp');
} elseif (Request::verifyGPCDataInt('erstellen_speichern') === 1 && Form::validateToken()) {
    // Speichern / Editieren
    $oKampagne             = new Kampagne();
    $oKampagne->cName      = $_POST['cName'];
    $oKampagne->cParameter = $_POST['cParameter'];
    $oKampagne->cWert      = $_POST['cWert'];
    $oKampagne->nDynamisch = $_POST['nDynamisch'];
    $oKampagne->nAktiv     = $_POST['nAktiv'];
    $oKampagne->dErstellt  = 'NOW()';

    // Editieren
    if (Request::verifyGPCDataInt('kKampagne') > 0) {
        $oKampagne->kKampagne = Request::verifyGPCDataInt('kKampagne');
    }

    $nReturnValue = speicherKampagne($oKampagne);

    if ($nReturnValue === 1) {
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('successCampaignSave'), 'successCampaignSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, mappeFehlerCodeSpeichern($nReturnValue), 'campaignError');
        $smarty->assign('oKampagne', $oKampagne);
        $step = 'kampagne_erstellen';
    }
} elseif (Request::verifyGPCDataInt('delete') === 1 && Form::validateToken()) {
    // Loeschen
    if (isset($_POST['kKampagne']) && is_array($_POST['kKampagne']) && count($_POST['kKampagne']) > 0) {
        $nReturnValue = loescheGewaehlteKampagnen($_POST['kKampagne']);

        if ($nReturnValue == 1) {
            $alertHelper->addAlert(Alert::TYPE_NOTE, __('successCampaignDelete'), 'successCampaignDelete');
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneCampaign'), 'errorAtLeastOneCampaign');
    }
} elseif (Request::verifyGPCDataInt('nAnsicht') > 0) { // Ansicht
    $_SESSION['Kampagne']->nAnsicht = Request::verifyGPCDataInt('nAnsicht');
} elseif (Request::verifyGPCDataInt('nStamp') === -1 || Request::verifyGPCDataInt('nStamp') === 1) {
    // Vergangenheit
    if (Request::verifyGPCDataInt('nStamp') === -1) {
        $_SESSION['Kampagne']->cStamp = gibStamp($_SESSION['Kampagne']->cStamp, -1, $_SESSION['Kampagne']->nAnsicht);
    } elseif (Request::verifyGPCDataInt('nStamp') === 1) {
        // Zukunft
        $_SESSION['Kampagne']->cStamp = gibStamp($_SESSION['Kampagne']->cStamp, 1, $_SESSION['Kampagne']->nAnsicht);
    }
} elseif (Request::verifyGPCDataInt('nSort') > 0) { // Sortierung
    // ASC / DESC
    if ($_SESSION['Kampagne']->nSort == Request::verifyGPCDataInt('nSort')) {
        if ($_SESSION['Kampagne']->cSort === 'ASC') {
            $_SESSION['Kampagne']->cSort = 'DESC';
        } else {
            $_SESSION['Kampagne']->cSort = 'ASC';
        }
    }

    $_SESSION['Kampagne']->nSort = Request::verifyGPCDataInt('nSort');
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
    if (mb_strlen($cStamp) === 0) {
        $cStamp = checkGesamtStatZeitParam();
    }

    if ($kKampagne > 0 && $kKampagneDef > 0 && mb_strlen($cStamp) > 0) {
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
            ReturnType::ARRAY_OF_OBJECTS
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

$dates = Date::getDateParts($_SESSION['Kampagne']->cStamp);
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
       ->assign('step', $step)
       ->display('kampagne.tpl');
