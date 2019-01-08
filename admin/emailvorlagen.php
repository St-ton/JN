<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Date;
use Helpers\Form;
use Helpers\Request;
use Helpers\ShippingMethod;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_EMAIL_TEMPLATE_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
/** @global Smarty\JTLSmarty $smarty */
$mailTpl               = null;
$hinweis               = '';
$cHinweis              = '';
$cFehler               = '';
$nFehler               = 0;
$continue              = true;
$oEmailvorlage         = null;
$localized             = [];
$cFehlerAnhang_arr     = [];
$step                  = 'uebersicht';
$Einstellungen         = Shop::getSettings([CONF_EMAILS]);
$oSmartyError          = new stdClass();
$oSmartyError->nCode   = 0;
$cTable                = 'temailvorlage';
$cTableSprache         = 'temailvorlagesprache';
$cTableSpracheOriginal = 'temailvorlagespracheoriginal';
$cTableSetting         = 'temailvorlageeinstellungen';
$cTablePluginSetting   = 'tpluginemailvorlageeinstellungen';
$db                    = Shop::Container()->getDB();
if (Request::verifyGPCDataInt('kPlugin') > 0) {
    $cTable                = 'tpluginemailvorlage';
    $cTableSprache         = 'tpluginemailvorlagesprache';
    $cTableSpracheOriginal = 'tpluginemailvorlagespracheoriginal';
    $cTableSetting         = 'tpluginemailvorlageeinstellungen';
}
// Errorhandler
if (isset($_GET['err'])) {
    setzeFehler($_GET['kEmailvorlage'], true);
    $cFehler = '<b>Die Emailvorlage ist fehlerhaft.</b>';
    if (is_array($_SESSION['last_error'])) {
        $cFehler .= '<br />' . $_SESSION['last_error']['message'];
        unset($_SESSION['last_error']);
    }
}
// Emailvorlage zuruecksetzen
if (isset($_POST['resetConfirm']) && (int)$_POST['resetConfirm'] > 0) {
    $oEmailvorlage = $db->select($cTable, 'kEmailvorlage', (int)$_POST['resetConfirm']);

    if (isset($oEmailvorlage->kEmailvorlage) && $oEmailvorlage->kEmailvorlage > 0) {
        $step = 'zuruecksetzen';

        $smarty->assign('oEmailvorlage', $oEmailvorlage);
    }
}

if (isset($_POST['resetEmailvorlage'])
    && (int)$_POST['resetEmailvorlage'] === 1
    && (int)$_POST['kEmailvorlage'] > 0
    && Form::validateToken()
) {
    $oEmailvorlage = $db->select($cTable, 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
    if ($oEmailvorlage->kEmailvorlage > 0 && isset($_POST['resetConfirmJaSubmit'])) {
        // Resetten
        if (Request::verifyGPCDataInt('kPlugin') > 0) {
            $db->delete(
                'tpluginemailvorlagesprache',
                'kEmailvorlage',
                (int)$_POST['kEmailvorlage']
            );
        } else {
            $db->query(
                'DELETE temailvorlage, temailvorlagesprache
                    FROM temailvorlage
                    LEFT JOIN temailvorlagesprache
                        ON temailvorlagesprache.kEmailvorlage = temailvorlage.kEmailvorlage
                    WHERE temailvorlage.kEmailvorlage = ' . (int)$_POST['kEmailvorlage'],
                \DB\ReturnType::DEFAULT
            );
            $db->query(
                'INSERT INTO temailvorlage
                    SELECT *
                    FROM temailvorlageoriginal
                    WHERE temailvorlageoriginal.kEmailvorlage = ' . (int)$_POST['kEmailvorlage'],
                \DB\ReturnType::DEFAULT
            );
        }
        $db->query(
            'INSERT INTO ' . $cTableSprache . '
                SELECT *
                FROM ' . $cTableSpracheOriginal . '
                WHERE ' . $cTableSpracheOriginal . '.kEmailvorlage = ' . (int)$_POST['kEmailvorlage'],
            \DB\ReturnType::DEFAULT
        );
        $languages = Sprache::getAllLanguages();
        if (Request::verifyGPCDataInt('kPlugin') === 0) {
            $vorlage = $db->select(
                'temailvorlageoriginal',
                'kEmailvorlage',
                (int)$_POST['kEmailvorlage']
            );
            if (isset($vorlage->cDateiname) && strlen($vorlage->cDateiname) > 0) {
                foreach ($languages as $_lang) {
                    $path      = PFAD_ROOT . PFAD_EMAILVORLAGEN . $_lang->cISO;
                    $fileHtml  = $path . '/' . $vorlage->cDateiname . '_html.tpl';
                    $filePlain = $path . '/' . $vorlage->cDateiname . '_plain.tpl';
                    if (!isset($_lang->cISO)
                        || !file_exists(PFAD_ROOT . PFAD_EMAILVORLAGEN . $_lang->cISO)
                        || !file_exists($fileHtml)
                        || !file_exists($filePlain)
                    ) {
                        continue;
                    }
                    $upd               = new stdClass();
                    $html              = file_get_contents($fileHtml);
                    $text              = file_get_contents($filePlain);
                    $doDecodeHtml      = function_exists('mb_detect_encoding')
                        ? (mb_detect_encoding($html, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15'], true) !== 'UTF-8')
                        : (StringHandler::is_utf8($html) === 1);
                    $doDecodeText      = function_exists('mb_detect_encoding')
                        ? (mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15'], true) !== 'UTF-8')
                        : (StringHandler::is_utf8($text) === 1);
                    $upd->cContentHtml = $doDecodeHtml === true ? StringHandler::convertUTF8($html) : $html;
                    $upd->cContentText = $doDecodeText === true ? StringHandler::convertUTF8($text) : $text;
                    $db->update(
                        $cTableSprache,
                        ['kEmailVorlage', 'kSprache'],
                        [(int)$_POST['kEmailvorlage'], (int)$_lang->kSprache],
                        $upd
                    );
                }
            }
        }
        $cHinweis .= 'Ihre markierte Emailvorlage wurde erfolgreich zurückgesetzt.<br />';
    }
}
if (isset($_POST['preview']) && (int)$_POST['preview'] > 0) {
    $Sprachen                = $db->query(
        'SELECT * 
            FROM tsprache 
            ORDER BY cShopStandard DESC, cNameDeutsch',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $mailTpl                 = $db->select(
        $cTable,
        'kEmailvorlage',
        (int)$_POST['preview']
    );
    $order                   = new stdClass();
    $order->kWaehrung        = 1;
    $order->kSprache         = 1;
    $order->fGuthaben        = 5;
    $order->fGesamtsumme     = 433;
    $order->cBestellNr       = 'Prefix-3432-Suffix';
    $order->cVersandInfo     = 'Optionale Information zum Versand';
    $order->cTracking        = 'Track232837';
    $order->cKommentar       = 'Kundenkommentar zur Bestellung';
    $order->cVersandartName  = 'DHL bis 10kg';
    $order->cZahlungsartName = 'Nachnahme';
    $order->cStatus          = 1;
    $order->dVersandDatum    = '2010-10-21';
    $order->dErstellt        = '2010-10-12 09:28:38';
    $order->dBezahltDatum    = '2010-10-20';

    $order->cLogistiker            = 'DHL';
    $order->cTrackingURL           = 'http://dhl.de/linkzudhl.php';
    $order->dVersanddatum_de       = '21.10.2007';
    $order->dBezahldatum_de        = '20.10.2007';
    $order->dErstelldatum_de       = '12.10.2007';
    $order->dVersanddatum_en       = '21st October 2010';
    $order->dBezahldatum_en        = '20th October 2010';
    $order->dErstelldatum_en       = '12th October 2010';
    $order->cBestellwertLocalized  = '511,00 EUR';
    $order->GuthabenNutzen         = 1;
    $order->GutscheinLocalized     = '5,00 EUR';
    $order->fWarensumme            = 433.004004;
    $order->fVersand               = 0;
    $order->nZahlungsTyp           = 0;
    $order->WarensummeLocalized[0] = '511,00 EUR';
    $order->WarensummeLocalized[1] = '429,41 EUR';
    $order->oEstimatedDelivery     = (object)[
        'localized'  => '',
        'longestMin' => 3,
        'longestMax' => 6,
    ];
    $order->cEstimatedDelivery     = &$order->oEstimatedDelivery->localized;

    $order->Positionen                              = [];
    $order->Positionen[0]                           = new stdClass();
    $order->Positionen[0]->cName                    = 'LAN Festplatte IPDrive';
    $order->Positionen[0]->cArtNr                   = 'AF8374';
    $order->Positionen[0]->cEinheit                 = 'Stck.';
    $order->Positionen[0]->cLieferstatus            = '3-4 Tage';
    $order->Positionen[0]->fPreisEinzelNetto        = 111.2069;
    $order->Positionen[0]->fPreis                   = 368.1069;
    $order->Positionen[0]->fMwSt                    = 19;
    $order->Positionen[0]->nAnzahl                  = 2;
    $order->Positionen[0]->nPosTyp                  = 1;
    $order->Positionen[0]->cHinweis                 = 'Hinweistext zum Artikel';
    $order->Positionen[0]->cGesamtpreisLocalized[0] = '278,00 EUR';
    $order->Positionen[0]->cGesamtpreisLocalized[1] = '239,66 EUR';
    $order->Positionen[0]->cEinzelpreisLocalized[0] = '139,00 EUR';
    $order->Positionen[0]->cEinzelpreisLocalized[1] = '119,83 EUR';

    $order->Positionen[0]->WarenkorbPosEigenschaftArr                           = [];
    $order->Positionen[0]->WarenkorbPosEigenschaftArr[0]                        = new stdClass();
    $order->Positionen[0]->WarenkorbPosEigenschaftArr[0]->cEigenschaftName      = 'Kapazität';
    $order->Positionen[0]->WarenkorbPosEigenschaftArr[0]->cEigenschaftWertName  = '400GB';
    $order->Positionen[0]->WarenkorbPosEigenschaftArr[0]->fAufpreis             = 128.45;
    $order->Positionen[0]->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[0] = '149,00 EUR';
    $order->Positionen[0]->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[1] = '128,45 EUR';

    $order->Positionen[0]->nAusgeliefert       = 1;
    $order->Positionen[0]->nAusgeliefertGesamt = 1;
    $order->Positionen[0]->nOffenGesamt        = 1;
    $order->Positionen[0]->dMHD                = '2025-01-01';
    $order->Positionen[0]->dMHD_de             = '01.01.2025';
    $order->Positionen[0]->cChargeNr           = 'A2100698.b12';
    $order->Positionen[0]->cSeriennummer       = '465798132756';

    $order->Positionen[1]                           = new stdClass();
    $order->Positionen[1]->cName                    = 'Klappstuhl';
    $order->Positionen[1]->cArtNr                   = 'KS332';
    $order->Positionen[1]->cEinheit                 = 'Stck.';
    $order->Positionen[1]->cLieferstatus            = '1 Woche';
    $order->Positionen[1]->fPreisEinzelNetto        = 100;
    $order->Positionen[1]->fPreis                   = 200;
    $order->Positionen[1]->fMwSt                    = 19;
    $order->Positionen[1]->nAnzahl                  = 1;
    $order->Positionen[1]->nPosTyp                  = 2;
    $order->Positionen[1]->cHinweis                 = 'Hinweistext zum Artikel';
    $order->Positionen[1]->cGesamtpreisLocalized[0] = '238,00 EUR';
    $order->Positionen[1]->cGesamtpreisLocalized[1] = '200,00 EUR';
    $order->Positionen[1]->cEinzelpreisLocalized[0] = '238,00 EUR';
    $order->Positionen[1]->cEinzelpreisLocalized[1] = '200,00 EUR';

    $order->Positionen[1]->nAusgeliefert       = 1;
    $order->Positionen[1]->nAusgeliefertGesamt = 1;
    $order->Positionen[1]->nOffenGesamt        = 0;

    $order->Steuerpositionen                     = [];
    $order->Steuerpositionen[0]                  = new stdClass();
    $order->Steuerpositionen[0]->cName           = 'inkl. 19% USt.';
    $order->Steuerpositionen[0]->fUst            = 19;
    $order->Steuerpositionen[0]->fBetrag         = 98.04;
    $order->Steuerpositionen[0]->cPreisLocalized = '98,04 EUR';

    $order->Waehrung                       = new stdClass();
    $order->Waehrung->cISO                 = 'EUR';
    $order->Waehrung->cName                = 'EUR';
    $order->Waehrung->cNameHTML            = '&euro;';
    $order->Waehrung->fFaktor              = 1;
    $order->Waehrung->cStandard            = 'Y';
    $order->Waehrung->cVorBetrag           = 'N';
    $order->Waehrung->cTrennzeichenCent    = ',';
    $order->Waehrung->cTrennzeichenTausend = '.';

    $order->Zahlungsart           = new stdClass();
    $order->Zahlungsart->cName    = 'Billpay';
    $order->Zahlungsart->cModulId = 'za_billpay_jtl';

    $order->Zahlungsinfo               = new stdClass();
    $order->Zahlungsinfo->cBankName    = 'Bankname';
    $order->Zahlungsinfo->cBLZ         = '3443234';
    $order->Zahlungsinfo->cKontoNr     = 'Kto12345';
    $order->Zahlungsinfo->cIBAN        = 'IB239293';
    $order->Zahlungsinfo->cBIC         = 'BIC3478';
    $order->Zahlungsinfo->cKartenNr    = 'KNR4834';
    $order->Zahlungsinfo->cGueltigkeit = '20.10.2010';
    $order->Zahlungsinfo->cCVV         = '1234';
    $order->Zahlungsinfo->cKartenTyp   = 'VISA';
    $order->Zahlungsinfo->cInhaber     = 'Max Mustermann';

    $order->Lieferadresse                   = new stdClass();
    $order->Lieferadresse->kLieferadresse   = 1;
    $order->Lieferadresse->cAnrede          = 'm';
    $order->Lieferadresse->cAnredeLocalized = 'Herr';
    $order->Lieferadresse->cVorname         = 'John';
    $order->Lieferadresse->cNachname        = 'Doe';
    $order->Lieferadresse->cStrasse         = 'Musterlieferstr.';
    $order->Lieferadresse->cHausnummer      = '77';
    $order->Lieferadresse->cAdressZusatz    = '2. Etage';
    $order->Lieferadresse->cPLZ             = '12345';
    $order->Lieferadresse->cOrt             = 'Musterlieferstadt';
    $order->Lieferadresse->cBundesland      = 'Lieferbundesland';
    $order->Lieferadresse->cLand            = 'Lieferland';
    $order->Lieferadresse->cTel             = '112345678';
    $order->Lieferadresse->cMobil           = '123456789';
    $order->Lieferadresse->cFax             = '12345678909';
    $order->Lieferadresse->cMail            = 'john.doe@example.com';

    $order->fWaehrungsFaktor = 1;

    //Lieferschein
    $order->oLieferschein_arr = [];

    $oLieferschein = new Lieferschein();
    $oLieferschein->setEmailVerschickt(false);
    $oLieferschein->oVersand_arr = [];
    $oVersand                    = new Versand();
    $oVersand->setLogistikURL('http://nolp.dhl.de/nextt-online-public/' .
        'report_popup.jsp?lang=de&zip=#PLZ#&idc=#IdentCode#');
    $oVersand->setIdentCode('123456');
    $oLieferschein->oVersand_arr[]  = $oVersand;
    $oLieferschein->oPosition_arr   = [];
    $oLieferschein->oPosition_arr[] = $order->Positionen[0];
    $oLieferschein->oPosition_arr[] = $order->Positionen[1];

    $order->oLieferschein_arr[] = $oLieferschein;

    $customer                   = new stdClass();
    $customer->fRabatt          = 0.00;
    $customer->fGuthaben        = 0.00;
    $customer->cAnrede          = 'm';
    $customer->Anrede           = 'Herr';
    $customer->cAnredeLocalized = 'Herr';
    $customer->cTitel           = 'Dr.';
    $customer->cVorname         = 'Max';
    $customer->cNachname        = 'Mustermann';
    $customer->cFirma           = 'Musterfirma';
    $customer->cStrasse         = 'Musterstrasse';
    $customer->cHausnummer      = '123';
    $customer->cPLZ             = '12345';
    $customer->cOrt             = 'Musterstadt';
    $customer->cLand            = 'Musterland';
    $customer->cTel             = '12345678';
    $customer->cFax             = '98765432';
    $customer->cMail            = $Einstellungen['emails']['email_master_absender'];
    $customer->cUSTID           = 'ust234';
    $customer->cBundesland      = 'NRW';
    $customer->cAdressZusatz    = 'Linker Hof';
    $customer->cMobil           = '01772322234';
    $customer->dGeburtstag      = '1981-10-10';
    $customer->cWWW             = 'http://max.de';
    $customer->kKundengruppe    = 1;

    $customerGroup                = new stdClass();
    $customerGroup->kKundengruppe = 1;
    $customerGroup->cName         = 'Endkunden';
    $customerGroup->nNettoPreise  = 0;

    $gutschein                 = new stdClass();
    $gutschein->cLocalizedWert = '5,00 EUR';
    $gutschein->cGrund         = 'Geburtstag';

    $coupon                        = new stdClass();
    $coupon->cName                 = 'Kuponname';
    $coupon->fWert                 = 5;
    $coupon->cWertTyp              = 'festpreis';
    $coupon->dGueltigAb            = '2007-11-07 17:05:00';
    $coupon->dGueltigBis           = '2008-11-07 17:05:00';
    $coupon->cCode                 = 'geheimcode';
    $coupon->nVerwendungenProKunde = 2;
    $coupon->AngezeigterName       = 'lokalisierter Name des Kupons';
    $coupon->cKuponTyp             = 'standard';
    $coupon->cLocalizedWert        = '5 EUR';
    $coupon->cLocalizedMBW         = '100,00 EUR';
    $coupon->fMindestbestellwert   = 100;
    $coupon->Artikel               = [];
    $coupon->Artikel[0]            = new stdClass();
    $coupon->Artikel[1]            = new stdClass();
    $coupon->Artikel[0]->cName     = 'Artikel eins';
    $coupon->Artikel[0]->cURL      = 'http://meinshop.de/artikel=1';
    $coupon->Artikel[1]->cName     = 'Artikel zwei';
    $coupon->Artikel[1]->cURL      = 'http://meinshop.de/artikel=2';
    $coupon->Kategorien            = [];
    $coupon->Kategorien[0]         = new stdClass();
    $coupon->Kategorien[1]         = new stdClass();
    $coupon->Kategorien[0]->cName  = 'Kategorie eins';
    $coupon->Kategorien[0]->cURL   = 'http://meinshop.de/kat=1';
    $coupon->Kategorien[1]->cName  = 'Kategorie zwei';
    $coupon->Kategorien[1]->cURL   = 'http://meinshop.de/kat=2';

    $msg             = new stdClass();
    $msg->cNachricht = 'Anfragetext...';
    $msg->cAnrede    = 'm';
    $msg->cVorname   = 'Max';
    $msg->cNachname  = 'Mustermann';
    $msg->cFirma     = 'Musterfirma';
    $msg->cMail      = 'max@musterman.de';
    $msg->cFax       = '34782034';
    $msg->cTel       = '34782035';
    $msg->cMobil     = '34782036';
    $msg->cBetreff   = 'Allgemeine Anfrage';

    $product                    = new stdClass();
    $product->cName             = 'LAN Festplatte IPDrive';
    $product->cArtNr            = 'AF8374';
    $product->cEinheit          = 'Stck.';
    $product->cLieferstatus     = '3-4 Tage';
    $product->fPreisEinzelNetto = 111.2069;
    $product->fPreis            = 368.1069;
    $product->fMwSt             = 19;
    $product->nAnzahl           = 1;
    $product->cURL              = 'LAN-Festplatte-IPDrive';

    $wishlist               = new stdClass();
    $wishlist->kWunschlsite = 5;
    $wishlist->kKunde       = 1480;
    $wishlist->cName        = 'Wunschzettel';
    $wishlist->nStandard    = 1;
    $wishlist->nOeffentlich = 0;
    $wishlist->cURLID       = '5686f6vv6c86v65nv6m8';
    $wishlist->dErstellt    = '2009-07-12 13:55:10';

    $wishlist->CWunschlistePos_arr                     = [];
    $wishlist->CWunschlistePos_arr[0]                  = new stdClass();
    $wishlist->CWunschlistePos_arr[0]->kWunschlistePos = 3;
    $wishlist->CWunschlistePos_arr[0]->kWunschliste    = 5;
    $wishlist->CWunschlistePos_arr[0]->kArtikel        = 261;
    $wishlist->CWunschlistePos_arr[0]->cArtikelName    = 'Hansu Televsion';
    $wishlist->CWunschlistePos_arr[0]->fAnzahl         = 2;
    $wishlist->CWunschlistePos_arr[0]->cKommentar      = 'Television';
    $wishlist->CWunschlistePos_arr[0]->dHinzugefuegt   = '2009-07-12 13:55:11';

    $wishlist->CWunschlistePos_arr[0]->Artikel                        = new stdClass();
    $wishlist->CWunschlistePos_arr[0]->Artikel->cName                 = 'LAN Festplatte IPDrive';
    $wishlist->CWunschlistePos_arr[0]->Artikel->cEinheit              = 'Stck.';
    $wishlist->CWunschlistePos_arr[0]->Artikel->fPreis                = 368.1069;
    $wishlist->CWunschlistePos_arr[0]->Artikel->fMwSt                 = 19;
    $wishlist->CWunschlistePos_arr[0]->Artikel->nAnzahl               = 1;
    $wishlist->CWunschlistePos_arr[0]->Artikel->cURL                  = 'LAN-Festplatte-IPDrive';
    $wishlist->CWunschlistePos_arr[0]->Artikel->Bilder                = [];
    $wishlist->CWunschlistePos_arr[0]->Artikel->Bilder[0]             = new stdClass();
    $wishlist->CWunschlistePos_arr[0]->Artikel->Bilder[0]->cPfadKlein = BILD_KEIN_ARTIKELBILD_VORHANDEN;

    $wishlist->CWunschlistePos_arr[1]                  = new stdClass();
    $wishlist->CWunschlistePos_arr[1]->kWunschlistePos = 4;
    $wishlist->CWunschlistePos_arr[1]->kWunschliste    = 5;
    $wishlist->CWunschlistePos_arr[1]->kArtikel        = 262;
    $wishlist->CWunschlistePos_arr[1]->cArtikelName    = 'Hansu Phone';
    $wishlist->CWunschlistePos_arr[1]->fAnzahl         = 1;
    $wishlist->CWunschlistePos_arr[1]->cKommentar      = 'Phone';
    $wishlist->CWunschlistePos_arr[1]->dHinzugefuegt   = '2009-07-12 13:55:18';

    $wishlist->CWunschlistePos_arr[1]->Artikel                        = new stdClass();
    $wishlist->CWunschlistePos_arr[1]->Artikel->cName                 = 'USB Connector';
    $wishlist->CWunschlistePos_arr[1]->Artikel->cEinheit              = 'Stck.';
    $wishlist->CWunschlistePos_arr[1]->Artikel->fPreis                = 89.90;
    $wishlist->CWunschlistePos_arr[1]->Artikel->fMwSt                 = 19;
    $wishlist->CWunschlistePos_arr[1]->Artikel->nAnzahl               = 1;
    $wishlist->CWunschlistePos_arr[1]->Artikel->cURL                  = 'USB-Connector';
    $wishlist->CWunschlistePos_arr[1]->Artikel->Bilder                = [];
    $wishlist->CWunschlistePos_arr[1]->Artikel->Bilder[0]             = new stdClass();
    $wishlist->CWunschlistePos_arr[1]->Artikel->Bilder[0]->cPfadKlein = BILD_KEIN_ARTIKELBILD_VORHANDEN;

    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr                                = [];
    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]                             = new stdClass();
    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->kWunschlistePosEigenschaft = 2;
    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->kWunschlistePos            = 4;
    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->kEigenschaft               = 2;
    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->kEigenschaftWert           = 3;
    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->cFreifeldWert              = '';
    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->cEigenschaftName           = 'Farbe';
    $wishlist->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->cEigenschaftWertName       = 'rot';

    $NewsletterEmpfaenger                     = new stdClass();
    $NewsletterEmpfaenger->kSprache           = 1;
    $NewsletterEmpfaenger->kKunde             = null;
    $NewsletterEmpfaenger->nAktiv             = 0;
    $NewsletterEmpfaenger->cAnrede            = 'w';
    $NewsletterEmpfaenger->cVorname           = 'Erika';
    $NewsletterEmpfaenger->cNachname          = 'Mustermann';
    $NewsletterEmpfaenger->cEmail             = 'test@example.com';
    $NewsletterEmpfaenger->cOptCode           = '88abd18fe51be05d775a2151fbb74bf7';
    $NewsletterEmpfaenger->cLoeschCode        = 'a14a986321ff6a4998e81b84056933d3';
    $NewsletterEmpfaenger->dEingetragen       = 'NOW()';
    $NewsletterEmpfaenger->dLetzterNewsletter = '_DBNULL_';
    $NewsletterEmpfaenger->cLoeschURL         = Shop::getURL() .
        '/newsletter.php?lang=ger&lc=a14a986321ff6a4998e81b84056933d3';
    $NewsletterEmpfaenger->cFreischaltURL     = Shop::getURL() .
        '/newsletter.php?lang=ger&fc=88abd18fe51be05d775a2151fbb74bf7';

    $Bestandskunde                = new stdClass();
    $Bestandskunde->kKunde        = 1379;
    $Bestandskunde->kKundengruppe = 1;
    $Bestandskunde->kSprache      = 1;
    $Bestandskunde->cKundenNr     = 1028;
    $Bestandskunde->cPasswort     = 'a725e241eceb20739d4617d6ae5a2cef';
    $Bestandskunde->cAnrede       = 'm';
    $Bestandskunde->Anrede        = 'Herr';
    $Bestandskunde->cTitel        = '';
    $Bestandskunde->cVorname      = 'Max';
    $Bestandskunde->cNachname     = 'Mustermann';
    $Bestandskunde->cFirma        = '';
    $Bestandskunde->cStrasse      = 'Beispielweg';
    $Bestandskunde->cHausnummer   = '5';
    $Bestandskunde->cAdressZusatz = '';
    $Bestandskunde->cPLZ          = 12345;
    $Bestandskunde->cOrt          = 'Musterhausen';
    $Bestandskunde->cBundesland   = '';
    $Bestandskunde->cLand         = 'DE';
    $Bestandskunde->cTel          = '';
    $Bestandskunde->cMobil        = '';
    $Bestandskunde->cFax          = '';
    $Bestandskunde->cMail         = 'test@example.com';
    $Bestandskunde->cUSTID        = '';
    $Bestandskunde->cWWW          = 'www.example.com';
    $Bestandskunde->fGuthaben     = 0.0;
    $Bestandskunde->cNewsletter   = '';
    $Bestandskunde->dGeburtstag   = '1980-12-03';
    $Bestandskunde->fRabatt       = 0.0;
    $Bestandskunde->cHerkunft     = '';
    $Bestandskunde->dErstellt     = '2016-07-06';
    $Bestandskunde->dVeraendert   = '2016-11-18 13:52:25';
    $Bestandskunde->cAktiv        = 'Y';
    $Bestandskunde->cAbgeholt     = 'Y';
    $Bestandskunde->nRegistriert  = 0;

    $BestandskundenBoni               = new stdClass();
    $BestandskundenBoni->kKunde       = 1379;
    $BestandskundenBoni->fGuthaben    = '2,00 &euro';
    $BestandskundenBoni->nBonuspunkte = 0;
    $BestandskundenBoni->dErhalten    = 'NOW()';

    $Neues_Passwort = 'geheim007';

    $Benachrichtigung            = new stdClass();
    $Benachrichtigung->cVorname  = $customer->cVorname;
    $Benachrichtigung->cNachname = $customer->cNachname;

    $sendStatus = true;

    foreach ($Sprachen as $Sprache) {
        $Sprache->kSprache = (int)$Sprache->kSprache;
        $oAGBWRB           = new stdClass();
        if ($customer->kKundengruppe > 0) {
            $oAGBWRB = $db->select(
                'ttext',
                ['kKundengruppe', 'kSprache'],
                [$customer->kKundengruppe, $Sprache->kSprache]
            );
        }
        $localized[$Sprache->kSprache] = $db->select(
            $cTableSprache,
            ['kEmailvorlage', 'kSprache'],
            [(int)$mailTpl->kEmailvorlage, (int)$Sprache->kSprache]
        );
        if (!empty($localized[$Sprache->kSprache])) {
            $cModulId = $mailTpl->cModulId;
            if (Request::verifyGPCDataInt('kPlugin') > 0) {
                $cModulId = 'kPlugin_' . Request::verifyGPCDataInt('kPlugin') . '_' . $cModulId;
            }
            $order->oEstimatedDelivery->localized = ShippingMethod::getDeliverytimeEstimationText(
                $order->oEstimatedDelivery->longestMin,
                $order->oEstimatedDelivery->longestMax
            );
            $order->cEstimatedDeliveryEx          = Date::dateAddWeekday(
                $order->dErstellt,
                $order->oEstimatedDelivery->longestMin
            )->format('d.m.Y') . ' - ' .
            Date::dateAddWeekday(
                $order->dErstellt,
                $order->oEstimatedDelivery->longestMax
            )->format('d.m.Y');

            $customer->kSprache                    = $Sprache->kSprache;
            $NewsletterEmpfaenger->kSprache        = $Sprache->kSprache;
            $obj                                   = new stdClass();
            $obj->tkunde                           = $customer;
            $obj->tkunde->cPasswortKlartext        = 'superGeheim';
            $obj->tkundengruppe                    = $customerGroup;
            $obj->tbestellung                      = $order;
            $obj->neues_passwort                   = $Neues_Passwort;
            $obj->passwordResetLink                = Shop::getURL() . '/pass.php?fpwh=ca68b243f0c1e7e57162055f248218fd';
            $obj->tgutschein                       = $gutschein;
            $obj->AGB                              = $oAGBWRB;
            $obj->WRB                              = $oAGBWRB;
            $obj->DSE                              = $oAGBWRB;
            $obj->tkupon                           = $coupon;
            $obj->tnachricht                       = $msg;
            $obj->tartikel                         = $product;
            $obj->twunschliste                     = $wishlist;
            $obj->tvonkunde                        = $obj->tkunde;
            $obj->tverfuegbarkeitsbenachrichtigung = $Benachrichtigung;
            $obj->NewsletterEmpfaenger             = $NewsletterEmpfaenger;
            $res                                   = sendeMail($cModulId, $obj);
            if ($res === false) {
                $sendStatus = false;
            }
        } else {
            $cHinweis .= 'Es existiert keine Emailvorlage: ' . $Sprache->cNameDeutsch . '<br/>';
        }
    }
    if ($sendStatus === true) {
        $cHinweis .= 'E-Mail wurde erfolgreich versendet.';
    } else {
        $cFehler = 'E-Mail konnte nicht versendet werden.';
    }
}
if (isset($_POST['Aendern'], $_POST['kEmailvorlage'])
    && (int)$_POST['Aendern'] === 1
    && (int)$_POST['kEmailvorlage'] > 0 && Form::validateToken()
) {
    $step                        = 'uebersicht';
    $kEmailvorlage               = (int)$_POST['kEmailvorlage'];
    $cUploadVerzeichnis          = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $oEmailvorlageSpracheTMP_arr = $db->selectAll(
        $cTableSprache,
        'kEmailvorlage',
        (int)$_POST['kEmailvorlage'],
        'cPDFS, cDateiname, kSprache'
    );
    $oEmailvorlageSprache_arr    = [];
    foreach ($oEmailvorlageSpracheTMP_arr as $oEmailvorlageSpracheTMP) {
        $oEmailvorlageSprache_arr[$oEmailvorlageSpracheTMP->kSprache] = $oEmailvorlageSpracheTMP;
    }
    $Sprachen = $db->query(
        'SELECT * 
            FROM tsprache 
            ORDER BY cShopStandard DESC, cNameDeutsch',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (!isset($localized) || is_array($localized)) {
        $localized = new stdClass();
    }
    $localized->kEmailvorlage = (int)$_POST['kEmailvorlage'];
    $cAnhangError_arr         = [];

    $revision = new Revision();
    $revision->addRevision('mail', (int)$_POST['kEmailvorlage'], true);

    foreach ($Sprachen as $Sprache) {
        // PDFs hochladen
        $filenames         = [];
        $pdfFiles          = [];
        $cPDFSTMP_arr      = isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS)
            ? bauePDFArray($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS)
            : [];
        $cDateinameTMP_arr = isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname)
            ? baueDateinameArray($oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname)
            : [];
        if (!isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS)
            || strlen($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS) === 0
            || count($cPDFSTMP_arr) < 3
        ) {
            if (count($cPDFSTMP_arr) < 3) {
                foreach ($cPDFSTMP_arr as $i => $cPDFSTMP) {
                    $pdfFiles[] = $cPDFSTMP;

                    if (strlen($_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache]) > 0) {
                        $regs = [];
                        preg_match(
                            '/[A-Za-z0-9_-]+/',
                            $_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache],
                            $regs
                        );
                        if (strlen($regs[0]) === strlen($_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache])) {
                            $filenames[] = $_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache];
                            unset($_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache]);
                        } else {
                            $cFehler .= 'Fehler: Ihr Dateiname "' .
                                $_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache] .
                                '" enthält unzulässige Zeichen (Erlaubt sind A-Z, a-z, 0-9, _ und -).<br />';
                            $nFehler  = 1;
                            break;
                        }
                    } else {
                        $filenames[] = $cDateinameTMP_arr[$i];
                    }
                }
            }

            for ($i = 1; $i <= 3; $i++) {
                if (isset($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name'])
                    && strlen($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name']) > 0
                    && strlen($_POST['dateiname_' . $i . '_' . $Sprache->kSprache]) > 0
                ) {
                    if ($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['size'] <= 2097152) {
                        if (!strrpos($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name'], ';')
                            && !strrpos($_POST['dateiname_' . $i . '_' . $Sprache->kSprache], ';')
                        ) {
                            $cPlugin = '';
                            if (Request::verifyGPCDataInt('kPlugin') > 0) {
                                $cPlugin = '_' . Request::verifyGPCDataInt('kPlugin');
                            }
                            $cUploadDatei = $cUploadVerzeichnis . $localized->kEmailvorlage .
                                '_' . $Sprache->kSprache . '_' . $i . $cPlugin . '.pdf';
                            if (!move_uploaded_file(
                                $_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['tmp_name'],
                                $cUploadDatei
                            )) {
                                $cFehler .= 'Fehler: Die Dateien konnten nicht geschrieben werden. ' .
                                    'Prüfen Sie bitte, ob das PDF Verzeichnis Schreibrechte besitzt.<br />';
                                $nFehler  = 1;
                                break;
                            }
                            $filenames[] = $_POST['dateiname_' . $i . '_' . $Sprache->kSprache];
                            $pdfFiles[]  = $localized->kEmailvorlage . '_' .
                                $Sprache->kSprache . '_' . $i . $cPlugin . '.pdf';
                        } else {
                            $cFehler .= 'Fehler: Bitte geben Sie zu jeder Datei ' .
                                'auch einen Dateinamen (Wunschnamen) ein.<br />';
                            $nFehler  = 1;
                            break;
                        }
                    } else {
                        $cFehler .= 'Fehler: Die Datei muss ein PDF sein und darf maximal 2MB groß sein.<br />';
                        $nFehler  = 1;
                        break;
                    }
                } elseif (isset(
                    $_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name'],
                    $_POST['dateiname_' . $i . '_' . $Sprache->kSprache]
                )
                    && strlen($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name']) > 0
                    && strlen($_POST['dateiname_' . $i . '_' . $Sprache->kSprache]) === 0
                ) {
                    $cFehlerAnhang_arr[$Sprache->kSprache][$i] = 1;
                    $cFehler                                  .= 'Fehler: Sie haben zu einem PDF keinen ' .
                        'Dateinamen angegeben.<br />';
                    $nFehler                                   = 1;
                    break;
                }
            }
        } else {
            $pdfFiles = bauePDFArray($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS);
            foreach ($pdfFiles as $i => $pdf) {
                $j   = $i + 1;
                $idx = 'dateiname_' . $j . '_' . $Sprache->kSprache;
                if (strlen($_POST['dateiname_' . $j . '_' . $Sprache->kSprache]) > 0 && strlen($pdfFiles[$j - 1]) > 0) {
                    $regs = [];
                    preg_match('/[A-Za-z0-9_-]+/', $_POST[$idx], $regs);
                    if (strlen($regs[0]) === strlen($_POST[$idx])) {
                        $filenames[] = $_POST[$idx];
                    } else {
                        $cFehler .= 'Fehler: Ihr Dateiname "' . $_POST[$idx] .
                            '" enthält unzulässige Zeichen (Erlaubt sind A-Z, a-z, 0-9, _ und -).<br />';
                        $nFehler  = 1;
                        break;
                    }
                } else {
                    $cFehler .= 'Fehler: Sie haben zu einem PDF keinen Dateinamen angegeben.<br />';
                    $nFehler  = 1;
                    break;
                }
            }
        }
        $localized->cDateiname   = '';
        $localized->kSprache     = $Sprache->kSprache;
        $localized->cBetreff     = $_POST['cBetreff_' . $Sprache->kSprache] ?? null;
        $localized->cContentHtml = $_POST['cContentHtml_' . $Sprache->kSprache] ?? null;
        $localized->cContentText = $_POST['cContentText_' . $Sprache->kSprache] ?? null;
        $localized->cPDFS        = '';
        if (count($pdfFiles) > 0) {
            $localized->cPDFS = ';' . implode(';', $pdfFiles) . ';';
        } elseif (isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS)
            && strlen($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS) > 0
        ) {
            $localized->cPDFS = $oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS;
        }
        if (count($filenames) > 0) {
            $localized->cDateiname = ';' . implode(';', $filenames) . ';';
        } elseif (isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname)
            && strlen($oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname) > 0
        ) {
            $localized->cDateiname = $oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname;
        }
        if ($nFehler === 0) {
            $db->delete(
                $cTableSprache,
                ['kSprache', 'kEmailvorlage'],
                [
                    (int)$Sprache->kSprache,
                    (int)$_POST['kEmailvorlage']
                ]
            );
            $db->insert($cTableSprache, $localized);
            $mailSmarty = new Smarty\JTLSmarty(true, \Smarty\ContextType::MAIL);
            $mailSmarty->registerResource('db', new \Smarty\SmartyResourceNiceDB($db, \Smarty\ContextType::MAIL))
                       ->registerPlugin('function', 'includeMailTemplate', 'includeMailTemplate')
                       ->setCaching(Smarty::CACHING_OFF)
                       ->setDebugging(Smarty::DEBUG_OFF)
                       ->setCompileDir(PFAD_ROOT . PFAD_COMPILEDIR);
            if (MAILTEMPLATE_USE_SECURITY) {
                $mailSmarty->activateBackendSecurityMode();
            }
            try {
                $mailSmarty->fetch('db:html_' . $localized->kEmailvorlage .
                    '_' . $Sprache->kSprache . '_' . $cTableSprache);
                $mailSmarty->fetch('db:text_' . $localized->kEmailvorlage .
                    '_' . $Sprache->kSprache . '_' . $cTableSprache);
            } catch (Exception $e) {
                $oSmartyError->cText = $e->getMessage();
                $oSmartyError->nCode = 1;
            }
        }
    }
    $kEmailvorlage  = (int)$_POST['kEmailvorlage'];
    $_upd           = new stdClass();
    $_upd->cMailTyp = $_POST['cMailTyp'];
    $_upd->cAktiv   = $_POST['cEmailActive'];
    $_upd->nAKZ     = isset($_POST['nAKZ']) ? (int)$_POST['nAKZ'] : 0;
    $_upd->nAGB     = isset($_POST['nAGB']) ? (int)$_POST['nAGB'] : 0;
    $_upd->nWRB     = isset($_POST['nWRB']) ? (int)$_POST['nWRB'] : 0;
    $_upd->nWRBForm = isset($_POST['nWRBForm']) ? (int)$_POST['nWRBForm'] : 0;
    $_upd->nDSE     = isset($_POST['nDSE']) ? (int)$_POST['nDSE'] : 0;
    $db->update($cTable, 'kEmailvorlage', $kEmailvorlage, $_upd);

    // Einstellungen
    $db->delete($cTableSetting, 'kEmailvorlage', $kEmailvorlage);
    // Email Ausgangsadresse
    if (isset($_POST['cEmailOut']) && strlen($_POST['cEmailOut']) > 0) {
        saveEmailSetting($cTableSetting, $kEmailvorlage, 'cEmailOut', $_POST['cEmailOut']);
    }
    // Email Absendername
    if (isset($_POST['cEmailSenderName']) && strlen($_POST['cEmailSenderName']) > 0) {
        saveEmailSetting($cTableSetting, $kEmailvorlage, 'cEmailSenderName', $_POST['cEmailSenderName']);
    }
    // Email Kopie
    if (isset($_POST['cEmailCopyTo']) && strlen($_POST['cEmailCopyTo']) > 0) {
        saveEmailSetting($cTableSetting, $kEmailvorlage, 'cEmailCopyTo', $_POST['cEmailCopyTo']);
    }

    if ($nFehler === 1) {
        $step = 'prebearbeiten';
    } elseif ($oSmartyError->nCode == 0) {
        setzeFehler((int)$_POST['kEmailvorlage'], false, true);
        $cHinweis = 'Emailvorlage erfolgreich geändert.';
        $step     = 'uebersicht';
        $continue = (isset($_POST['continue']) && $_POST['continue'] === '1');
    } else {
        $nFehler = 1;
        $step    = 'prebearbeiten';
        $cFehler = '<b>Die E-Mail Vorlage ist fehlerhaft</b><br />' . $oSmartyError->cText;
        setzeFehler($_POST['kEmailvorlage'], true);
    }
}
if (((isset($_POST['kEmailvorlage']) && (int)$_POST['kEmailvorlage'] > 0 && $continue === true)
        || $step === 'prebearbeiten'
        || (isset($_GET['a']) && $_GET['a'] === 'pdfloeschen')
    ) && Form::validateToken()
) {
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $localized          = [];

    if (empty($_POST['kEmailvorlage']) || (int)$_POST['kEmailvorlage'] === 0) {
        $_POST['kEmailvorlage'] = (isset($_GET['a'], $_GET['kEmailvorlage']) && $_GET['a'] === 'pdfloeschen')
            ? $_GET['kEmailvorlage']
            : $kEmailvorlage;
    }
    // PDF loeschen
    if (isset($_GET['kS'], $_GET['a'], $_GET['token'])
        && $_GET['a'] === 'pdfloeschen'
        && $_GET['token'] === $_SESSION['jtl_token']
    ) {
        $_POST['kEmailvorlage'] = $_GET['kEmailvorlage'];
        $_POST['kS']            = $_GET['kS'];
        $oEmailvorlageSprache   = $db->select(
            $cTableSprache,
            'kEmailvorlage',
            (int)$_POST['kEmailvorlage'],
            'kSprache',
            (int)$_POST['kS'],
            null,
            null,
            false,
            'cPDFS, cDateiname'
        );
        $pdfFiles               = bauePDFArray($oEmailvorlageSprache->cPDFS);

        if (is_array($pdfFiles) && count($pdfFiles) > 0) {
            foreach ($pdfFiles as $pdf) {
                if (file_exists($cUploadVerzeichnis . $pdf)) {
                    @unlink($cUploadVerzeichnis . $pdf);
                }
            }
        }
        $upd             = new stdClass();
        $upd->cPDFS      = '';
        $upd->cDateiname = '';
        $db->update(
            $cTableSprache,
            ['kEmailvorlage', 'kSprache'],
            [
                (int)$_POST['kEmailvorlage'],
                (int)$_POST['kS']
            ],
            $upd
        );
        $cHinweis .= 'Ihre Dateianhänge für Ihre gewählte Sprache, wurden erfolgreich gelöscht.<br />';
    }

    $step       = 'bearbeiten';
    $cFromTable = isset($_REQUEST['kPlugin']) ? $cTablePluginSetting : $cTableSetting;

    $Sprachen    = Sprache::getAllLanguages();
    $mailTpl     = $db->select($cTable, 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
    $config      = $db->selectAll($cFromTable, 'kEmailvorlage', (int)$mailTpl->kEmailvorlage);
    $configAssoc = [];
    foreach ($config as $oEmailEinstellung) {
        $configAssoc[$oEmailEinstellung->cKey] = $oEmailEinstellung->cValue;
    }

    foreach ($Sprachen as $Sprache) {
        $localized[$Sprache->kSprache] = $db->select(
            $cTableSprache,
            'kEmailvorlage',
            (int)$_POST['kEmailvorlage'],
            'kSprache',
            (int)$Sprache->kSprache
        );
        $pdfFiles                      = [];
        $filenames                     = [];
        if (!empty($localized[$Sprache->kSprache]->cPDFS)) {
            $cPDFSTMP_arr = bauePDFArray($localized[$Sprache->kSprache]->cPDFS);
            foreach ($cPDFSTMP_arr as $cPDFSTMP) {
                $pdfFiles[] = $cPDFSTMP;
            }
            $cDateinameTMP_arr = baueDateinameArray($localized[$Sprache->kSprache]->cDateiname);
            foreach ($cDateinameTMP_arr as $cDateinameTMP) {
                $filenames[] = $cDateinameTMP;
            }
        }
        if (!isset($localized[$Sprache->kSprache]) ||
            $localized[$Sprache->kSprache] === false) {
            $localized[$Sprache->kSprache] = new stdClass();
        }
        $localized[$Sprache->kSprache]->cPDFS_arr      = $pdfFiles;
        $localized[$Sprache->kSprache]->cDateiname_arr = $filenames;
    }
    $smarty->assign('Sprachen', $Sprachen)
           ->assign('oEmailEinstellungAssoc_arr', $configAssoc)
           ->assign('cUploadVerzeichnis', $cUploadVerzeichnis);
}

if ($step === 'uebersicht') {
    $smarty->assign('emailvorlagen', $db->selectAll('temailvorlage', [], [], '*', 'cModulId'))
           ->assign('oPluginEmailvorlage_arr', $db->selectAll('tpluginemailvorlage', [], [], '*', 'cModulId'));
}

if ($step === 'bearbeiten') {
    $smarty->assign('Emailvorlage', $mailTpl)
           ->assign('Emailvorlagesprache', $localized);
}
$smarty->assign('kPlugin', Request::verifyGPCDataInt('kPlugin'))
       ->assign('cFehlerAnhang_arr', $cFehlerAnhang_arr)
       ->assign('step', $step)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('Einstellungen', $Einstellungen);

$smarty->display('emailvorlagen.tpl');

/**
 * @param string $cPDF
 * @return array
 */
function bauePDFArray($cPDF)
{
    $cPDFTMP_arr = explode(';', $cPDF);
    $cPDF_arr    = [];
    if (count($cPDFTMP_arr) > 0) {
        foreach ($cPDFTMP_arr as $cPDFTMP) {
            if (strlen($cPDFTMP) > 0) {
                $cPDF_arr[] = $cPDFTMP;
            }
        }
    }

    return $cPDF_arr;
}

/**
 * @param string $cDateiname
 * @return array
 */
function baueDateinameArray($cDateiname)
{
    $cDateinameTMP_arr = explode(';', $cDateiname);
    $cDateiname_arr    = [];
    if (count($cDateinameTMP_arr) > 0) {
        foreach ($cDateinameTMP_arr as $cDateinameTMP) {
            if (strlen($cDateinameTMP) > 0) {
                $cDateiname_arr[] = $cDateinameTMP;
            }
        }
    }

    return $cDateiname_arr;
}

/**
 * @param int  $kEmailvorlage
 * @param bool $bFehler
 * @param bool $bForce
 */
function setzeFehler($kEmailvorlage, $bFehler = true, $bForce = false)
{
    $cAktiv           = $bFehler ? 'N' : 'Y';
    $upd              = new stdClass();
    $upd->nFehlerhaft = (int)$bFehler;
    if (!$bForce) {
        $upd->cAktiv = $cAktiv;
    }
    Shop::Container()->getDB()->update('temailvorlage', 'kEmailvorlage', (int)$kEmailvorlage, $upd);
}

/**
 * @param string $cTableSetting
 * @param int    $kEmailvorlage
 * @param string $cKey
 * @param string $cValue
 */
function saveEmailSetting($cTableSetting, $kEmailvorlage, $cKey, $cValue)
{
    if ((int)$kEmailvorlage > 0 && strlen($cTableSetting) > 0 && strlen($cKey) > 0 && strlen($cValue) > 0) {
        $oEmailvorlageEinstellung                = new stdClass();
        $oEmailvorlageEinstellung->kEmailvorlage = (int)$kEmailvorlage;
        $oEmailvorlageEinstellung->cKey          = $cKey;
        $oEmailvorlageEinstellung->cValue        = $cValue;

        Shop::Container()->getDB()->insert($cTableSetting, $oEmailvorlageEinstellung);
    }
}
