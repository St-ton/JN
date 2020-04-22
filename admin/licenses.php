<?php declare(strict_types=1);

use GuzzleHttp\Exception\RequestException;
use JTL\Alert\Alert;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$db      = Shop::Container()->getDB();
$manager = new Manager($db);
// @todo:
// $lang = $oAccount->getGetText()->getLanguage()
try {
    $manager->update();
} catch (RequestException $e) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_ERROR,
        __('errorFetchLicenseAPI'),
        'errorFetchLicenseAPI'
    );
}
//$v1 = Version::parse('1.0.0');
//$v2 = Version::parse('1.0.1');
//Shop::dbg($v2->greaterThan($v1), true);
$mapper   = new Mapper($db, $manager);
$licenses = $mapper->getCollection();
$smarty->assign('licenses', $licenses)
    ->assign('lastUpdate', $lastItem->timestamp ?? null)
    ->display('licenses.tpl');
