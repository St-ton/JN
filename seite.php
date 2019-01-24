<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;
use Helpers\URL;
use Helpers\ShippingMethod;

if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';
$smarty      = Shop::Smarty();
$conf        = Shopsetting::getInstance()->getAll();
$linkHelper  = Shop::Container()->getLinkService();
$alertHelper = Shop::Container()->getAlertService();
if (Shop::$isInitialized === true) {
    $kLink = Shop::$kLink;
}
$link = $linkHelper->getLinkByID(Shop::$kLink);
if ($link === null || !$link->isVisible()) {
    $link = $linkHelper->getSpecialPage(LINKTYP_STARTSEITE);
    $link->setRedirectCode(301);
}
$requestURL = URL::buildURL($link, URLART_SEITE);
if ($link->getLinkType() === LINKTYP_STARTSEITE) {
    $cCanonicalURL = Shop::getURL() . '/';
} elseif (strpos($requestURL, '.php') === false) {
    $cCanonicalURL = Shop::getURL() . '/' . $requestURL;
}
if ($link->getLinkType() === LINKTYP_STARTSEITE) {
    if ($link->getRedirectCode() > 0) {
        header('Location: ' . $cCanonicalURL, true, $link->getRedirectCode());
        exit();
    }
    $smarty->assign('StartseiteBoxen', CMSHelper::getHomeBoxes())
           ->assign('oNews_arr', $conf['news']['news_benutzen'] === 'Y'
               ? CMSHelper::getHomeNews($conf)
               : []);
    \Extensions\AuswahlAssistent::startIfRequired(AUSWAHLASSISTENT_ORT_STARTSEITE, 1, Shop::getLanguage(), $smarty);
} elseif ($link->getLinkType() === LINKTYP_AGB) {
    $smarty->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
        Shop::getLanguage(),
        \Session\Frontend::getCustomerGroup()->getID()
    ));
} elseif ($link->getLinkType() === LINKTYP_WRB) {
    $smarty->assign('WRB', Shop::Container()->getLinkService()->getAGBWRB(
        Shop::getLanguage(),
        \Session\Frontend::getCustomerGroup()->getID()
    ));
} elseif ($link->getLinkType() === LINKTYP_VERSAND) {
    if (isset($_POST['land'], $_POST['plz']) && !ShippingMethod::getShippingCosts($_POST['land'], $_POST['plz'])) {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages'),
            'missingParamShippingDetermination'
        );
    }
    $smarty->assign(
        'laender',
        ShippingMethod::getPossibleShippingCountries(
            \Session\Frontend::getCustomerGroup()->getID()
        )
    );
} elseif ($link->getLinkType() === LINKTYP_LIVESUCHE) {
    $liveSearchTop  = CMSHelper::getLiveSearchTop($conf);
    $liveSearchLast = CMSHelper::getLiveSearchLast($conf);
    if (count($liveSearchTop) === 0 && count($liveSearchLast) === 0) {
        $alertHelper->addAlert(Alert::TYPE_WARNING, Shop::Lang()->get('noDataAvailable'), 'noDataAvailable');
    }
    $smarty->assign('LivesucheTop', $liveSearchTop)
           ->assign('LivesucheLast', $liveSearchLast);
} elseif ($link->getLinkType() === LINKTYP_TAGGING) {
    $smarty->assign('Tagging', CMSHelper::getTagging($conf));
} elseif ($link->getLinkType() === LINKTYP_HERSTELLER) {
    $smarty->assign('oHersteller_arr', Hersteller::getAll());
} elseif ($link->getLinkType() === LINKTYP_NEWSLETTERARCHIV) {
    $smarty->assign('oNewsletterHistory_arr', CMSHelper::getNewsletterHistory());
} elseif ($link->getLinkType() === LINKTYP_SITEMAP) {
    Shop::setPageType(PAGE_SITEMAP);
    $sitemap = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), $conf);
    $sitemap->assignData($smarty);
} elseif ($link->getLinkType() === LINKTYP_404) {
    $sitemap = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), $conf);
    $sitemap->assignData($smarty);
    Shop::setPageType(PAGE_404);
    $alertHelper->addAlert(Alert::TYPE_DANGER, Shop::Lang()->get('pageNotFound'), 'pageNotFound');
} elseif ($link->getLinkType() === LINKTYP_GRATISGESCHENK) {
    if ($conf['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
        $oArtikelGeschenk_arr = CMSHelper::getFreeGifts($conf);
        if (is_array($oArtikelGeschenk_arr) && count($oArtikelGeschenk_arr) > 0) {
            $smarty->assign('oArtikelGeschenk_arr', $oArtikelGeschenk_arr);
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('freegiftsNogifts', 'errorMessages'),
                'freegiftsNogifts'
            );
        }
    }
} elseif ($link->getLinkType() === LINKTYP_AUSWAHLASSISTENT) {
    \Extensions\AuswahlAssistent::startIfRequired(
        AUSWAHLASSISTENT_ORT_LINK,
        $link->getID(),
        Shop::getLanguageID(),
        $smarty
    );
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_SEITE_PAGE_IF_LINKART);
$smarty->assign('Link', $link);

executeHook(HOOK_SEITE_PAGE);

$smarty->display('layout/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
