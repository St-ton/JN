<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

Shop::setPageType(PAGE_BESTELLSTATUS);
$smarty        = Shop::Smarty();
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG
]);
$hinweis       = '';
$linkHelper    = Shop::Container()->getLinkService();

if (strlen($_GET['uid']) === 40) {
    $status = Shop::Container()->getDB()->queryPrepared(
        'SELECT kBestellung 
            FROM tbestellstatus 
            WHERE dDatum >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
            AND cUID = :uid',
        ['uid' => $_GET['uid']],
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (empty($status->kBestellung)) {
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
        exit;
    }
    $bestellung = (new Bestellung($status->kBestellung))->fuelleBestellung();
    $smarty->assign('Bestellung', $bestellung)
           ->assign('Kunde', new Kunde($bestellung->kKunde))
           ->assign('Lieferadresse', $bestellung->Lieferadresse);
} else {
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
    exit;
}

$step                   = 'bestellung';
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('BESTELLUNG_STATUS_BEZAHLT', BESTELLUNG_STATUS_BEZAHLT)
       ->assign('BESTELLUNG_STATUS_VERSANDT', BESTELLUNG_STATUS_VERSANDT)
       ->assign('BESTELLUNG_STATUS_OFFEN', BESTELLUNG_STATUS_OFFEN);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
