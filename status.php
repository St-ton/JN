<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

Shop::setPageType(PAGE_BESTELLSTATUS);
$AktuelleSeite = 'BESTELLSTATUS';
$Einstellungen = Shop::getSettings(array(
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG)
);
$hinweis    = '';
$requestURL = '';

pruefeHttps();

if (strlen($_GET['uid']) === 40) {
    $status = Shop::DB()->query("SELECT kBestellung FROM tbestellstatus WHERE dDatum >= date_sub(now(), INTERVAL 30 DAY) AND cUID = '" . Shop::DB()->escape($_GET['uid']) . "'", 1);
    if (empty($status->kBestellung)) {
        header('Location: ' . Shop::getURL() . '/jtl.php', true, 303);
        exit;
    } else {
        $bestellung = new Bestellung($status->kBestellung);
        $bestellung->fuelleBestellung();
        $Kunde = new Kunde($bestellung->kKunde);
        $smarty->assign('Bestellung', $bestellung)
               ->assign('Kunde', $Kunde)
               ->assign('Lieferadresse', $bestellung->Lieferadresse);
    }
} else {
    header('Location: jtl.php', true, 303);
    exit;
}

$step = 'bestellung';
//hole alle OberKategorien
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;

//specific assigns
$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('requestURL', $requestURL)
       ->assign('Einstellungen', $Einstellungen);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
