<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';

$kSektion = verifyGPCDataInteger('kSektion');
$bSuche   = verifyGPCDataInteger('einstellungen_suchen');

if ($bSuche) {
    $oAccount->permission('SETTINGS_SEARCH_VIEW', true, true);
}

switch ($kSektion) {
    case 1:
        $oAccount->permission('SETTINGS_GLOBAL_VIEW', true, true);
        break;
    case 2:
        $oAccount->permission('SETTINGS_STARTPAGE_VIEW', true, true);
        break;
    case 3:
        $oAccount->permission('SETTINGS_EMAILS_VIEW', true, true);
        break;
    case 4:
        $oAccount->permission('SETTINGS_ARTICLEOVERVIEW_VIEW', true, true);
        break;
    case 5:
        $oAccount->permission('SETTINGS_ARTICLEDETAILS_VIEW', true, true);
        break;
    case 6:
        $oAccount->permission('SETTINGS_CUSTOMERFORM_VIEW', true, true);
        break;
    case 7:
        $oAccount->permission('SETTINGS_BASKET_VIEW', true, true);
        break;
    case 8:
        $oAccount->permission('SETTINGS_BOXES_VIEW', true, true);
        break;
    case 9:
        $oAccount->permission('SETTINGS_IMAGES_VIEW', true, true);
        break;
    default:
        $oAccount->redirectOnFailure();
        break;
}

$standardwaehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard = 'Y'", 1);
$cHinweis         = '';
$cFehler          = '';
$Sektion          = null;
$step             = 'uebersicht';
if (verifyGPCDataInteger('kSektion') > 0) {
    $step    = 'einstellungen bearbeiten';
    $Sektion = Shop::DB()->query("
        SELECT *
            FROM teinstellungensektion
            WHERE kEinstellungenSektion = " . verifyGPCDataInteger('kSektion'), 1
    );
    $smarty->assign('kEinstellungenSektion', $Sektion->kEinstellungenSektion);
} else {
    $Sektion = Shop::DB()->query("
        SELECT *
            FROM teinstellungensektion
            WHERE kEinstellungenSektion = 1", 1
    );
    $smarty->assign('kEinstellungenSektion', 1);
}

if (verifyGPCDataInteger('einstellungen_suchen') === 1) {
    $step = 'einstellungen bearbeiten';
}

if (isset($_POST['einstellungen_bearbeiten']) && intval($_POST['einstellungen_bearbeiten']) === 1 && verifyGPCDataInteger('kSektion') > 0 && validateToken()) {
    // Einstellungssuche
    $oSQL = new stdClass();
    if (verifyGPCDataInteger('einstellungen_suchen') === 1) {
        $oSQL = bearbeiteEinstellungsSuche(verifyGPDataString('cSuche'), true);
    }
    if (!isset($oSQL->cWHERE)) {
        $oSQL->cWHERE = '';
    }
    $step = 'einstellungen bearbeiten';
    $Conf = array();
    if (strlen($oSQL->cWHERE) > 0) {
        $Conf = $oSQL->oEinstellung_arr;
        $smarty->assign('cSearch', $oSQL->cSearch);
    } else {
        $Sektion = Shop::DB()->query("
            SELECT *
                FROM teinstellungensektion
                WHERE kEinstellungenSektion = " . verifyGPCDataInteger('kSektion'), 1
        );
        $Conf = Shop::DB()->query("
            SELECT *
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = " . (int)$Sektion->kEinstellungenSektion . "
                    AND cConf = 'Y'
                    AND nModul = 0 " . $oSQL->cWHERE . "
                ORDER BY nSort", 2
        );
    }
    foreach ($Conf as $i => $oConfig) {
        $aktWert = new stdClass();
        if (isset($_POST[$Conf[$i]->cWertName])) {
            $aktWert->cWert                 = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName                 = $Conf[$i]->cWertName;
            $aktWert->kEinstellungenSektion = $Conf[$i]->kEinstellungenSektion;
            switch ($Conf[$i]->cInputTyp) {
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
                case 'pass':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            Shop::DB()->query("
                DELETE
                    FROM teinstellungen
                    WHERE kEinstellungenSektion=" . $Conf[$i]->kEinstellungenSektion . "
                        AND cName='" . $Conf[$i]->cWertName . "'", 4
            );
            if (is_array($_POST[$Conf[$i]->cWertName])) {
                foreach ($_POST[$Conf[$i]->cWertName] as $cWert) {
                    $aktWert->cWert = $cWert;
                    Shop::DB()->insert('teinstellungen', $aktWert);
                }
            } else {
                Shop::DB()->insert('teinstellungen', $aktWert);
            }
        }
    }

    Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
    $cHinweis    = 'Die Einstellungen wurden erfolgreich gespeichert.';
    $tagsToFlush = array(CACHING_GROUP_OPTION);
    if ($kSektion === 1 || $kSektion === 4 || $kSektion === 5) {
        $tagsToFlush[] = CACHING_GROUP_CORE;
        $tagsToFlush[] = CACHING_GROUP_ARTICLE;
        $tagsToFlush[] = CACHING_GROUP_CATEGORY;
    }
    Shop::Cache()->flushTags($tagsToFlush);
    if (Shop::Cache()->isPageCacheEnabled()) {
        $_smarty = new JTLSmarty(true, false, true, 'cache');
        $_smarty->setCachingParams(true)->clearCache(null, 'jtlc');
    }
}

if ($step === 'uebersicht') {
    $Sektionen    = Shop::DB()->query("SELECT * FROM teinstellungensektion ORDER BY kEinstellungenSektion", 2);
    $sectionCount = count($Sektionen);
    for ($i = 0; $i < $sectionCount; $i++) {
        $anz_einstellunen = Shop::DB()->query("
            SELECT count(*) AS anz
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = " . (int)$Sektionen[$i]->kEinstellungenSektion . "
                    AND cConf = 'Y'
                    AND nModul = 0", 1
        );
        $Sektionen[$i]->anz = $anz_einstellunen->anz;
    }
    $smarty->assign('Sektionen', $Sektionen);
}
if ($step === 'einstellungen bearbeiten') {
    // Einstellungssuche
    $Conf = array();
    $oSQL = new stdClass();
    if (verifyGPCDataInteger('einstellungen_suchen') === 1) {
        $oSQL = bearbeiteEinstellungsSuche(verifyGPDataString('cSuche'));
    }
    if (!isset($oSQL->cWHERE)) {
        $oSQL->cWHERE = '';
    }
    $Conf = array();
    if (strlen($oSQL->cWHERE) > 0) {
        $Conf = $oSQL->oEinstellung_arr;
        $smarty->assign('cSearch', $oSQL->cSearch)
               ->assign('cSuche', $oSQL->cSuche);
    } else {
        $Conf = Shop::DB()->query("
            SELECT *
                FROM teinstellungenconf
                WHERE nModul = 0 AND kEinstellungenSektion = " . (int)$Sektion->kEinstellungenSektion . " " . $oSQL->cWHERE . "
                ORDER BY nSort", 2
        );
    }
    $configCount = count($Conf);
    for ($i = 0; $i < $configCount; $i++) {
        /* ToDo: Setting 492 is the only one listbox at the moment.
           But In special case of setting 492 values come from kKundengruppe instead of teinstellungenconfwerte */
        if ($Conf[$i]->cInputTyp === 'listbox' && $Conf[$i]->kEinstellungenConf == 492) {
            $Conf[$i]->ConfWerte = Shop::DB()->query(
                "SELECT kKundengruppe AS cWert, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC", 2
            );
        } elseif (in_array($Conf[$i]->cInputTyp, array('selectbox', 'listbox'), true)) {
            $Conf[$i]->ConfWerte = Shop::DB()->query("
                SELECT *
                    FROM teinstellungenconfwerte
                    WHERE kEinstellungenConf = " . (int)$Conf[$i]->kEinstellungenConf . "
                    ORDER BY nSort", 2
            );
        }

        if ($Conf[$i]->cInputTyp === 'listbox') {
            $setValue = Shop::DB()->query(
                "SELECT cWert
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = " . CONF_BEWERTUNG . "
                        AND cName = '" . $Conf[$i]->cWertName . "'", 2
            );
            $Conf[$i]->gesetzterWert = $setValue;
        } else {
            $setValue = Shop::DB()->query("
                SELECT cWert
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = " . (int)$Conf[$i]->kEinstellungenSektion . "
                    AND cName = '" . $Conf[$i]->cWertName . "'", 1
            );
            $Conf[$i]->gesetzterWert = (isset($setValue->cWert)) ? StringHandler::htmlentities($setValue->cWert) : null;
        }
    }

    $smarty->assign('Sektion', $Sektion)
           ->assign('Conf', $Conf);
}

$k = verifyGPCDataInteger('kSektion');
$smarty->ConfigLoad('german.conf', 'einstellungen')
       ->assign('cPrefDesc', $smarty->getConfigVars('prefDesc' . $k))
       ->assign('cPrefURL', $smarty->getConfigVars('prefURL' . $k))
       ->assign('step', $step)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('waehrung', $standardwaehrung->cName)
       ->display('einstellungen.tpl');
