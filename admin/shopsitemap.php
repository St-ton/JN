<?php declare(strict_types=1);

use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);
if (isset($_POST['einstellungen']) && Form::validateToken()) {
    saveAdminSectionSettings(CONF_SITEMAP, $_POST);
    if (GeneralObject::hasCount('nVon', $_POST) && GeneralObject::hasCount('nBis', $_POST)) {
        $db = Shop::Container()->getDB();
        $db->query('TRUNCATE TABLE tpreisspannenfilter');
        for ($i = 0; $i < 10; $i++) {
            if ((int)$_POST['nVon'][$i] >= 0 && (int)$_POST['nBis'][$i] > 0) {
                $filter       = new stdClass();
                $filter->nVon = (int)$_POST['nVon'][$i];
                $filter->nBis = (int)$_POST['nBis'][$i];

                $db->insert('tpreisspannenfilter', $filter);
            }
        }
    }
}
getAdminSectionSettings(CONF_SITEMAP);
$smarty->display('shopsitemap.tpl');
