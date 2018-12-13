<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;

/**
 * @global Smarty\JTLSmarty $smarty
 * @global AdminAccount     $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SETTINGS_NAVIGATION_FILTER_VIEW', true, true);

$cHinweis = '';
$cFehler  = '';
setzeSprache();
if (isset($_POST['speichern']) && FormHelper::validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_NAVIGATIONSFILTER, $_POST);
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_CATEGORY]);
    if (is_array($_POST['nVon'])
        && is_array($_POST['nBis'])
        && count($_POST['nVon']) > 0
        && count($_POST['nBis']) > 0
    ) {
        Shop::Container()->getDB()->query('TRUNCATE TABLE tpreisspannenfilter', \DB\ReturnType::AFFECTED_ROWS);
        foreach ($_POST['nVon'] as $i => $nVon) {
            $nVon = (float)$nVon;
            $nBis = (float)$_POST['nBis'][$i];
            if ($nVon >= 0 && $nBis >= 0) {
                Shop::Container()->getDB()->insert('tpreisspannenfilter', (object)['nVon' => $nVon, 'nBis' => $nBis]);
            }
        }
    }
}

$priceRangeFilters = Shop::Container()->getDB()->query(
    'SELECT * FROM tpreisspannenfilter',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_NAVIGATIONSFILTER))
       ->assign('oPreisspannenfilter_arr', $priceRangeFilters)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('navigationsfilter.tpl');
