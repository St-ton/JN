<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'warenkorb_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

$AktuelleSeite = 'WARENKORB';
$MsgWarning    = '';
$Einstellungen = Shop::getSettings(array(
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KAUFABWICKLUNG,
    CONF_KUNDEN,
    CONF_ARTIKELUEBERSICHT,
    CONF_SONSTIGES
));
$Schnellkaufhinweis       = checkeSchnellkauf();
$linkHelper               = LinkHelper::getInstance();
$KuponcodeUngueltig       = false;
$nVersandfreiKuponGueltig = false;

pruefeHttps();
Shop::setPageType(PAGE_WARENKORB);
$kLink = $linkHelper->getSpecialPageLinkKey(LINKTYP_WARENKORB);
//Warenkorbaktualisierung?
uebernehmeWarenkorbAenderungen();
//validiere Konfigurationen
validiereWarenkorbKonfig();
//Versandermittlung?
if (isset($_POST['land']) && isset($_POST['plz']) && !VersandartHelper::getShippingCosts($_POST['land'], $_POST['plz'], $MsgWarning)) {
    $MsgWarning = Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages');
}
//Kupons bearbeiten
if (isset($_POST['Kuponcode']) && strlen($_POST['Kuponcode']) > 0 && !$_SESSION['Warenkorb']->posTypEnthalten(C_WARENKORBPOS_TYP_KUPON)) {
    // Kupon darf nicht im leeren Warenkorb eingelöst werden
    if (isset($_SESSION['Warenkorb']) && $_SESSION['Warenkorb']->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL)) > 0) {
        $Kupon             = Shop::DB()->select('tkupon', 'cCode', $_POST['Kuponcode']);
        $invalidCouponCode = false;
        if (isset($Kupon->kKupon)) {
            $Kuponfehler  = checkeKupon($Kupon);
            $nReturnValue = angabenKorrekt($Kuponfehler);
            if ($nReturnValue) {
                if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === 'standard') {
                    executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI);
                    kuponAnnehmen($Kupon);
                    executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                } elseif (!empty($Kupon->kKupon) && $Kupon->cKuponTyp === 'versandkupon') {
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
}

//Kupon nicht mehr verfügbar. Redirekt im Bestellabschluss. Fehlerausgabe
if (isset($_SESSION['checkCouponResult'])) {
    $KuponcodeUngueltig = true;
    $Kuponfehler        = $_SESSION['checkCouponResult'];
    unset($_SESSION['checkCouponResult']);
    $smarty->assign('cKuponfehler', $Kuponfehler['ungueltig']);
}

// Gratis Geschenk bearbeiten
if (isset($_POST['gratis_geschenk']) && intval($_POST['gratis_geschenk']) === 1 && isset($_POST['gratishinzufuegen'])) {
    $kArtikelGeschenk = (int)$_POST['gratisgeschenk'];
    // Pruefenn ob der Artikel wirklich ein Gratis Geschenk ist
    $oArtikelGeschenk = Shop::DB()->query(
        "SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
            FROM tartikelattribut
            JOIN tartikel ON tartikel.kArtikel = tartikelattribut.kArtikel
            WHERE tartikelattribut.kArtikel = " . $kArtikelGeschenk . "
            AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
            AND CAST(tartikelattribut.cWert AS DECIMAL) <= " . $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true), 1
    );
    if (isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0) {
        if ($oArtikelGeschenk->fLagerbestand <= 0 && $oArtikelGeschenk->cLagerKleinerNull === 'N' && $oArtikelGeschenk->cLagerBeachten === 'Y') {
            $MsgWarning = Shop::Lang()->get('freegiftsNostock', 'errorMessages');
        } else {
            executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                                  ->fuegeEin($kArtikelGeschenk, 1, array(), C_WARENKORBPOS_TYP_GRATISGESCHENK);
        }
    }
}
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
if (isset($_GET['fillOut'])) {
    if (intval($_GET['fillOut']) === 9 && isset($_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]) && $_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT] > 0 &&
        $_SESSION['Warenkorb']->gibGesamtsummeWaren(1, 0) < $_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]
    ) {
        $MsgWarning = Shop::Lang()->get('minordernotreached', 'checkout') . ' ' .
            gibPreisStringLocalized($_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]);
    } elseif (intval($_GET['fillOut']) === 8) {
        $MsgWarning = Shop::Lang()->get('orderNotPossibleNow', 'checkout');
    } elseif (intval($_GET['fillOut']) === 3) {
        $MsgWarning = Shop::Lang()->get('yourbasketisempty', 'checkout');
    } elseif (intval($_GET['fillOut']) === 10) {
        $MsgWarning = Shop::Lang()->get('missingProducts', 'checkout');
        loescheAlleSpezialPos();
    } elseif (intval($_GET['fillOut']) === UPLOAD_ERROR_NEED_UPLOAD) {
        $MsgWarning = Shop::Lang()->get('missingFilesUpload', 'checkout');
    }
}

$kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKundengruppe > 0) {
    $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
}
// Canonical
$cCanonicalURL = Shop::getURL() . '/warenkorb.php';
// Metaangaben
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_WARENKORB);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;
// Uploads
if (class_exists('Upload')) {
    $oUploadSchema_arr = Upload::gibWarenkorbUploads($_SESSION['Warenkorb']);
    if ($oUploadSchema_arr) {
        $nMaxSize = Upload::uploadMax();
        $smarty->assign('cSessionID', session_id())
               ->assign('nMaxUploadSize', $nMaxSize)
               ->assign('cMaxUploadSize', Upload::formatGroesse($nMaxSize))
               ->assign('oUploadSchema_arr', $oUploadSchema_arr);
    }
}
//specific assigns
$smarty->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('Einstellungen', $Einstellungen)
       ->assign('MsgWarning', $MsgWarning)
       ->assign('Schnellkaufhinweis', $Schnellkaufhinweis)
       ->assign('requestURL', (isset($requestURL)) ? $requestURL : null)
       ->assign('laender', gibBelieferbareLaender($kKundengruppe))
       ->assign('KuponMoeglich', kuponMoeglich())
       ->assign('xselling', gibXSelling())
       ->assign('oArtikelGeschenk_arr', gibGratisGeschenke($Einstellungen))
       ->assign('BestellmengeHinweis', pruefeBestellMengeUndLagerbestand($Einstellungen))
       ->assign('PFAD_ART_ABNAHMEINTERVALL', PFAD_ART_ABNAHMEINTERVALL)
       ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
       ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK)
       ->assign('cErrorVersandkosten', (isset($cErrorVersandkosten)) ? $cErrorVersandkosten : null)
       ->assign('KuponcodeUngueltig', $KuponcodeUngueltig)
       ->assign('nVersandfreiKuponGueltig', $nVersandfreiKuponGueltig)
       ->assign('Warenkorb', $_SESSION['Warenkorb']);

if (isset($_SESSION['Warenkorbhinweise']) && $_SESSION['Warenkorbhinweise']) {
    $smarty->assign('Warenkorbhinweise', $_SESSION['Warenkorbhinweise']);
    unset($_SESSION['Warenkorbhinweise']);
} else {
    $smarty->assign('Warenkorbhinweise', array());
}

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_WARENKORB_PAGE);

$smarty->display('basket/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
