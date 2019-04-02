<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Checkout\Bestellung;
use JTL\Alert\Alert;
use JTL\Customer\Kunde;
use JTL\Shop;
use JTL\DB\ReturnType;
use JTL\Session\Frontend;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

Shop::setPageType(PAGE_BESTELLSTATUS);
$smarty     = Shop::Smarty();
$linkHelper = Shop::Container()->getLinkService();

if (isset($_GET['uid'])) {
    $status = Shop::Container()->getDB()->queryPrepared(
        'SELECT kBestellung 
            FROM tbestellstatus 
            WHERE dDatum >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
            AND cUID = :uid',
        ['uid' => $_GET['uid']],
        ReturnType::SINGLE_OBJECT
    );
    if (empty($status->kBestellung)) {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_DANGER,
            Shop::Lang()->get('statusOrderNotFound', 'errorMessages'),
            'statusOrderNotFound',
            ['saveInSession' => true]
        );
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
        exit;
    }
    $order = new Bestellung($status->kBestellung, true);
    $smarty->assign('Bestellung', $order)
           ->assign('Kunde', new Kunde($order->kKunde))
           ->assign('Lieferadresse', $order->Lieferadresse)
           ->assign('showLoginPanel', Frontend::getCustomer()->isLoggedIn())
           ->assign('billingAddress', $order->oRechnungsadresse);
} else {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_DANGER,
        Shop::Lang()->get('uidNotFound', 'errorMessages'),
        'wrongUID',
        ['saveInSession' => true]
    );
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
    exit;
}

$step = 'Bestellung';
$smarty->assign('step', $step)
       ->assign('BESTELLUNG_STATUS_BEZAHLT', BESTELLUNG_STATUS_BEZAHLT)
       ->assign('BESTELLUNG_STATUS_VERSANDT', BESTELLUNG_STATUS_VERSANDT)
       ->assign('BESTELLUNG_STATUS_OFFEN', BESTELLUNG_STATUS_OFFEN);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
