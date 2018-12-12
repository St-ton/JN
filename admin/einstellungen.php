<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';
/** @global Smarty\JTLSmarty $smarty */
/** @global AdminAccount $oAccount */
$kSektion = isset($_REQUEST['kSektion']) ? (int)$_REQUEST['kSektion'] : 0;
$bSuche   = isset($_REQUEST['einstellungen_suchen']) && (int)$_REQUEST['einstellungen_suchen'] === 1;

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
        // Sucheinstellungen haben eigene Logik
        header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'sucheinstellungen.php');
        exit;
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

$standardwaehrung = Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y');
$cHinweis         = '';
$cFehler          = '';
$section          = null;
$step             = 'uebersicht';
$oSections        = [];
if ($kSektion > 0) {
    $step    = 'einstellungen bearbeiten';
    $section = Shop::Container()->getDB()->select('teinstellungensektion', 'kEinstellungenSektion', $kSektion);
    $smarty->assign('kEinstellungenSektion', $section->kEinstellungenSektion);
} else {
    $section = Shop::Container()->getDB()->select('teinstellungensektion', 'kEinstellungenSektion', CONF_GLOBAL);
    $smarty->assign('kEinstellungenSektion', CONF_GLOBAL);
}

if ($bSuche) {
    $step = 'einstellungen bearbeiten';
}
if (isset($_POST['einstellungen_bearbeiten'])
    && (int)$_POST['einstellungen_bearbeiten'] === 1
    && $kSektion > 0
    && FormHelper::validateToken()
) {
    // Einstellungssuche
    $oSQL = new stdClass();
    if ($bSuche) {
        $oSQL = bearbeiteEinstellungsSuche($_REQUEST['cSuche'], true);
    }
    if (!isset($oSQL->cWHERE)) {
        $oSQL->cWHERE = '';
    }
    $step = 'einstellungen bearbeiten';
    $Conf = [];
    if (strlen($oSQL->cWHERE) > 0) {
        $Conf = $oSQL->oEinstellung_arr;
        $smarty->assign('cSearch', $oSQL->cSearch);
    } else {
        $section = Shop::Container()->getDB()->select('teinstellungensektion', 'kEinstellungenSektion', $kSektion);
        $Conf    = Shop::Container()->getDB()->query(
            'SELECT *
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = ' . (int)$section->kEinstellungenSektion . "
                    AND cConf = 'Y'
                    AND nModul = 0
                    AND nStandardanzeigen = 1
                    {$oSQL->cWHERE}
                ORDER BY nSort",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    foreach ($Conf as $i => $oConfig) {
        $aktWert  = new stdClass();
        $oSection = SettingSection::getInstance((int)$oConfig->kEinstellungenSektion);
        if (isset($_POST[$Conf[$i]->cWertName])) {
            $aktWert->cWert                 = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName                 = $Conf[$i]->cWertName;
            $aktWert->kEinstellungenSektion = $Conf[$i]->kEinstellungenSektion;
            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
                case 'pass':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            if ($oSection->validate($Conf[$i], $_POST[$Conf[$i]->cWertName])) {
                Shop::Container()->getDB()->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$Conf[$i]->kEinstellungenSektion, $Conf[$i]->cWertName]
                );
                if (is_array($_POST[$Conf[$i]->cWertName])) {
                    foreach ($_POST[$Conf[$i]->cWertName] as $cWert) {
                        $aktWert->cWert = $cWert;
                        Shop::Container()->getDB()->insert('teinstellungen', $aktWert);
                    }
                } else {
                    Shop::Container()->getDB()->insert('teinstellungen', $aktWert);
                }
            }
        }
    }

    Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
    $cHinweis    = 'Die Einstellungen wurden erfolgreich gespeichert.';
    $tagsToFlush = [CACHING_GROUP_OPTION];
    if ($kSektion === 1 || $kSektion === 4 || $kSektion === 5) {
        $tagsToFlush[] = CACHING_GROUP_CORE;
        $tagsToFlush[] = CACHING_GROUP_ARTICLE;
        $tagsToFlush[] = CACHING_GROUP_CATEGORY;
    } elseif ($kSektion === 8) {
        $tagsToFlush[] = CACHING_GROUP_BOX;
    }
    Shop::Container()->getCache()->flushTags($tagsToFlush);
    Shopsetting::getInstance()->reset();
}

if ($step === 'uebersicht') {
    $sections     = Shop::Container()->getDB()->query(
        'SELECT * 
            FROM teinstellungensektion 
            ORDER BY kEinstellungenSektion',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $sectionCount = count($sections);
    for ($i = 0; $i < $sectionCount; $i++) {
        $anz_einstellunen = Shop::Container()->getDB()->queryPrepared(
            "SELECT COUNT(*) AS anz
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = :sid
                    AND cConf = 'Y'
                    AND nStandardAnzeigen = 1
                    AND nModul = 0",
            ['sid' => (int)$sections[$i]->kEinstellungenSektion],
            \DB\ReturnType::SINGLE_OBJECT
        );

        $sections[$i]->anz = $anz_einstellunen->anz;
    }
    $smarty->assign('Sektionen', $sections);
}
if ($step === 'einstellungen bearbeiten') {
    $Conf = [];
    $oSQL = new stdClass();
    if ($bSuche) {
        $oSQL = bearbeiteEinstellungsSuche($_REQUEST['cSuche']);
    }
    if (!isset($oSQL->cWHERE)) {
        $oSQL->cWHERE = '';
    }
    $Conf = [];
    if (strlen($oSQL->cWHERE) > 0) {
        $Conf = $oSQL->oEinstellung_arr;
        $smarty->assign('cSearch', $oSQL->cSearch)
               ->assign('cSuche', $oSQL->cSuche);
    } else {
        $Conf = Shop::Container()->getDB()->query(
            'SELECT *
                FROM teinstellungenconf
                WHERE nModul = 0 
                    AND nStandardAnzeigen = 1
                    AND kEinstellungenSektion = ' . (int)$section->kEinstellungenSektion . ' ' .
                $oSQL->cWHERE . '
                ORDER BY nSort',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    foreach ($Conf as $config) {
        $config->kEinstellungenConf    = (int)$config->kEinstellungenConf;
        $config->kEinstellungenSektion = (int)$config->kEinstellungenSektion;
        $config->nStandardAnzeigen     = (int)$config->nStandardAnzeigen;
        $config->nSort                 = (int)$config->nSort;
        $config->nModul                = (int)$config->nModul;
        $oSection                      = SettingSection::getInstance((int)$config->kEinstellungenSektion);
        //@ToDo: Setting 492 is the only one listbox at the moment.
        //But In special case of setting 492 values come from kKundengruppe instead of teinstellungenconfwerte
        if ($config->cInputTyp === 'listbox' && $config->kEinstellungenConf === 492) {
            $config->ConfWerte = Shop::Container()->getDB()->query(
                'SELECT kKundengruppe AS cWert, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        } elseif (in_array($config->cInputTyp, ['selectbox', 'listbox'], true)) {
            $config->ConfWerte = Shop::Container()->getDB()->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$config->kEinstellungenConf,
                '*',
                'nSort'
            );
        }
        if ($config->cInputTyp === 'listbox') {
            $setValue              = Shop::Container()->getDB()->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [(int)$config->kEinstellungenSektion, $config->cWertName]
            );
            $config->gesetzterWert = $setValue;
        } else {
            $setValue              = Shop::Container()->getDB()->select(
                'teinstellungen',
                'kEinstellungenSektion',
                (int)$config->kEinstellungenSektion,
                'cName',
                $config->cWertName
            );
            $config->gesetzterWert = isset($setValue->cWert)
                ? StringHandler::htmlentities($setValue->cWert)
                : null;
        }
        $oSection->setValue($config, $setValue);
        $oSections[(int)$config->kEinstellungenSektion] = $oSection;
    }

    $smarty->assign('Sektion', $section)
           ->assign('Conf', $Conf)
           ->assign('oSections', $oSections);
}

$smarty->configLoad('german.conf', 'einstellungen')
       ->assign('cPrefDesc', $smarty->getConfigVars('prefDesc' . $kSektion))
       ->assign('cPrefURL', $smarty->getConfigVars('prefURL' . $kSektion))
       ->assign('step', $step)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('waehrung', $standardwaehrung->cName)
       ->display('einstellungen.tpl');
