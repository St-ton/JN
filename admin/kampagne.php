<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Kampagne;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('STATS_CAMPAIGN_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kampagne_inc.php';

$campaignID   = 0;
$definitionID = 0;
$stamp        = '';
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

$dateNow = Date::getDateParts(date('Y-m-d H:i:s'));
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
    $step       = 'kampagne_erstellen';
    $campaignID = Request::verifyGPCDataInt('kKampagne');
} elseif (Request::verifyGPCDataInt('detail') === 1
    && Request::verifyGPCDataInt('kKampagne') > 0
    && Form::validateToken()
) {
    // Detail
    $step       = 'kampagne_detail';
    $campaignID = Request::verifyGPCDataInt('kKampagne');
    // Zeitraum / Ansicht
    setzeDetailZeitraum($dateNow);
} elseif (Request::verifyGPCDataInt('defdetail') === 1
    && Request::verifyGPCDataInt('kKampagne') > 0
    && Request::verifyGPCDataInt('kKampagneDef') > 0
    && Form::validateToken()
) { // Def Detail
    $step         = 'kampagne_defdetail';
    $campaignID   = Request::verifyGPCDataInt('kKampagne');
    $definitionID = Request::verifyGPCDataInt('kKampagneDef');
    $stamp        = Request::verifyGPDataString('cStamp');
} elseif (Request::verifyGPCDataInt('erstellen_speichern') === 1 && Form::validateToken()) {
    // Speichern / Editieren
    $campaign             = new Kampagne();
    $campaign->cName      = $_POST['cName'];
    $campaign->cParameter = $_POST['cParameter'];
    $campaign->cWert      = $_POST['cWert'];
    $campaign->nDynamisch = $_POST['nDynamisch'];
    $campaign->nAktiv     = $_POST['nAktiv'];
    $campaign->dErstellt  = 'NOW()';

    // Editieren
    if (Request::verifyGPCDataInt('kKampagne') > 0) {
        $campaign->kKampagne = Request::verifyGPCDataInt('kKampagne');
    }

    $res = speicherKampagne($campaign);

    if ($res === 1) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCampaignSave'), 'successCampaignSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, mappeFehlerCodeSpeichern($res), 'campaignError');
        $smarty->assign('oKampagne', $campaign);
        $step = 'kampagne_erstellen';
    }
} elseif (Request::verifyGPCDataInt('delete') === 1 && Form::validateToken()) {
    // Loeschen
    if (GeneralObject::hasCount('kKampagne', $_POST)) {
        $res = loescheGewaehlteKampagnen($_POST['kKampagne']);
        if ($res === 1) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCampaignDelete'), 'successCampaignDelete');
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
    $campaigns   = holeAlleKampagnen(true, false);
    $definitions = holeAlleKampagnenDefinitionen();
    $maxKey      = 0;
    if (is_array($campaigns) && count($campaigns) > 0) {
        $members = array_keys($campaigns);
        $maxKey  = $members[count($members) - 1];
    }

    $smarty->assign('nGroessterKey', $maxKey)
        ->assign('oKampagne_arr', $campaigns)
        ->assign('oKampagneDef_arr', $definitions)
        ->assign('oKampagneStat_arr', holeKampagneGesamtStats($campaigns, $definitions));
} elseif ($step === 'kampagne_erstellen') { // Erstellen / Editieren
    if ($campaignID > 0) {
        $smarty->assign('oKampagne', holeKampagne($campaignID));
    }
} elseif ($step === 'kampagne_detail') { // Detailseite
    if ($campaignID > 0) {
        $campaigns   = holeAlleKampagnen(true, false);
        $definitions = holeAlleKampagnenDefinitionen();
        if (!isset($_SESSION['Kampagne']->oKampagneDetailGraph)) {
            $_SESSION['Kampagne']->oKampagneDetailGraph = new stdClass();
        }
        $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDef_arr = $definitions;
        $_SESSION['nDiagrammTyp']                                     = 5;

        $stats = holeKampagneDetailStats($campaignID, $definitions);
        // Highchart
        $charts = [];
        for ($i = 1; $i <= 10; $i++) {
            $charts[$i] = PrepareLineChartKamp($stats, $i);
        }

        $smarty->assign('TypeNames', GetTypes())
            ->assign('Charts', $charts)
            ->assign('oKampagne', holeKampagne($campaignID))
            ->assign('oKampagneStat_arr', $stats)
            ->assign('oKampagne_arr', $campaigns)
            ->assign('oKampagneDef_arr', $definitions)
            ->assign('nRand', time());
    }
} elseif ($step === 'kampagne_defdetail') { // DefDetailseite
    if (mb_strlen($stamp) === 0) {
        $stamp = checkGesamtStatZeitParam();
    }

    if ($campaignID > 0 && $definitionID > 0 && mb_strlen($stamp) > 0) {
        $definition = holeKampagneDef($definitionID);
        $members    = [];
        $stampText  = '';
        $select     = '';
        $where      = '';
        baueDefDetailSELECTWHERE($select, $where, $stamp);

        $stats = Shop::Container()->getDB()->query(
            'SELECT kKampagne, kKampagneDef, kKey ' . $select . '
                FROM tkampagnevorgang
                ' . $where . '
                    AND kKampagne = ' . (int)$campaignID . '
                    AND kKampagneDef = ' . (int)$definition->kKampagneDef,
            ReturnType::ARRAY_OF_OBJECTS
        );

        $paginationDefinitionDetail = (new Pagination('defdetail'))
            ->setItemCount(count($stats))
            ->assemble();
        $campaignStats              = holeKampagneDefDetailStats(
            $campaignID,
            $definition,
            $stamp,
            $stampText,
            $members,
            ' LIMIT ' . $paginationDefinitionDetail->getLimitSQL()
        );

        $smarty->assign('oPagiDefDetail', $paginationDefinitionDetail)
            ->assign('oKampagne', holeKampagne($campaignID))
            ->assign('oKampagneStat_arr', $campaignStats)
            ->assign('oKampagneDef', $definition)
            ->assign('cMember_arr', $members)
            ->assign('cStampText', $stampText)
            ->assign('cStamp', $stamp)
            ->assign('nGesamtAnzahlDefDetail', count($stats));
    }
}

$dates = Date::getDateParts($_SESSION['Kampagne']->cStamp);
switch ((int)$_SESSION['Kampagne']->nAnsicht) {
    case 1:    // Monat
        $timeSpan   = '01.' . $dates['cMonat'] . '.' . $dates['cJahr'] . ' - ' .
            date('t', mktime(0, 0, 0, (int)$dates['cMonat'], 1, (int)$dates['cJahr'])) .
            '.' . $dates['cMonat'] . '.' . $dates['cJahr'];
        $greaterNow = (int)$dateNow['cMonat'] === (int)$dates['cMonat']
            && (int)$dateNow['cJahr'] === (int)$dates['cJahr'];
        $smarty->assign('cZeitraum', $timeSpan)
            ->assign('cZeitraumParam', base64_encode($timeSpan))
            ->assign('bGreaterNow', $greaterNow);
        break;
    case 2:    // Woche
        $dateParts  = ermittleDatumWoche($dates['cJahr'] . '-' . $dates['cMonat'] . '-' . $dates['cTag']);
        $timeSpan   = date('d.m.Y', $dateParts[0]) . ' - ' . date('d.m.Y', $dateParts[1]);
        $greaterNow = date('Y-m-d', $dateParts[1]) >= $dateNow['cDatum'];
        $smarty->assign('cZeitraum', $timeSpan)
            ->assign('cZeitraumParam', base64_encode($timeSpan))
            ->assign('bGreaterNow', $greaterNow);
        break;
    case 3:    // Tag
        $timeSpan   = $dates['cTag'] . '.' . $dates['cMonat'] . '.' . $dates['cJahr'];
        $greaterNow = (int)$dateNow['cTag'] === (int)$dates['cTag']
            && (int)$dateNow['cMonat'] === (int)$dates['cMonat']
            && (int)$dateNow['cJahr'] === (int)$dates['cJahr'];
        $smarty->assign('cZeitraum', $timeSpan)
            ->assign('cZeitraumParam', base64_encode($timeSpan))
            ->assign('bGreaterNow', $greaterNow);
        break;
}

$smarty->assign('PFAD_ADMIN', PFAD_ADMIN)
    ->assign('PFAD_TEMPLATES', PFAD_TEMPLATES)
    ->assign('PFAD_GFX', PFAD_GFX)
    ->assign('step', $step)
    ->display('kampagne.tpl');
