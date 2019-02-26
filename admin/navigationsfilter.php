<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Sprache;
use JTL\DB\ReturnType;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SETTINGS_NAVIGATION_FILTER_VIEW', true, true);

$db = Shop::Container()->getDB();
setzeSprache();
if (isset($_POST['speichern']) && Form::validateToken()) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_NAVIGATIONSFILTER, $_POST),
        'saveSettings'
    );
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_CATEGORY]);
    if (isset($_POST['nVon'], $_POST['nBis'])
        && is_array($_POST['nVon'])
        && is_array($_POST['nBis'])
        && count($_POST['nVon']) > 0
        && count($_POST['nBis']) > 0
    ) {
        $db->query('TRUNCATE TABLE tpreisspannenfilter', ReturnType::AFFECTED_ROWS);
        foreach ($_POST['nVon'] as $i => $nVon) {
            $nVon = (float)$nVon;
            $nBis = (float)$_POST['nBis'][$i];
            if ($nVon >= 0 && $nBis >= 0) {
                $db->insert('tpreisspannenfilter', (object)['nVon' => $nVon, 'nBis' => $nBis]);
            }
        }
    }
}

$priceRangeFilters = $db->query(
    'SELECT * FROM tpreisspannenfilter',
    ReturnType::ARRAY_OF_OBJECTS
);

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_NAVIGATIONSFILTER))
       ->assign('oPreisspannenfilter_arr', $priceRangeFilters)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->display('navigationsfilter.tpl');
