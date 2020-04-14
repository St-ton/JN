<?php declare(strict_types=1);

use GuzzleHttp\Exception\RequestException;
use JTL\Alert\Alert;
use JTL\License\Manager;
use JTL\Shop;
use JTL\License\Mapper;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$db      = Shop::Container()->getDB();
$manager = new Manager($db);

try {
    $manager->update();
} catch (RequestException $e) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_ERROR,
        __('errorFetchLicenseAPI'),
        'errorFetchLicenseAPI'
    );
}

$mapper   = new Mapper($db, $manager);
$licenses = $mapper->getCollection();
$smarty->assign('licenses', $licenses)
    ->assign('lastUpdate', $lastItem->timestamp ?? null)
    ->display('licenses.tpl');
