<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Backend\Revision;
use Helpers\Date;
use Helpers\Form;
use Helpers\Request;
use Helpers\ShippingMethod;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_EMAIL_TEMPLATE_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
/** @global Smarty\JTLSmarty $smarty */
$mailTpl             = null;
$hinweis             = '';
$cHinweis            = '';
$cFehler             = '';
$nFehler             = 0;
$continue            = true;
$emailTemplate       = null;
$localized           = [];
$attachmentErrors    = [];
$step                = 'uebersicht';
$conf                = Shop::getSettings([CONF_EMAILS]);
$smartyError         = new stdClass();
$smartyError->nCode  = 0;
$tableName           = 'temailvorlage';
$localizedTableName  = 'temailvorlagesprache';
$originalTableName   = 'temailvorlagespracheoriginal';
$settingsTableName   = 'temailvorlageeinstellungen';
$pluginSettingsTable = 'tpluginemailvorlageeinstellungen';
$db                  = Shop::Container()->getDB();
if (Request::verifyGPCDataInt('kPlugin') > 0) {
    $tableName          = 'tpluginemailvorlage';
    $localizedTableName = 'tpluginemailvorlagesprache';
    $originalTableName  = 'tpluginemailvorlagespracheoriginal';
    $settingsTableName  = 'tpluginemailvorlageeinstellungen';
}
if (isset($_GET['err'])) {
    setzeFehler($_GET['kEmailvorlage']);
    $cFehler = __('errorTemplate');
    if (is_array($_SESSION['last_error'])) {
        $cFehler .= '<br />' . $_SESSION['last_error']['message'];
        unset($_SESSION['last_error']);
    }
}
if (isset($_POST['resetConfirm']) && (int)$_POST['resetConfirm'] > 0) {
    $emailTemplate = $db->select($tableName, 'kEmailvorlage', (int)$_POST['resetConfirm']);
    if (isset($emailTemplate->kEmailvorlage) && $emailTemplate->kEmailvorlage > 0) {
        $step = 'zuruecksetzen';
        $smarty->assign('oEmailvorlage', $emailTemplate);
    }
}

if (isset($_POST['resetEmailvorlage'])
    && (int)$_POST['resetEmailvorlage'] === 1
    && (int)$_POST['kEmailvorlage'] > 0
    && Form::validateToken()
) {
    $emailTemplate = $db->select($tableName, 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
    if ($emailTemplate->kEmailvorlage > 0 && isset($_POST['resetConfirmJaSubmit'])) {
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
            'INSERT INTO ' . $localizedTableName . '
                SELECT *
                FROM ' . $originalTableName . '
                WHERE ' . $originalTableName . '.kEmailvorlage = ' . (int)$_POST['kEmailvorlage'],
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
                        $localizedTableName,
                        ['kEmailVorlage', 'kSprache'],
                        [(int)$_POST['kEmailvorlage'], (int)$_lang->kSprache],
                        $upd
                    );
                }
            }
        }
        $cHinweis .= __('successTemplateReset') . '<br />';
    }
}
if (isset($_POST['preview']) && (int)$_POST['preview'] > 0) {
    $availableLanguages      = $db->query(
        'SELECT * 
            FROM tsprache 
            ORDER BY cShopStandard DESC, cNameDeutsch',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $mailTpl                 = $db->select(
        $tableName,
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
    $customer->cMail            = $conf['emails']['email_master_absender'];
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
    $coupon->cKuponTyp             = Kupon::TYPE_STANDARD;
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

    $newsletterRecipient                     = new stdClass();
    $newsletterRecipient->kSprache           = 1;
    $newsletterRecipient->kKunde             = null;
    $newsletterRecipient->nAktiv             = 0;
    $newsletterRecipient->cAnrede            = 'w';
    $newsletterRecipient->cVorname           = 'Erika';
    $newsletterRecipient->cNachname          = 'Mustermann';
    $newsletterRecipient->cEmail             = 'test@example.com';
    $newsletterRecipient->cOptCode           = '88abd18fe51be05d775a2151fbb74bf7';
    $newsletterRecipient->cLoeschCode        = 'a14a986321ff6a4998e81b84056933d3';
    $newsletterRecipient->dEingetragen       = 'NOW()';
    $newsletterRecipient->dLetzterNewsletter = '_DBNULL_';
    $newsletterRecipient->cLoeschURL         = Shop::getURL() .
        '/newsletter.php?lang=ger&lc=a14a986321ff6a4998e81b84056933d3';
    $newsletterRecipient->cFreischaltURL     = Shop::getURL() .
        '/newsletter.php?lang=ger&fc=88abd18fe51be05d775a2151fbb74bf7';

    $existingCustomer                = new stdClass();
    $existingCustomer->kKunde        = 1379;
    $existingCustomer->kKundengruppe = 1;
    $existingCustomer->kSprache      = 1;
    $existingCustomer->cKundenNr     = 1028;
    $existingCustomer->cPasswort     = 'a725e241eceb20739d4617d6ae5a2cef';
    $existingCustomer->cAnrede       = 'm';
    $existingCustomer->Anrede        = 'Herr';
    $existingCustomer->cTitel        = '';
    $existingCustomer->cVorname      = 'Max';
    $existingCustomer->cNachname     = 'Mustermann';
    $existingCustomer->cFirma        = '';
    $existingCustomer->cStrasse      = 'Beispielweg';
    $existingCustomer->cHausnummer   = '5';
    $existingCustomer->cAdressZusatz = '';
    $existingCustomer->cPLZ          = 12345;
    $existingCustomer->cOrt          = 'Musterhausen';
    $existingCustomer->cBundesland   = '';
    $existingCustomer->cLand         = 'DE';
    $existingCustomer->cTel          = '';
    $existingCustomer->cMobil        = '';
    $existingCustomer->cFax          = '';
    $existingCustomer->cMail         = 'test@example.com';
    $existingCustomer->cUSTID        = '';
    $existingCustomer->cWWW          = 'www.example.com';
    $existingCustomer->fGuthaben     = 0.0;
    $existingCustomer->cNewsletter   = '';
    $existingCustomer->dGeburtstag   = '1980-12-03';
    $existingCustomer->fRabatt       = 0.0;
    $existingCustomer->cHerkunft     = '';
    $existingCustomer->dErstellt     = '2016-07-06';
    $existingCustomer->dVeraendert   = '2016-11-18 13:52:25';
    $existingCustomer->cAktiv        = 'Y';
    $existingCustomer->cAbgeholt     = 'Y';
    $existingCustomer->nRegistriert  = 0;

    $customerBonus               = new stdClass();
    $customerBonus->kKunde       = 1379;
    $customerBonus->fGuthaben    = '2,00 &euro';
    $customerBonus->nBonuspunkte = 0;
    $customerBonus->dErhalten    = 'NOW()';

    $availabilityMsg            = new stdClass();
    $availabilityMsg->cVorname  = $customer->cVorname;
    $availabilityMsg->cNachname = $customer->cNachname;

    $sendStatus = true;
    foreach ($availableLanguages as $lang) {
        $lang->kSprache = (int)$lang->kSprache;
        $oAGBWRB        = new stdClass();
        if ($customer->kKundengruppe > 0) {
            $oAGBWRB = $db->select(
                'ttext',
                ['kKundengruppe', 'kSprache'],
                [$customer->kKundengruppe, $lang->kSprache]
            );
        }
        $localized[$lang->kSprache] = $db->select(
            $localizedTableName,
            ['kEmailvorlage', 'kSprache'],
            [(int)$mailTpl->kEmailvorlage, (int)$lang->kSprache]
        );
        if (!empty($localized[$lang->kSprache])) {
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

            $customer->kSprache                    = $lang->kSprache;
            $newsletterRecipient->kSprache         = $lang->kSprache;
            $obj                                   = new stdClass();
            $obj->tkunde                           = $customer;
            $obj->tkunde->cPasswortKlartext        = 'superGeheim';
            $obj->tkundengruppe                    = $customerGroup;
            $obj->tbestellung                      = $order;
            $obj->neues_passwort                   = 'geheim007';
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
            $obj->tverfuegbarkeitsbenachrichtigung = $availabilityMsg;
            $obj->NewsletterEmpfaenger             = $newsletterRecipient;
            $res                                   = sendeMail($cModulId, $obj);
            if ($res === false) {
                $sendStatus = false;
            }
        } else {
            $cHinweis .= __('errorTemplateMissing') . $lang->cNameDeutsch . '<br/>';
        }
    }
    if ($sendStatus === true) {
        $cHinweis .= __('successEmailSend');
    } else {
        $cFehler = __('errorEmailSend');
    }
}
if (isset($_POST['Aendern'], $_POST['kEmailvorlage'])
    && (int)$_POST['Aendern'] === 1
    && (int)$_POST['kEmailvorlage'] > 0 && Form::validateToken()
) {
    $step          = 'uebersicht';
    $kEmailvorlage = (int)$_POST['kEmailvorlage'];
    $uploadDir     = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $localizedData = $db->selectAll(
        $localizedTableName,
        'kEmailvorlage',
        (int)$_POST['kEmailvorlage'],
        'cPDFS, cDateiname, kSprache'
    );
    $localizedTPLs = [];
    foreach ($localizedData as $translation) {
        $localizedTPLs[$translation->kSprache] = $translation;
    }
    $availableLanguages = $db->query(
        'SELECT * 
            FROM tsprache 
            ORDER BY cShopStandard DESC, cNameDeutsch',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (!isset($localized) || is_array($localized)) {
        $localized = new stdClass();
    }
    $localized->kEmailvorlage = (int)$_POST['kEmailvorlage'];

    $revision = new Revision();
    $revision->addRevision('mail', (int)$_POST['kEmailvorlage'], true);
    foreach ($availableLanguages as $lang) {
        $filenames    = [];
        $pdfFiles     = [];
        $tmpPDFs      = isset($localizedTPLs[$lang->kSprache]->cPDFS)
            ? bauePDFArray($localizedTPLs[$lang->kSprache]->cPDFS)
            : [];
        $tmpFileNames = isset($localizedTPLs[$lang->kSprache]->cDateiname)
            ? baueDateinameArray($localizedTPLs[$lang->kSprache]->cDateiname)
            : [];
        if (!isset($localizedTPLs[$lang->kSprache]->cPDFS)
            || strlen($localizedTPLs[$lang->kSprache]->cPDFS) === 0
            || count($tmpPDFs) < 3
        ) {
            if (count($tmpPDFs) < 3) {
                foreach ($tmpPDFs as $i => $cPDFSTMP) {
                    $pdfFiles[] = $cPDFSTMP;

                    if (strlen($_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache]) > 0) {
                        $regs = [];
                        preg_match(
                            '/[A-Za-z0-9_-]+/',
                            $_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache],
                            $regs
                        );
                        if (strlen($regs[0]) === strlen($_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache])) {
                            $filenames[] = $_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache];
                            unset($_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache]);
                        } else {
                            $cFehler .= sprintf(
                                __('errorFileName'),
                                $_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache]
                            ) . '<br />';
                            $nFehler  = 1;
                            break;
                        }
                    } else {
                        $filenames[] = $tmpFileNames[$i];
                    }
                }
            }

            for ($i = 1; $i <= 3; $i++) {
                if (isset($_FILES['pdf_' . $i . '_' . $lang->kSprache]['name'])
                    && strlen($_FILES['pdf_' . $i . '_' . $lang->kSprache]['name']) > 0
                    && strlen($_POST['dateiname_' . $i . '_' . $lang->kSprache]) > 0
                ) {
                    if ($_FILES['pdf_' . $i . '_' . $lang->kSprache]['size'] <= 2097152) {
                        if (!strrpos($_FILES['pdf_' . $i . '_' . $lang->kSprache]['name'], ';')
                            && !strrpos($_POST['dateiname_' . $i . '_' . $lang->kSprache], ';')
                        ) {
                            $cPlugin = '';
                            if (Request::verifyGPCDataInt('kPlugin') > 0) {
                                $cPlugin = '_' . Request::verifyGPCDataInt('kPlugin');
                            }
                            $cUploadDatei = $uploadDir . $localized->kEmailvorlage .
                                '_' . $lang->kSprache . '_' . $i . $cPlugin . '.pdf';
                            if (!move_uploaded_file(
                                $_FILES['pdf_' . $i . '_' . $lang->kSprache]['tmp_name'],
                                $cUploadDatei
                            )) {
                                $cFehler .= __('errorFileSave') . '<br />';
                                $nFehler  = 1;
                                break;
                            }
                            $filenames[] = $_POST['dateiname_' . $i . '_' . $lang->kSprache];
                            $pdfFiles[]  = $localized->kEmailvorlage . '_' .
                                $lang->kSprache . '_' . $i . $cPlugin . '.pdf';
                        } else {
                            $cFehler .= __('errorFileNameMissing') . '<br />';
                            $nFehler  = 1;
                            break;
                        }
                    } else {
                        $cFehler .= __('errorFileSizeType') . '<br />';
                        $nFehler  = 1;
                        break;
                    }
                } elseif (isset(
                    $_FILES['pdf_' . $i . '_' . $lang->kSprache]['name'],
                    $_POST['dateiname_' . $i . '_' . $lang->kSprache]
                )
                    && strlen($_FILES['pdf_' . $i . '_' . $lang->kSprache]['name']) > 0
                    && strlen($_POST['dateiname_' . $i . '_' . $lang->kSprache]) === 0
                ) {
                    $attachmentErrors[$lang->kSprache][$i] = 1;
                    $cFehler                              .= __('errorFileNamePdfMissing') . '<br />';
                    $nFehler                               = 1;
                    break;
                }
            }
        } else {
            $pdfFiles = bauePDFArray($localizedTPLs[$lang->kSprache]->cPDFS);
            foreach ($pdfFiles as $i => $pdf) {
                $j   = $i + 1;
                $idx = 'dateiname_' . $j . '_' . $lang->kSprache;
                if (strlen($_POST['dateiname_' . $j . '_' . $lang->kSprache]) > 0 && strlen($pdfFiles[$j - 1]) > 0) {
                    $regs = [];
                    preg_match('/[A-Za-z0-9_-]+/', $_POST[$idx], $regs);
                    if (strlen($regs[0]) === strlen($_POST[$idx])) {
                        $filenames[] = $_POST[$idx];
                    } else {
                        $cFehler .= __('errorFileName') . '<br />';
                        $nFehler  = 1;
                        break;
                    }
                } else {
                    $cFehler .= __('errorFileNamePdfMissing') . '<br />';
                    $nFehler  = 1;
                    break;
                }
            }
        }
        $localized->cDateiname   = '';
        $localized->kSprache     = $lang->kSprache;
        $localized->cBetreff     = $_POST['cBetreff_' . $lang->kSprache] ?? null;
        $localized->cContentHtml = $_POST['cContentHtml_' . $lang->kSprache] ?? null;
        $localized->cContentText = $_POST['cContentText_' . $lang->kSprache] ?? null;
        $localized->cPDFS        = '';
        if (count($pdfFiles) > 0) {
            $localized->cPDFS = ';' . implode(';', $pdfFiles) . ';';
        } elseif (isset($localizedTPLs[$lang->kSprache]->cPDFS)
            && strlen($localizedTPLs[$lang->kSprache]->cPDFS) > 0
        ) {
            $localized->cPDFS = $localizedTPLs[$lang->kSprache]->cPDFS;
        }
        if (count($filenames) > 0) {
            $localized->cDateiname = ';' . implode(';', $filenames) . ';';
        } elseif (isset($localizedTPLs[$lang->kSprache]->cDateiname)
            && strlen($localizedTPLs[$lang->kSprache]->cDateiname) > 0
        ) {
            $localized->cDateiname = $localizedTPLs[$lang->kSprache]->cDateiname;
        }
        if ($nFehler === 0) {
            $db->delete(
                $localizedTableName,
                ['kSprache', 'kEmailvorlage'],
                [
                    (int)$lang->kSprache,
                    (int)$_POST['kEmailvorlage']
                ]
            );
            $db->insert($localizedTableName, $localized);
            $mailSmarty = new Smarty\JTLSmarty(true, \Smarty\ContextType::MAIL);
            $mailSmarty->registerResource('db', new \Smarty\SmartyResourceNiceDB($db, \Smarty\ContextType::MAIL))
                       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'includeMailTemplate', 'includeMailTemplate')
                       ->setCaching(Smarty::CACHING_OFF)
                       ->setDebugging(Smarty::DEBUG_OFF)
                       ->setCompileDir(PFAD_ROOT . PFAD_COMPILEDIR);
            if (MAILTEMPLATE_USE_SECURITY) {
                $mailSmarty->activateBackendSecurityMode();
            }
            try {
                $mailSmarty->fetch('db:html_' . $localized->kEmailvorlage .
                    '_' . $lang->kSprache . '_' . $localizedTableName);
                $mailSmarty->fetch('db:text_' . $localized->kEmailvorlage .
                    '_' . $lang->kSprache . '_' . $localizedTableName);
            } catch (Exception $e) {
                $smartyError->cText = $e->getMessage();
                $smartyError->nCode = 1;
            }
        }
    }
    $kEmailvorlage = (int)$_POST['kEmailvorlage'];
    $upd           = new stdClass();
    $upd->cMailTyp = $_POST['cMailTyp'];
    $upd->cAktiv   = $_POST['cEmailActive'];
    $upd->nAKZ     = isset($_POST['nAKZ']) ? (int)$_POST['nAKZ'] : 0;
    $upd->nAGB     = isset($_POST['nAGB']) ? (int)$_POST['nAGB'] : 0;
    $upd->nWRB     = isset($_POST['nWRB']) ? (int)$_POST['nWRB'] : 0;
    $upd->nWRBForm = isset($_POST['nWRBForm']) ? (int)$_POST['nWRBForm'] : 0;
    $upd->nDSE     = isset($_POST['nDSE']) ? (int)$_POST['nDSE'] : 0;
    $db->update($tableName, 'kEmailvorlage', $kEmailvorlage, $upd);
    $db->delete($settingsTableName, 'kEmailvorlage', $kEmailvorlage);
    if (isset($_POST['cEmailOut']) && strlen($_POST['cEmailOut']) > 0) {
        saveEmailSetting($settingsTableName, $kEmailvorlage, 'cEmailOut', $_POST['cEmailOut']);
    }
    if (isset($_POST['cEmailSenderName']) && strlen($_POST['cEmailSenderName']) > 0) {
        saveEmailSetting($settingsTableName, $kEmailvorlage, 'cEmailSenderName', $_POST['cEmailSenderName']);
    }
    if (isset($_POST['cEmailCopyTo']) && strlen($_POST['cEmailCopyTo']) > 0) {
        saveEmailSetting($settingsTableName, $kEmailvorlage, 'cEmailCopyTo', $_POST['cEmailCopyTo']);
    }

    if ($nFehler === 1) {
        $step = 'prebearbeiten';
    } elseif ($smartyError->nCode === 0) {
        setzeFehler((int)$_POST['kEmailvorlage'], false, true);
        $cHinweis = __('successTemplateEdit');
        $step     = 'uebersicht';
        $continue = (isset($_POST['continue']) && $_POST['continue'] === '1');
    } else {
        $nFehler = 1;
        $step    = 'prebearbeiten';
        $cFehler = __('errorTemplate') . '<br />' . $smartyError->cText;
        setzeFehler($_POST['kEmailvorlage']);
    }
}
if (((isset($_POST['kEmailvorlage']) && (int)$_POST['kEmailvorlage'] > 0 && $continue === true)
        || $step === 'prebearbeiten'
        || (isset($_GET['a']) && $_GET['a'] === 'pdfloeschen')
    ) && Form::validateToken()
) {
    $uploadDir = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $localized = [];
    if (empty($_POST['kEmailvorlage']) || (int)$_POST['kEmailvorlage'] === 0) {
        $_POST['kEmailvorlage'] = (isset($_GET['a'], $_GET['kEmailvorlage']) && $_GET['a'] === 'pdfloeschen')
            ? $_GET['kEmailvorlage']
            : $kEmailvorlage;
    }
    if (isset($_GET['kS'], $_GET['a'], $_GET['token'])
        && $_GET['a'] === 'pdfloeschen'
        && $_GET['token'] === $_SESSION['jtl_token']
    ) {
        $_POST['kEmailvorlage'] = $_GET['kEmailvorlage'];
        $_POST['kS']            = $_GET['kS'];
        $localizedData          = $db->select(
            $localizedTableName,
            'kEmailvorlage',
            (int)$_POST['kEmailvorlage'],
            'kSprache',
            (int)$_POST['kS'],
            null,
            null,
            false,
            'cPDFS, cDateiname'
        );
        $pdfFiles               = bauePDFArray($localizedData->cPDFS);
        foreach ($pdfFiles as $pdf) {
            if (file_exists($uploadDir . $pdf)) {
                @unlink($uploadDir . $pdf);
            }
        }
        $upd             = new stdClass();
        $upd->cPDFS      = '';
        $upd->cDateiname = '';
        $db->update(
            $localizedTableName,
            ['kEmailvorlage', 'kSprache'],
            [
                (int)$_POST['kEmailvorlage'],
                (int)$_POST['kS']
            ],
            $upd
        );
        $cHinweis .= __('successFileAppendixDelete') . '<br />';
    }

    $step  = 'bearbeiten';
    $table = isset($_REQUEST['kPlugin']) ? $pluginSettingsTable : $settingsTableName;

    $availableLanguages = Sprache::getAllLanguages();
    $mailTpl            = $db->select($tableName, 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
    $config             = $db->selectAll($table, 'kEmailvorlage', (int)$mailTpl->kEmailvorlage);
    $configAssoc        = [];
    foreach ($config as $item) {
        $configAssoc[$item->cKey] = $item->cValue;
    }
    foreach ($availableLanguages as $lang) {
        $localized[$lang->kSprache] = $db->select(
            $localizedTableName,
            'kEmailvorlage',
            (int)$_POST['kEmailvorlage'],
            'kSprache',
            (int)$lang->kSprache
        );
        $pdfFiles                   = [];
        $filenames                  = [];
        if (!empty($localized[$lang->kSprache]->cPDFS)) {
            $tmpPDFs = bauePDFArray($localized[$lang->kSprache]->cPDFS);
            foreach ($tmpPDFs as $cPDFSTMP) {
                $pdfFiles[] = $cPDFSTMP;
            }
            $tmpFileNames = baueDateinameArray($localized[$lang->kSprache]->cDateiname);
            foreach ($tmpFileNames as $cDateinameTMP) {
                $filenames[] = $cDateinameTMP;
            }
        }
        if (!isset($localized[$lang->kSprache]) ||
            $localized[$lang->kSprache] === false) {
            $localized[$lang->kSprache] = new stdClass();
        }
        $localized[$lang->kSprache]->cPDFS_arr      = $pdfFiles;
        $localized[$lang->kSprache]->cDateiname_arr = $filenames;
    }
    $smarty->assign('Sprachen', $availableLanguages)
           ->assign('oEmailEinstellungAssoc_arr', $configAssoc)
           ->assign('cUploadVerzeichnis', $uploadDir);
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
       ->assign('cFehlerAnhang_arr', $attachmentErrors)
       ->assign('step', $step)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('Einstellungen', $conf)
       ->display('emailvorlagen.tpl');

/**
 * @param string $cPDF
 * @return array
 */
function bauePDFArray($cPDF)
{
    $pdf = [];
    foreach (explode(';', $cPDF) as $cPDFTMP) {
        if (strlen($cPDFTMP) > 0) {
            $pdf[] = $cPDFTMP;
        }
    }

    return $pdf;
}

/**
 * @param string $fileName
 * @return array
 */
function baueDateinameArray($fileName)
{
    $fileNames = [];
    foreach (explode(';', $fileName) as $cDateinameTMP) {
        if (strlen($cDateinameTMP) > 0) {
            $fileNames[] = $cDateinameTMP;
        }
    }

    return $fileNames;
}

/**
 * @param int  $kEmailvorlage
 * @param bool $error
 * @param bool $force
 */
function setzeFehler($kEmailvorlage, $error = true, $force = false)
{
    $upd              = new stdClass();
    $upd->nFehlerhaft = (int)$error;
    if (!$force) {
        $upd->cAktiv = $error ? 'N' : 'Y';
    }
    Shop::Container()->getDB()->update('temailvorlage', 'kEmailvorlage', (int)$kEmailvorlage, $upd);
}

/**
 * @param string $settingsTable
 * @param int    $kEmailvorlage
 * @param string $key
 * @param string $value
 */
function saveEmailSetting($settingsTable, $kEmailvorlage, $key, $value)
{
    if ((int)$kEmailvorlage > 0 && strlen($settingsTable) > 0 && strlen($key) > 0 && strlen($value) > 0) {
        $conf                = new stdClass();
        $conf->kEmailvorlage = (int)$kEmailvorlage;
        $conf->cKey          = $key;
        $conf->cValue        = $value;

        Shop::Container()->getDB()->insert($settingsTable, $conf);
    }
}
