<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Backend\Notification;
use JTL\Backend\NotificationEntry;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/admininclude.php';
require_once __DIR__ . '/includes/einstellungen_inc.php';
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
$getText          = Shop::Container()->getGetText();
$alertService     = Shop::Container()->getAlertService();
$adminAccount     = Shop::Container()->getAdminAccount();
$sectionFactory   = new SectionFactory();
$settingManager   = new Manager($db, $smarty, $adminAccount, $getText, $alertService);

$getText->loadAdminLocale('pages/einstellungen');

if (Request::postInt('einstellungen_bearbeiten') === 1 && Form::validateToken()) {
    $sucheFulltext = in_array(Request::postVar('suche_fulltext', []), ['Y', 'B'], true);
    if ($sucheFulltext) {
        if (version_compare($mysqlVersion, '5.6', '<')) {
            //Volltextindizes werden von MySQL mit InnoDB erst ab Version 5.6 unterstützt
            $_POST['suche_fulltext'] = 'N';
            $alertService->addAlert(Alert::TYPE_ERROR, __('errorFulltextSearchMYSQL'), 'errorFulltextSearchMYSQL');
        } else {
            // Bei Volltextsuche die Mindeswortlänge an den DB-Parameter anpassen
            $currentVal = $db->getSingleObject('SELECT @@ft_min_word_len AS ft_min_word_len');
            if (($currentVal->ft_min_word_len ?? $_POST['suche_min_zeichen']) !== $_POST['suche_min_zeichen']) {
                $_POST['suche_min_zeichen'] = $currentVal->ft_min_word_len;
                $alertService->addAlert(
                    Alert::TYPE_WARNING,
                    __('errorFulltextSearchMinLen'),
                    'errorFulltextSearchMinLen'
                );
            }
        }
    }

    $shopSettings = Shopsetting::getInstance();
    $alertService->addAlert(
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
        $alertService->addAlert(Alert::TYPE_SUCCESS, __('successSearchActivate'), 'successSearchActivate');
    } elseif ($fulltextChanged) {
        $alertService->addAlert(Alert::TYPE_SUCCESS, __('successSearchDeactivate'), 'successSearchDeactivate');
    }

    $conf = Shop::getSettings([$sectionID]);
}

$section = $sectionFactory->getSection($sectionID, $settingManager);
$section->load();
if ($conf['artikeluebersicht']['suche_fulltext'] !== 'N'
    && (!$db->getSingleObject("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'")
        || !$db->getSingleObject("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'"))) {
    $alertService->addAlert(
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
getAdminSectionSettings(CONF_ARTIKELUEBERSICHT);
$smarty->assign('action', 'sucheinstellungen.php')
    ->assign('kEinstellungenSektion', $sectionID)
    ->assign('sections', [$section])
    ->assign('cPrefURL', $smarty->getConfigVars('prefURL' . $sectionID))
    ->assign('step', $step)
    ->assign('supportFulltext', version_compare($mysqlVersion, '5.6', '>='))
    ->assign('createIndex', $createIndex)
    ->assign('waehrung', $standardwaehrung->cName)
    ->display('sucheinstellungen.tpl');
