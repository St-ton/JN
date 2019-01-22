<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;
use Helpers\ShippingMethod;
use Helpers\Cart;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'warenkorb_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

$warning = '';
$smarty  = Shop::Smarty();
$conf    = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KAUFABWICKLUNG,
    CONF_KUNDEN,
    CONF_ARTIKELUEBERSICHT,
    CONF_SONSTIGES
]);
Shop::setPageType(PAGE_WARENKORB);
$linkHelper              = Shop::Container()->getLinkService();
$couponCodeValid         = true;
$shippingFreeCouponValid = false;
$cart                    = \Session\Frontend::getCart();
$kLink                   = $linkHelper->getSpecialPageLinkKey(LINKTYP_WARENKORB);
$link                    = $linkHelper->getPageLink($kLink);
Cart::applyCartChanges();
Cart::validateCartConfig();
pruefeGuthabenNutzen();
if (isset($_POST['land'], $_POST['plz'])
    && !ShippingMethod::getShippingCosts($_POST['land'], $_POST['plz'], $warning)
) {
    $warning = Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages');
}
if ($cart !== null
    && isset($_POST['Kuponcode'])
    && strlen($_POST['Kuponcode']) > 0
    && $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
) {
    // Kupon darf nicht im leeren Warenkorb eingelöst werden
    $coupon            = new Kupon();
    $coupon            = $coupon->getByCode($_POST['Kuponcode']);
    $invalidCouponCode = false;
    if ($coupon !== false && $coupon->kKupon > 0) {
        $couponError = Kupon::checkCoupon($coupon);
        $check       = angabenKorrekt($couponError);
        executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
            'error'        => &$couponError,
            'nReturnValue' => &$check
        ]);
        if ($check) {
            if ($coupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                Kupon::acceptCoupon($coupon);
                executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
            } elseif (!empty($coupon->kKupon) && $coupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
                $_SESSION['oVersandfreiKupon'] = $coupon;
                $smarty->assign('cVersandfreiKuponLieferlaender_arr', explode(';', $coupon->cLieferlaender));
                $shippingFreeCouponValid = true;
            }
        } else {
            $smarty->assign('cKuponfehler', $couponError['ungueltig']);
        }
    } else {
        $invalidCouponCode = true;
        $smarty->assign('invalidCouponCode', $invalidCouponCode);
    }
}
// Kupon nicht mehr verfügbar. Redirect im Bestellabschluss. Fehlerausgabe
if (isset($_SESSION['checkCouponResult'])) {
    $couponCodeValid = false;
    $couponError     = $_SESSION['checkCouponResult'];
    unset($_SESSION['checkCouponResult']);
    $smarty->assign('cKuponfehler', $couponError['ungueltig']);
}
if (isset($_POST['gratis_geschenk'], $_POST['gratisgeschenk']) && (int)$_POST['gratis_geschenk'] === 1) {
    $giftID = (int)$_POST['gratisgeschenk'];
    $gift   = Shop::Container()->getDB()->query(
        'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, 
            tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
            FROM tartikelattribut
                JOIN tartikel 
                    ON tartikel.kArtikel = tartikelattribut.kArtikel
                WHERE tartikelattribut.kArtikel = ' . $giftID . "
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
        $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (isset($gift->kArtikel) && $gift->kArtikel > 0) {
        if ($gift->fLagerbestand <= 0 && $gift->cLagerKleinerNull === 'N'  && $gift->cLagerBeachten === 'Y') {
            $warning = Shop::Lang()->get('freegiftsNostock', 'errorMessages');
        } else {
            executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
            $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                 ->fuegeEin($giftID, 1, [], C_WARENKORBPOS_TYP_GRATISGESCHENK);
            WarenkorbPers::addToCheck($giftID, 1, [], '', 0, C_WARENKORBPOS_TYP_GRATISGESCHENK);
        }
    }
}
if (isset($_GET['fillOut'])) {
    $mbw = \Session\Frontend::getCustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT);
    if ((int)$_GET['fillOut'] === 9 && $mbw > 0 && $cart->gibGesamtsummeWaren(true, false) < $mbw) {
        $warning = Shop::Lang()->get('minordernotreached', 'checkout') . ' ' . Preise::getLocalizedPriceString($mbw);
    } elseif ((int)$_GET['fillOut'] === 8) {
        $warning = Shop::Lang()->get('orderNotPossibleNow', 'checkout');
    } elseif ((int)$_GET['fillOut'] === 3) {
        $warning = Shop::Lang()->get('yourbasketisempty', 'checkout');
    } elseif ((int)$_GET['fillOut'] === 10) {
        $warning = Shop::Lang()->get('missingProducts', 'checkout');
        Cart::deleteAllSpecialPositions();
    } elseif ((int)$_GET['fillOut'] === UPLOAD_ERROR_NEED_UPLOAD) {
        $warning = Shop::Lang()->get('missingFilesUpload', 'checkout');
    }
}
$kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKundengruppe > 0) {
    $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
}
$cCanonicalURL = $linkHelper->getStaticRoute('warenkorb.php');
$cartNotices   = [];
if (class_exists('Upload')) {
    $uploads = Upload::gibWarenkorbUploads($cart);
    $maxSize = Upload::uploadMax();
    $smarty->assign('nMaxUploadSize', $maxSize)
           ->assign('cMaxUploadSize', Upload::formatGroesse($maxSize))
           ->assign('oUploadSchema_arr', $uploads);
}
if (!empty($_SESSION['Warenkorbhinweise'])) {
    $cartNotices = $_SESSION['Warenkorbhinweise'];
    unset($_SESSION['Warenkorbhinweise']);
}

Cart::addVariationPictures($cart);
$smarty->assign('MsgWarning', $warning)
       ->assign('Link', $link)
       ->assign('Schnellkaufhinweis', Cart::checkQuickBuy())
       ->assign('laender', ShippingMethod::getPossibleShippingCountries($kKundengruppe))
       ->assign('KuponMoeglich', Kupon::couponsAvailable())
       ->assign('currentCoupon', Shop::Lang()->get('currentCoupon', 'checkout'))
       ->assign('currentCouponName', (!empty($_SESSION['Kupon']->translationList)
           ? $_SESSION['Kupon']->translationList
           : null))
       ->assign('currentShippingCouponName', (!empty($_SESSION['oVersandfreiKupon']->translationList)
           ? $_SESSION['oVersandfreiKupon']->translationList
           : null))
       ->assign('xselling', Cart::getXSelling())
       ->assign('oArtikelGeschenk_arr', Cart::getFreeGifts($conf))
       ->assign('BestellmengeHinweis', Cart::checkOrderAmountAndStock($conf))
       ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
       ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK)
       ->assign('cErrorVersandkosten', $cErrorVersandkosten ?? null)
       ->assign('KuponcodeUngueltig', !$couponCodeValid)
       ->assign('nVersandfreiKuponGueltig', $shippingFreeCouponValid)
       ->assign('Warenkorb', $cart)
       ->assign('Warenkorbhinweise', $cartNotices);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_WARENKORB_PAGE);

$smarty->display('basket/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
