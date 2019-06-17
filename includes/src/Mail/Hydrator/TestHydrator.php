<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Hydrator;

use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferschein;
use JTL\Checkout\Versand;
use JTL\Customer\Kundengruppe;
use JTL\DB\ReturnType;
use JTL\Helpers\Date;
use JTL\Helpers\ShippingMethod;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use stdClass;

/**
 * Class TestHydrator
 * @package JTL\Mail\Hydrator
 */
class TestHydrator extends DefaultsHydrator
{
    /**
     * @inheritdoc
     */
    public function hydrate(?object $data, object $language): void
    {
        parent::hydrate($data, $language);
        $lang = Shop::Lang();
        $all  = LanguageHelper::getAllLanguages(1);
        $lang->setzeSprache($all[$language->kSprache]->cISO);

        $langID        = (int)$language->kSprache;
        $msg           = $this->getMessage();
        $customerBonus = $this->getBonus();
        $customerGroup = (new Kundengruppe())->loadDefaultGroup();
        $order         = $this->getOrder($langID);
        $customer      = $this->getCustomer($langID, $customerGroup->getID());
        $checkbox      = $this->getCheckbox();
        $oAGBWRB       = $this->db->select(
            'ttext',
            ['kKundengruppe', 'kSprache'],
            [$customer->kKundengruppe, $langID]
        );

        $this->smarty->assign('oKunde', $customer)
            ->assign('oMailObjekt', $this->getStatusMail())
            ->assign('Verfuegbarkeit_arr', ['cArtikelName_arr' => [], 'cHinweis' => ''])
            ->assign('BestandskundenBoni', (object)['fGuthaben' => Preise::getLocalizedPriceString(1.23)])
            ->assign('cAnzeigeOrt', 'Example')
            ->assign('oSprache', $language)
            ->assign('oCheckBox', $checkbox)
            ->assign('Kunde', $customer)
            ->assign('Kundengruppe', $customerGroup)
            ->assign('cAnredeLocalized', Shop::Lang()->get('salutationM'))
            ->assign('Bestellung', $order)
            ->assign('Neues_Passwort', 'geheim007')
            ->assign('passwordResetLink', Shop::getURL() . '/pass.php?fpwh=ca68b243f0c1e7e57162055f248218fd')
            ->assign('Gutschein', $this->getGift())
            ->assign('AGB', $oAGBWRB)
            ->assign('WRB', $oAGBWRB)
            ->assign('DSE', $oAGBWRB)
            ->assign('URL_SHOP', Shop::getURL() . '/')
            ->assign('Kupon', $this->getCoupon())
            ->assign('couponTypes', Kupon::getCouponTypes())
            ->assign('Nachricht', $msg)
            ->assign('Artikel', $this->getProduct())
            ->assign('Wunschliste', $this->getWishlist())
            ->assign('VonKunde', $customer)
            ->assign('Benachrichtigung', $this->getAvailabilityMessage())
            ->assign('NewsletterEmpfaenger', $this->getNewsletterRecipient($langID))
            ->assign('oBewertungGuthabenBonus', $customerBonus);
    }

    /**
     * @return stdClass
     */
    private function getStatusMail(): stdClass
    {
        $mail                                           = new stdClass();
        $mail->mail                                     = new stdClass();
        $mail->oAnzahlArtikelProKundengruppe            = 1;
        $mail->nAnzahlNeukunden                         = 21;
        $mail->nAnzahlNeukundenGekauft                  = 33;
        $mail->nAnzahlBestellungen                      = 17;
        $mail->nAnzahlBestellungenNeukunden             = 13;
        $mail->nAnzahlBesucher                          = 759;
        $mail->nAnzahlBesucherSuchmaschine              = 165;
        $mail->nAnzahlBewertungen                       = 99;
        $mail->nAnzahlBewertungenNichtFreigeschaltet    = 15;
        $mail->oAnzahlGezahltesGuthaben                 = -1;
        $mail->nAnzahlTags                              = 33;
        $mail->nAnzahlTagsNichtFreigeschaltet           = 22;
        $mail->nAnzahlGeworbenerKunden                  = 11;
        $mail->nAnzahlErfolgreichGeworbenerKunden       = 0;
        $mail->nAnzahlVersendeterWunschlisten           = 0;
        $mail->nAnzahlDurchgefuehrteUmfragen            = -1;
        $mail->nAnzahlNewskommentare                    = 21;
        $mail->nAnzahlNewskommentareNichtFreigeschaltet = 11;
        $mail->nAnzahlProduktanfrageArtikel             = 1;
        $mail->nAnzahlProduktanfrageVerfuegbarkeit      = 2;
        $mail->nAnzahlVergleiche                        = 3;
        $mail->nAnzahlGenutzteKupons                    = 4;
        $mail->nAnzahlZahlungseingaengeVonBestellungen  = 5;
        $mail->nAnzahlVersendeterBestellungen           = 6;
        $mail->dVon                                     = '01.01.2019';
        $mail->dBis                                     = '31.01.2019';
        $mail->oLogEntry_arr                            = [];
        $mail->cIntervall                               = 'Monatliche Status-Email';

        return $mail;
    }

    /**
     * @return CheckBox
     */
    private function getCheckbox(): CheckBox
    {
        $id = $this->db->query('SELECT kCheckbox FROM tcheckbox LIMIT 1', ReturnType::SINGLE_OBJECT);

        return new CheckBox((int)($id->kCheckbox ?? 0));
    }

    /**
     * @return stdClass
     */
    private function getAvailabilityMessage(): stdClass
    {
        $msg            = new stdClass();
        $msg->cVorname  = 'Max';
        $msg->cNachname = 'Musterman';

        return $msg;
    }

    /**
     * @return stdClass
     */
    private function getGift(): stdClass
    {
        $gift                 = new stdClass();
        $gift->cLocalizedWert = '5,00 EUR';
        $gift->cGrund         = 'Geburtstag';

        return $gift;
    }

    /**
     * @return stdClass
     */
    private function getMessage(): stdClass
    {
        $msg                   = new stdClass();
        $msg->cNachricht       = 'Lorem ipsum dolor sit amet.';
        $msg->cAnrede          = 'm';
        $msg->cAnredeLocalized = Shop::Lang()->get('salutationM');
        $msg->cVorname         = 'Max';
        $msg->cNachname        = 'Mustermann';
        $msg->cFirma           = 'Musterfirma';
        $msg->cMail            = 'max@musterman.de';
        $msg->cFax             = '34782034';
        $msg->cTel             = '34782035';
        $msg->cMobil           = '34782036';
        $msg->cBetreff         = 'Allgemeine Anfrage';

        return $msg;
    }

    /**
     * @return stdClass
     */
    private function getWishlist(): stdClass
    {
        $wishlist                      = new stdClass();
        $wishlist->kWunschlsite        = 5;
        $wishlist->kKunde              = 1480;
        $wishlist->cName               = 'Wunschzettel';
        $wishlist->nStandard           = 1;
        $wishlist->nOeffentlich        = 0;
        $wishlist->cURLID              = '5686f6vv6c86v65nv6m8';
        $wishlist->dErstellt           = '2019-01-01 01:01:01';
        $wishlist->CWunschlistePos_arr = [];

        $position                                 = new stdClass();
        $position->kWunschlistePos                = 3;
        $position->kWunschliste                   = 5;
        $position->kArtikel                       = 261;
        $position->cArtikelName                   = 'Hansu Televsion';
        $position->fAnzahl                        = 2;
        $position->cKommentar                     = 'Television';
        $position->dHinzugefuegt                  = '2009-07-12 13:55:11';
        $position->Artikel                        = new stdClass();
        $position->Artikel->cName                 = 'LAN Festplatte IPDrive';
        $position->Artikel->cEinheit              = 'Stck.';
        $position->Artikel->fPreis                = 368.1069;
        $position->Artikel->fMwSt                 = 19;
        $position->Artikel->nAnzahl               = 1;
        $position->Artikel->cURL                  = 'LAN-Festplatte-IPDrive';
        $position->Artikel->Bilder                = [];
        $position->Artikel->Bilder[0]             = new stdClass();
        $position->Artikel->Bilder[0]->cPfadKlein = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        $position->CWunschlistePosEigenschaft_arr = [];

        $wishlist->CWunschlistePos_arr[] = $position;

        $position                                 = new stdClass();
        $position->kWunschlistePos                = 4;
        $position->kWunschliste                   = 5;
        $position->kArtikel                       = 262;
        $position->cArtikelName                   = 'Hansu Phone';
        $position->fAnzahl                        = 1;
        $position->cKommentar                     = 'Phone';
        $position->dHinzugefuegt                  = '2009-07-12 13:55:18';
        $position->Artikel                        = new stdClass();
        $position->Artikel->cName                 = 'USB Connector';
        $position->Artikel->cEinheit              = 'Stck.';
        $position->Artikel->fPreis                = 89.90;
        $position->Artikel->fMwSt                 = 19;
        $position->Artikel->nAnzahl               = 1;
        $position->Artikel->cURL                  = 'USB-Connector';
        $position->Artikel->Bilder                = [];
        $position->Artikel->Bilder[0]             = new stdClass();
        $position->Artikel->Bilder[0]->cPfadKlein = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        $position->CWunschlistePosEigenschaft_arr = [];

        $attr                                       = new stdClass();
        $attr->kWunschlistePosEigenschaft           = 2;
        $attr->kWunschlistePos                      = 4;
        $attr->kEigenschaft                         = 2;
        $attr->kEigenschaftWert                     = 3;
        $attr->cFreifeldWert                        = '';
        $attr->cEigenschaftName                     = 'Farbe';
        $attr->cEigenschaftWertName                 = 'rot';
        $position->CWunschlistePosEigenschaft_arr[] = $attr;

        $wishlist->CWunschlistePos_arr[] = $position;

        return $wishlist;
    }

    /**
     * @return stdClass
     */
    private function getCoupon(): stdClass
    {
        $coupon                        = new stdClass();
        $coupon->cName                 = 'Kuponname';
        $coupon->fWert                 = 5;
        $coupon->cWertTyp              = 'festpreis';
        $coupon->dGueltigAb            = '2019-01-01 17:05:00';
        $coupon->GueltigAb             = '2019-01-01 17:05:00';
        $coupon->dGueltigBis           = '2019-12-31 17:05:00';
        $coupon->GueltigBis            = '2019-12-31 17:05:00';
        $coupon->cCode                 = 'geheimcode';
        $coupon->nVerwendungen         = 100;
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

        return $coupon;
    }

    /**
     * @param int $langID
     * @param int $customerGroupID
     * @return stdClass
     */
    private function getCustomer(int $langID, int $customerGroupID): stdClass
    {
        $customer                    = new stdClass();
        $customer->fRabatt           = 0.00;
        $customer->fGuthaben         = 0.00;
        $customer->cAnrede           = 'm';
        $customer->Anrede            = 'Herr';
        $customer->cAnredeLocalized  = Shop::Lang()->get('salutationM');
        $customer->cTitel            = 'Dr.';
        $customer->cVorname          = 'Max';
        $customer->cNachname         = 'Mustermann';
        $customer->cFirma            = 'Musterfirma';
        $customer->cStrasse          = 'Musterstrasse';
        $customer->cHausnummer       = '123';
        $customer->cPLZ              = '12345';
        $customer->cOrt              = 'Musterstadt';
        $customer->cLand             = 'Musterland';
        $customer->cTel              = '12345678';
        $customer->cFax              = '98765432';
        $customer->cMail             = $this->settings['emails']['email_master_absender'];
        $customer->cUSTID            = 'ust234';
        $customer->cBundesland       = 'NRW';
        $customer->cAdressZusatz     = 'Linker Hof';
        $customer->cMobil            = '01772322234';
        $customer->dGeburtstag       = '1981-10-10';
        $customer->cWWW              = 'http://example.com';
        $customer->kKundengruppe     = $customerGroupID;
        $customer->kSprache          = $langID;
        $customer->cPasswortKlartext = 'superGeheim';

        return $customer;
    }

    /**
     * @param int $languageID
     * @return stdClass
     */
    private function getOrder(int $languageID): stdClass
    {
        $order                   = new stdClass();
        $order->kWaehrung        = $languageID;
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

        $order->Positionen = [];

        $position                           = new stdClass();
        $position->kArtikel                 = 1;
        $position->cName                    = 'LAN Festplatte IPDrive';
        $position->cArtNr                   = 'AF8374';
        $position->cEinheit                 = 'Stck.';
        $position->cLieferstatus            = '3-4 Tage';
        $position->fPreisEinzelNetto        = 111.2069;
        $position->fPreis                   = 368.1069;
        $position->fMwSt                    = 19;
        $position->nAnzahl                  = 2;
        $position->nPosTyp                  = 1;
        $position->cHinweis                 = 'Hinweistext zum Artikel';
        $position->cGesamtpreisLocalized[0] = '278,00 EUR';
        $position->cGesamtpreisLocalized[1] = '239,66 EUR';
        $position->cEinzelpreisLocalized[0] = '139,00 EUR';
        $position->cEinzelpreisLocalized[1] = '119,83 EUR';

        $position->WarenkorbPosEigenschaftArr                           = [];
        $position->WarenkorbPosEigenschaftArr[0]                        = new stdClass();
        $position->WarenkorbPosEigenschaftArr[0]->cEigenschaftName      = 'Kapazität';
        $position->WarenkorbPosEigenschaftArr[0]->cEigenschaftWertName  = '400GB';
        $position->WarenkorbPosEigenschaftArr[0]->fAufpreis             = 128.45;
        $position->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[0] = '149,00 EUR';
        $position->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[1] = '128,45 EUR';

        $position->nAusgeliefert       = 1;
        $position->nAusgeliefertGesamt = 1;
        $position->nOffenGesamt        = 1;
        $position->dMHD                = '2025-01-01';
        $position->dMHD_de             = '01.01.2025';
        $position->cChargeNr           = 'A2100698.b12';
        $position->cSeriennummer       = '465798132756';
        $order->Positionen[]           = $position;

        $position                           = new stdClass();
        $position->kArtikel                 = 2;
        $position->cName                    = 'Klappstuhl';
        $position->cArtNr                   = 'KS332';
        $position->cEinheit                 = 'Stck.';
        $position->cLieferstatus            = '1 Woche';
        $position->fPreisEinzelNetto        = 100;
        $position->fPreis                   = 200;
        $position->fMwSt                    = 19;
        $position->nAnzahl                  = 1;
        $position->nPosTyp                  = 2;
        $position->cHinweis                 = 'Hinweistext zum Artikel';
        $position->cGesamtpreisLocalized[0] = '238,00 EUR';
        $position->cGesamtpreisLocalized[1] = '200,00 EUR';
        $position->cEinzelpreisLocalized[0] = '238,00 EUR';
        $position->cEinzelpreisLocalized[1] = '200,00 EUR';

        $position->nAusgeliefert       = 1;
        $position->nAusgeliefertGesamt = 1;
        $position->nOffenGesamt        = 0;
        $order->Positionen[]           = $position;

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
        $order->Zahlungsart->cName    = 'Rechnung';
        $order->Zahlungsart->cModulId = 'za_rechnung_jtl';

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
        $order->Lieferadresse->cAnredeLocalized = Shop::Lang()->get('salutationM');
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

        $order->fWaehrungsFaktor  = 1;
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
        $oLieferschein->oPosition_arr[] = $position;
        $oLieferschein->oPosition_arr[] = $position;

        $order->oLieferschein_arr[] = $oLieferschein;

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

        return $order;
    }

    /**
     * @param int $languageID
     * @return stdClass
     */
    private function getNewsletterRecipient(int $languageID): stdClass
    {
        $recipient                     = new stdClass();
        $recipient->kSprache           = $languageID;
        $recipient->kKunde             = null;
        $recipient->nAktiv             = 0;
        $recipient->cAnrede            = 'w';
        $recipient->cVorname           = 'Erika';
        $recipient->cNachname          = 'Mustermann';
        $recipient->cEmail             = 'test@example.com';
        $recipient->cOptCode           = '88abd18fe51be05d775a2151fbb74bf7';
        $recipient->cLoeschCode        = 'a14a986321ff6a4998e81b84056933d3';
        $recipient->dEingetragen       = 'NOW()';
        $recipient->dLetzterNewsletter = '_DBNULL_';
        $recipient->cLoeschURL         = Shop::getURL() .
            '/newsletter.php?lang=ger&lc=a14a986321ff6a4998e81b84056933d3';
        $recipient->cFreischaltURL     = Shop::getURL() .
            '/newsletter.php?lang=ger&fc=88abd18fe51be05d775a2151fbb74bf7';

        return $recipient;
    }

    /**
     * @return stdClass
     */
    private function getProduct(): stdClass
    {
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

        return $product;
    }

    /**
     * @return stdClass
     */
    private function getBonus(): stdClass
    {
        $customerBonus                          = new stdClass();
        $customerBonus->kKunde                  = 1379;
        $customerBonus->fGuthaben               = '2,00 &euro';
        $customerBonus->nBonuspunkte            = 0;
        $customerBonus->dErhalten               = 'NOW()';
        $customerBonus->fGuthabenBonusLocalized = Preise::getLocalizedPriceString(2.00);

        return $customerBonus;
    }
}
