<?php

use JTL\Alert\Alert;
use JTL\Backend\Notification;
use JTL\Backend\NotificationEntry;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/admininclude.php';
require_once __DIR__ . '/includes/einstellungen_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_ARTICLEOVERVIEW_VIEW', true, true);
$sectionID        = CONF_ARTIKELUEBERSICHT;
$conf             = Shop::getSettings([$sectionID]);
$db               = Shop::Container()->getDB();
$standardwaehrung = $db->select('twaehrung', 'cStandard', 'Y');
$mysqlVersion     = $db->getSingleObject("SHOW VARIABLES LIKE 'innodb_version'")->Value;
$step             = 'einstellungen bearbeiten';
$Conf             = [];
$createIndex      = false;
$alertHelper      = Shop::Container()->getAlertService();

Shop::Container()->getGetText()->loadAdminLocale('pages/einstellungen');

if (Request::postInt('einstellungen_bearbeiten') === 1 && Form::validateToken()) {
    $sucheFulltext = in_array(Request::postVar('suche_fulltext', []), ['Y', 'B'], true);
    if ($sucheFulltext) {
        if (version_compare($mysqlVersion, '5.6', '<')) {
            //Volltextindizes werden von MySQL mit InnoDB erst ab Version 5.6 unterstützt
            $_POST['suche_fulltext'] = 'N';
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFulltextSearchMYSQL'), 'errorFulltextSearchMYSQL');
        } else {
            // Bei Volltextsuche die Mindeswortlänge an den DB-Parameter anpassen
            $currentVal = $db->getSingleObject('SELECT @@ft_min_word_len AS ft_min_word_len');
            if (($currentVal->ft_min_word_len ?? $_POST['suche_min_zeichen']) !== $_POST['suche_min_zeichen']) {
                $_POST['suche_min_zeichen'] = $currentVal->ft_min_word_len;
                $alertHelper->addAlert(
                    Alert::TYPE_WARNING,
                    __('errorFulltextSearchMinLen'),
                    'errorFulltextSearchMinLen'
                );
            }
        }
    }

    $shopSettings = Shopsetting::getInstance();
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings($sectionID, $_POST),
        'saveSettings'
    );

    Shop::Container()->getCache()->flushTags(
        [CACHING_GROUP_OPTION, CACHING_GROUP_CORE, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]
    );
    $shopSettings->reset();

    $fulltextChanged = false;
    foreach ([
            'suche_fulltext',
            'suche_prio_name',
            'suche_prio_suchbegriffe',
            'suche_prio_artikelnummer',
            'suche_prio_kurzbeschreibung',
            'suche_prio_beschreibung',
            'suche_prio_ean',
            'suche_prio_isbn',
            'suche_prio_han',
            'suche_prio_anmerkung'
        ] as $sucheParam) {
        if (isset($_POST[$sucheParam]) && ($_POST[$sucheParam] != $conf['artikeluebersicht'][$sucheParam])) {
            $fulltextChanged = true;
            break;
        }
    }
    if ($fulltextChanged) {
        $createIndex = $sucheFulltext ? 'Y' : 'N';
    }

    if ($sucheFulltext && $fulltextChanged) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSearchActivate'), 'successSearchActivate');
    } elseif ($fulltextChanged) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSearchDeactivate'), 'successSearchDeactivate');
    }

    $conf = Shop::getSettings([$sectionID]);
}

$section = $db->select('teinstellungensektion', 'kEinstellungenSektion', $sectionID);
if ($conf['artikeluebersicht']['suche_fulltext'] !== 'N'
    && (!$db->getSingleObject("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'")
    || !$db->getSingleObject("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'"))) {
    $alertHelper->addAlert(
        Alert::TYPE_ERROR,
        __('errorCreateTime') .
        '<a href="sucheinstellungen.php" title="Aktualisieren"><i class="alert-danger fa fa-refresh"></i></a>',
        'errorCreateTime'
    );
    Notification::getInstance($db)->add(
        NotificationEntry::TYPE_WARNING,
        __('indexCreate'),
        'sucheinstellungen.php'
    );
}

$smarty->assign('action', 'sucheinstellungen.php')
       ->assign('kEinstellungenSektion', $sectionID)
       ->assign('Sektion', $section)
       ->assign('Conf', getAdminSectionSettings(CONF_ARTIKELUEBERSICHT))
       ->assign('cPrefDesc', filteredConfDescription($sectionID))
       ->assign('cPrefURL', $smarty->getConfigVars('prefURL' . $sectionID))
       ->assign('step', $step)
       ->assign('supportFulltext', version_compare($mysqlVersion, '5.6', '>='))
       ->assign('createIndex', $createIndex)
       ->assign('waehrung', $standardwaehrung->cName)
       ->display('sucheinstellungen.tpl');
