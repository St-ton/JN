<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'warenkorb_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

$AktuelleSeite = 'WARENKORB';
$MsgWarning    = '';
$smarty        = Shop::Smarty();
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KAUFABWICKLUNG,
    CONF_KUNDEN,
    CONF_ARTIKELUEBERSICHT,
    CONF_SONSTIGES
]);
Shop::setPageType(PAGE_WARENKORB);
$Schnellkaufhinweis       = checkeSchnellkauf();
$linkHelper               = Shop::Container()->getLinkService();
$KuponcodeUngueltig       = false;
$nVersandfreiKuponGueltig = false;
$cart                     = Session::Cart();
$kLink                    = $linkHelper->getSpecialPageLinkKey(LINKTYP_WARENKORB);
// Warenkorbaktualisierung?
uebernehmeWarenkorbAenderungen();
// validiere Konfigurationen
validiereWarenkorbKonfig();
pruefeGuthabenNutzen();
// Versandermittlung?
if (isset($_POST['land'], $_POST['plz'])
    && !VersandartHelper::getShippingCosts($_POST['land'], $_POST['plz'], $MsgWarning)
) {
    $MsgWarning = Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages');
}
// Kupons bearbeiten
if ($cart !== null
    && isset($_POST['Kuponcode'])
    && strlen($_POST['Kuponcode']) > 0
    && $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
) {
    // Kupon darf nicht im leeren Warenkorb eingelöst werden
    $Kupon             = new Kupon();
    $Kupon             = $Kupon->getByCode($_POST['Kuponcode']);
    $invalidCouponCode = false;
    if ($Kupon !== false && $Kupon->kKupon > 0) {
        $Kuponfehler  = checkeKupon($Kupon);
        $nReturnValue = angabenKorrekt($Kuponfehler);
        executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
            'error'        => &$Kuponfehler,
            'nReturnValue' => &$nReturnValue
        ]);
        if ($nReturnValue) {
            if ($Kupon->cKuponTyp === 'standard') {
                kuponAnnehmen($Kupon);
                executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
            } elseif (!empty($Kupon->kKupon) && $Kupon->cKuponTyp === 'versandkupon') {
                // Aktiven Kupon aus der Session löschen und dessen Warenkorbposition
                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
                // Versandfrei Kupon
                $_SESSION['oVersandfreiKupon'] = $Kupon;
                $smarty->assign('cVersandfreiKuponLieferlaender_arr', explode(';', $Kupon->cLieferlaender));
                $nVersandfreiKuponGueltig = true;
            }
        } else {
            $smarty->assign('cKuponfehler', $Kuponfehler['ungueltig']);
        }
    } else {
        $invalidCouponCode = true;
        $smarty->assign('invalidCouponCode', $invalidCouponCode);
    }
}
// Kupon nicht mehr verfügbar. Redirect im Bestellabschluss. Fehlerausgabe
if (isset($_SESSION['checkCouponResult'])) {
    $KuponcodeUngueltig = true;
    $Kuponfehler        = $_SESSION['checkCouponResult'];
    unset($_SESSION['checkCouponResult']);
    $smarty->assign('cKuponfehler', $Kuponfehler['ungueltig']);
}
// Gratis Geschenk bearbeiten
if (isset($_POST['gratis_geschenk'], $_POST['gratishinzufuegen']) && (int)$_POST['gratis_geschenk'] === 1) {
    $kArtikelGeschenk = (int)$_POST['gratisgeschenk'];
    // Pruefen ob der Artikel wirklich ein Gratis Geschenk ist
    $oArtikelGeschenk = Shop::Container()->getDB()->query(
        "SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, 
            tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
            FROM tartikelattribut
                JOIN tartikel 
                    ON tartikel.kArtikel = tartikelattribut.kArtikel
                WHERE tartikelattribut.kArtikel = " . $kArtikelGeschenk . "
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
        $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0) {
        if ($oArtikelGeschenk->fLagerbestand <= 0
            && $oArtikelGeschenk->cLagerKleinerNull === 'N'
            && $oArtikelGeschenk->cLagerBeachten === 'Y'
        ) {
            $MsgWarning = Shop::Lang()->get('freegiftsNostock', 'errorMessages');
        } else {
            executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
            $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                 ->fuegeEin($kArtikelGeschenk, 1, [], C_WARENKORBPOS_TYP_GRATISGESCHENK);
            WarenkorbPers::addToCheck($kArtikelGeschenk, 1, [], '', 0, C_WARENKORBPOS_TYP_GRATISGESCHENK);
        }
    }
}
// hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
if (isset($_GET['fillOut'])) {
    $mbw = Session::CustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT);
    if ((int)$_GET['fillOut'] === 9 && $mbw > 0 && $cart->gibGesamtsummeWaren(1, 0) < $mbw) {
        $MsgWarning = Shop::Lang()->get('minordernotreached', 'checkout') . ' ' .
            gibPreisStringLocalized($mbw);
    } elseif ((int)$_GET['fillOut'] === 8) {
        $MsgWarning = Shop::Lang()->get('orderNotPossibleNow', 'checkout');
    } elseif ((int)$_GET['fillOut'] === 3) {
        $MsgWarning = Shop::Lang()->get('yourbasketisempty', 'checkout');
    } elseif ((int)$_GET['fillOut'] === 10) {
        $MsgWarning = Shop::Lang()->get('missingProducts', 'checkout');
        loescheAlleSpezialPos();
    } elseif ((int)$_GET['fillOut'] === UPLOAD_ERROR_NEED_UPLOAD) {
        $MsgWarning = Shop::Lang()->get('missingFilesUpload', 'checkout');
    }
}
$kKundengruppe = Session::CustomerGroup()->getID();
if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKundengruppe > 0) {
    $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
}
// Canonical
$cCanonicalURL = $linkHelper->getStaticRoute('warenkorb.php');
// Metaangaben
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_WARENKORB);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;
$cartNotices      = [];
// Uploads
if (class_exists('Upload')) {
    $oUploadSchema_arr = Upload::gibWarenkorbUploads($cart);
    if ($oUploadSchema_arr) {
        $nMaxSize = Upload::uploadMax();
        $smarty->assign('cSessionID', session_id())
               ->assign('nMaxUploadSize', $nMaxSize)
               ->assign('cMaxUploadSize', Upload::formatGroesse($nMaxSize))
               ->assign('oUploadSchema_arr', $oUploadSchema_arr);
    }
}
if (!empty($_SESSION['Warenkorbhinweise'])) {
    $cartNotices = $_SESSION['Warenkorbhinweise'];
    unset($_SESSION['Warenkorbhinweise']);
}

WarenkorbHelper::addVariationPictures($cart);
$smarty->assign('MsgWarning', $MsgWarning)
       ->assign('Schnellkaufhinweis', $Schnellkaufhinweis)
       ->assign('laender', VersandartHelper::getPossibleShippingCountries($kKundengruppe))
       ->assign('KuponMoeglich', kuponMoeglich())
       ->assign('currentCoupon', Shop::Lang()->get('currentCoupon', 'checkout'))
       ->assign('currentCouponName', (!empty($_SESSION['Kupon']->translationList)
           ? $_SESSION['Kupon']->translationList
           : null))
       ->assign('currentShippingCouponName', (!empty($_SESSION['oVersandfreiKupon']->translationList)
           ? $_SESSION['oVersandfreiKupon']->translationList
           : null))
       ->assign('xselling', gibXSelling())
       ->assign('oArtikelGeschenk_arr', gibGratisGeschenke($Einstellungen))
       ->assign('BestellmengeHinweis', pruefeBestellMengeUndLagerbestand($Einstellungen))
       ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
       ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK)
       ->assign('cErrorVersandkosten', $cErrorVersandkosten ?? null)
       ->assign('KuponcodeUngueltig', $KuponcodeUngueltig)
       ->assign('nVersandfreiKuponGueltig', $nVersandfreiKuponGueltig)
       ->assign('Warenkorb', $cart)
       ->assign('Warenkorbhinweise', $cartNotices);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_WARENKORB_PAGE);

$smarty->display('basket/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
