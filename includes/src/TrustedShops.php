<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';

define('SOAP_ERROR', -1);

// 0 = Test
// 1 = Produktiv
define('TS_MODUS', 1);

if (!defined('TS_BUYERPROT_CLASSIC') || !defined('TS_BUYERPROT_EXCELLENCE')) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'defines_inc.php';
}

if (TS_MODUS === 1) {
    // Produktiv
    //define('TS_SERVER', 'https://protection.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl');
    define('TS_SERVER', 'https://www.trustedshops.de/ts/services/TsProtection?wsdl');
    define('TS_SERVER_PROTECTION', 'https://www.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl');
    define('TS_CHECK_SERVER', 'https://www.trustedshops.de/ts/services/TsRating?wsdl');
    define('TS_RATING_SERVER', 'https://www.trustedshops.de/ts/services/TsRating?wsdl');
} else {
    // Test
    //define('TS_SERVER', 'https://protection-qa.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl');
    define('TS_SERVER', 'https://qa.trustedshops.de/ts/services/TsProtection?wsdl');
    define('TS_SERVER_PROTECTION', 'https://protection-qa.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl');
    define('TS_CHECK_SERVER', 'https://qa.trustedshops.de/ts/services/TsProtection?wsdl');
    define('TS_RATING_SERVER', 'https://qa.trustedshops.de/ts/services/TsRating?wsdl');
}

/**
 * Class TrustedShops
 */
class TrustedShops
{
    /**
     * @var int
     */
    public $kTrustedShopsZertifikat;

    /**
     * @var string
     */
    public $tsId;

    /**
     * @var string
     */
    public $tsProductId;

    /**
     * @var string
     */
    public $partnerPackage = 'JTL';

    /**
     * @var string
     */
    public $amount;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $paymentType;

    /**
     * @var string
     */
    public $buyerEmail;

    /**
     * @var string
     */
    public $shopCustomerID;

    /**
     * @var string
     */
    public $shopOrderID;

    /**
     * @var string
     */
    public $orderDate;

    /**
     * @var string
     */
    public $shopSystemVersion;

    /**
     * @var string
     */
    public $wsUser;

    /**
     * @var string
     */
    public $wsPassword;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var int
     */
    public $eType;

    /**
     * @var string
     */
    public $dChecked;

    /**
     * @var array
     */
    public $cBoxText;

    /**
     * @var string
     */
    public $cLogoURL;

    /**
     * @var string
     */
    public $cSpeicherungURL;

    /**
     * @var string
     */
    public $cBedingungURL;

    /**
     * @var object
     */
    public $oKaeuferschutzProdukte;

    /**
     * @var object
     */
    public $oKaeuferschutzProdukteDB;

    /**
     * @var object
     */
    public $oZertifikat;

    /**
     * @var array
     */
    public $cLogoSiegelBoxURL;

    /**
     * @var int
     */
    public $kTrustedshopsKundenbewertung;

    /**
     * Konstruktor
     *
     * @param string|int $tsId - Falls angegeben, wird das Zertifikat mit Käuferschutzprodukten aus der DB geholt
     * @param string     $cISOSprache
     */
    public function __construct($tsId = '', $cISOSprache = '')
    {
        if ($tsId !== -1 && strlen($tsId) > 0 && strlen($cISOSprache) > 0) {
            $this->fuelleTrustedShops($tsId, $cISOSprache);
        } elseif ($tsId !== -1 && strlen($tsId) > 0) {
            $this->fuelleTrustedShops($tsId);
        } elseif (strlen($cISOSprache) > 0) {
            $this->fuelleTrustedShops($tsId, $cISOSprache);
        }
        if ($this->eType === TS_BUYERPROT_CLASSIC) {
            $this->deaktiviereZertifikat($tsId, $cISOSprache);
        }
    }

    /**
     * @param string $tsId
     * @param string $cISOSprache
     * @return $this
     */
    public function fuelleTrustedShops($tsId, $cISOSprache = ''): self
    {
        $conf    = Shop::getSettings([CONF_GLOBAL]);
        $cacheID = 'jtl_ts_' . $tsId . '_' . $cISOSprache;
        if (($artikel = Shop::Cache()->get($cacheID)) !== false) {
            foreach (get_object_vars($artikel) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        if ($tsId != -1 && strlen($tsId) > 0 && strlen($cISOSprache) > 0) {
            $this->oZertifikat = $this->gibTrustedShopsZertifikatISO($cISOSprache, $tsId);
            if (strlen($this->oZertifikat->cTSID) > 0) {
                $this->kTrustedShopsZertifikat = $this->oZertifikat->kTrustedShopsZertifikat;
                $this->tsId                    = $this->oZertifikat->cTSID;
                $this->wsUser                  = $this->oZertifikat->cWSUser;
                $this->wsPassword              = $this->oZertifikat->cWSPasswort;
                $this->nAktiv                  = $this->oZertifikat->nAktiv;
                $this->eType                   = $this->oZertifikat->eType;
                $this->dChecked                = $this->oZertifikat->dChecked;
            }
        } elseif ($tsId == -1) {
            $this->oZertifikat = $this->gibTrustedShopsZertifikatISO($cISOSprache);
            if (isset($this->oZertifikat->cTSID) && strlen($this->oZertifikat->cTSID) > 0) {
                $this->kTrustedShopsZertifikat = $this->oZertifikat->kTrustedShopsZertifikat;
                $this->tsId                    = $this->oZertifikat->cTSID;
                $this->wsUser                  = $this->oZertifikat->cWSUser;
                $this->wsPassword              = $this->oZertifikat->cWSPasswort;
                $this->nAktiv                  = $this->oZertifikat->nAktiv;
                $this->eType                   = $this->oZertifikat->eType;
                $this->dChecked                = $this->oZertifikat->dChecked;
            }
        } else {
            $this->oZertifikat = $this->gibTrustedShopsZertifikatTSID($tsId);
            if (isset($this->oZertifikat->cTSID) && strlen($this->oZertifikat->cTSID) > 0) {
                $this->kTrustedShopsZertifikat = $this->oZertifikat->kTrustedShopsZertifikat;
                $this->tsId                    = $this->oZertifikat->cTSID;
                $this->wsUser                  = $this->oZertifikat->cWSUser;
                $this->wsPassword              = $this->oZertifikat->cWSPasswort;
                $this->nAktiv                  = $this->oZertifikat->nAktiv;
                $this->eType                   = $this->oZertifikat->eType;
                $this->dChecked                = $this->oZertifikat->dChecked;
            }
        }

        if (isset($this->oZertifikat->kTrustedShopsZertifikat) && $this->oZertifikat->kTrustedShopsZertifikat > 0 && $this->oZertifikat->nAktiv == 1) {
            $this->holeKaeuferschutzProdukteDB($this->oZertifikat->cISOSprache);

            $cShopName = 'JTL-Shop';
            if (isset($conf['global']['global_shopname']) && strlen($conf['global']['global_shopname']) > 0) {
                $cShopName = $conf['global']['global_shopname'];
            }

            $this->cLogoURL                = 'https://www.trustedshops.com/shop/certificate.php?shop_id=' . $this->tsId;
            $this->cSpeicherungURL         = 'https://www.trustedshops.com/shop/data_privacy.php?shop_id=' . $this->tsId;
            $this->cBedingungURL           = 'https://www.trustedshops.com/shop/protection_conditions.php?shop_id=' . $this->tsId;
            $this->cLogoSiegelBoxURL['de'] = 'https://www.trustedshops.de/profil/' . urlencode($cShopName) . '_' . $this->tsId . '.html';
            $this->cLogoSiegelBoxURL['en'] = 'https://www.trustedshops.com/profile/' . urlencode($cShopName) . '_' . $this->tsId . '.html';
            $this->cLogoSiegelBoxURL['nl'] = 'https://www.trustedshops.nl/shop/certificate.php?shop_id=' . $this->tsId;
            $this->cLogoSiegelBoxURL['it'] = 'https://www.trustedshops.it/shop/certificate.php?shop_id=' . $this->tsId;

            $this->cBoxText['de']      = "Die im K&auml;uferschutz enthaltene <a href='" . $this->cBedingungURL . "' target='_blank'>Trusted Shops Garantie</a> sichert Ihren Online-Kauf ab. " .
                "Mit der &Uuml;bermittlung und <a href='" . $this->cSpeicherungURL . "' target='_blank'>Speicherung</a> meiner E-Mail-Adresse zur " .
                "Abwicklung des K&auml;uferschutzes durch Trusted Shops bin ich einverstanden. <a href='" . $this->cBedingungURL . "' target='_blank'>Garantiebedingungen</a> f&uuml;r den K&auml;uferschutz.";
            $this->cBoxText['en']      = "The Trusted Shops buyer protection secures your online purchase. I agree with the transfer and <a href='" . $this->cSpeicherungURL .
                "' target='_blank'>saving</a> of my email address for the buyer protection handling by Trusted Shops. <a href='" . $this->cBedingungURL .
                "' target='_blank'>Conditions</a> for the buyer protection.";
            $this->cBoxText['nl']      = "De in de koperbescherming inbegrepen Trusted Shops Garantie beveiligt uw online aankoop. Ik ga akkoord met de doorgifte en de <a href='" .
                $this->cSpeicherungURL . "' target='_blank'>opslag</a> van mijn E-mailadres voor de afwikkeling van de koperbescherming door Trusted Shops. <a href='" .
                $this->cBedingungURL . "' target='_blank'>Garantievoorwaarden</a>  voor de koperbescherming.";
            $this->cBoxText['it']      = "La protezione acquirenti di Trusted Shops rende sicuri i tuoi acquisti online. Do il mio assenso al trasferimento e al <a href='" .
                $this->cSpeicherungURL . "' target='_blank'>salvataggio</a> del mio indirizzo e-mail per lelaborazione della protezione acquirenti da parte di Trusted Shops. <a href='" .
                $this->cBedingungURL . "' target='_blank'>Condizioni</a> della protezione acquirenti.";
            $this->cBoxText['default'] = $this->cBoxText['de'];
        }
        Shop::Cache()->set($cacheID, $this, [CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);

        return $this;
    }

    /**
     * Sendet nach einer Bestellung den Käuferschutzantrag nach TrustedShops
     *
     * @return bool
     */
    public function sendeBuchung(): bool
    {
        if ($this->pruefeZertifikat(StringHandler::convertISO2ISO639(Shop::getLanguageCode())) !== 1) {
            Shop::Container()->getLogService()->error('TS certificate is invalid.');

            return false;
        }
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);
        $returnValue = '';
        $wsdlUrl     = TS_SERVER_PROTECTION;
        if (PHPSettingsHelper::checkSOAP($wsdlUrl)) {
            $client      = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            $returnValue = $client->requestForProtectionV2(
                $this->tsId,
                $this->tsProductId,
                (float)$this->amount,
                $this->currency,
                $this->paymentType,
                $this->buyerEmail,
                $this->shopCustomerID,
                $this->shopOrderID,
                $this->orderDate,
                $this->shopSystemVersion,
                $this->wsUser,
                $this->wsPassword
            );
            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                Shop::Container()->getLogService()->error('TS Soap error: ' . $errorText);
            }
        } else {
            Shop::Container()->getLogService()->error('TS: SOAP could not be loaded.');
        }
        //check return value
        //negative return value is an error code, positive value is the protection number
        if ($returnValue == SOAP_ERROR) {
            Shop::Container()->getLogService()->error('TS Soap error');
        } elseif ($returnValue < 0) {
            switch ($returnValue) {
                case -10001 :
                    break;
            }
        } else {
            Shop::Container()->getLogService()->debug(
                'TS Web Service has successfully protected transaction, protectioner is ' .
                    $returnValue . ' - Amount: ' . (float)$this->amount
            );
        }

        return true;
    }

    /**
     * @return bool
     * @todo: $cISOSprache???
     */
    public function holeStatus(): bool
    {
        $returnValue = null;
        $wsdlUrl     = TS_SERVER;
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);

        if (PHPSettingsHelper::checkSOAP($wsdlUrl)) {
            $client      = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            $returnValue = $client->getRequestState($this->tsId);
            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                Shop::Container()->getLogService()->error($errorText);
            }
        } else {
            Shop::Container()->getLogService()->error('SOAP could not be loaded.');
        }
        // Geaendert aufgrund Mail von Herrn van der Wielen
        // Quote: 'Tatsächlich jedoch sollten Zertifikate mit den Status 'PRODUCTION', 'INTEGRATION' (und 'TEST') akzeptiert werden.'
        $languageIso = StringHandler::convertISO2ISO639(Shop::getLanguageCode());
        return (($returnValue->stateEnum === 'PRODUCTION'
                || $returnValue->stateEnum === 'TEST'
                || $returnValue->stateEnum === 'INTEGRATION')
            && $returnValue->certificationLanguage === $languageIso);
    }

    /**
     * Lädt anhand der tsID von der TrustedShops API, die Käuferschutzprodukte und
     * speichert diese direkt in die DB
     *
     * @param int $certID
     * @return $this
     */
    public function holeKaeuferschutzProdukte(int $certID): self
    {
        $returnValue = null;
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);
        $wsdlUrl = TS_SERVER;
        if (PHPSettingsHelper::checkSOAP($wsdlUrl)) {
            $client      = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            $returnValue = $client->getProtectionItems($this->tsId);
            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                Shop::Container()->getLogService()->error($errorText);
            }
        } else {
            Shop::Container()->getLogService()->error('SOAP could not be loaded.');
        }

        if (isset($returnValue->item) && is_object($returnValue->item)) {
            $oTmp                = $returnValue->item;
            $returnValue->item   = [];
            $returnValue->item[] = $oTmp;
        }

        $this->oKaeuferschutzProdukte = $returnValue;

        if (isset($this->oKaeuferschutzProdukte->item)
            && is_array($this->oKaeuferschutzProdukte->item)
            && count($this->oKaeuferschutzProdukte->item) > 0
        ) {
            $cLandISO = $_SESSION['Lieferadresse']->cLand ?? null;
            if (!$cLandISO) {
                $cLandISO = $_SESSION['TrustedShops']->oSprache->cISOSprache ?? $_SESSION['Kunde']->cLand;
            }

            unset($_SESSION['Warenkorb']);
            $_SESSION['Warenkorb'] = new Warenkorb();
            foreach ($this->oKaeuferschutzProdukte->item as $i => $oItem) {
                $this->oKaeuferschutzProdukte->item[$i]->protectedAmountDecimalLocalized =
                    Preise::getLocalizedPriceString($oItem->protectedAmountDecimal);

                if (isset($_SESSION['Warenkorb'], $_SESSION['Steuersatz'])
                    && (!Session::CustomerGroup()->isMerchant())
                ) {
                    $this->oKaeuferschutzProdukte->item[$i]->grossFeeLocalized = Preise::getLocalizedPriceString($oItem->netFee *
                        ((100 + (float)$_SESSION['Steuersatz'][Session::Cart()->gibVersandkostenSteuerklasse($cLandISO)]) / 100));
                    $this->oKaeuferschutzProdukte->item[$i]->cFeeTxt           = Shop::Lang()->get('incl', 'productDetails') .
                        ' ' . Shop::Lang()->get('vat', 'productDetails');
                } else {
                    $this->oKaeuferschutzProdukte->item[$i]->grossFeeLocalized = Preise::getLocalizedPriceString($oItem->netFee);
                    $this->oKaeuferschutzProdukte->item[$i]->cFeeTxt           = Shop::Lang()->get('excl', 'productDetails') .
                        ' ' . Shop::Lang()->get('vat', 'productDetails');
                }

                // DB Member füllen
                if (!isset($this->oKaeuferschutzProdukteDB->item[$i])) {
                    $this->oKaeuferschutzProdukteDB->item[$i] = new stdClass();
                }
                $this->oKaeuferschutzProdukteDB->item[$i]->nID        = $oItem->id;
                $this->oKaeuferschutzProdukteDB->item[$i]->nWert      = $oItem->protectedAmountDecimal;
                $this->oKaeuferschutzProdukteDB->item[$i]->cWaehrung  = $oItem->currency;
                $this->oKaeuferschutzProdukteDB->item[$i]->cProduktID = $oItem->tsProductID;
                $this->oKaeuferschutzProdukteDB->item[$i]->fNetto     = $oItem->netFee;
                $this->oKaeuferschutzProdukteDB->item[$i]->fBrutto    = $oItem->grossFee;
                $this->oKaeuferschutzProdukteDB->item[$i]->dDatum     = date('Y-m-d H:i:s');
            }
            $this->speicherKaeuferschutzProdukteDB($certID);
        }

        return $this;
    }

    /**
     * Lädt die Käuferschutzprodukte aus der Datenbank
     *
     * @param string $cISOSprache
     * @param bool   $bWaehrendBestellung
     * @return $this
     */
    public function holeKaeuferschutzProdukteDB($cISOSprache, $bWaehrendBestellung = false): self
    {
        $oZertifikat = $this->gibTrustedShopsZertifikatISO($cISOSprache);
        $cSQL        = '';
        if ($bWaehrendBestellung) {
            $cISOWaehrung = 'EUR';
            if (strlen($_SESSION['cWaehrungName']) > 0) {
                $cISOWaehrung = $_SESSION['cWaehrungName'];
            }

            $cSQL = " AND cWaehrung = '" . $cISOWaehrung . "' ";
        }

        if (isset($oZertifikat->kTrustedShopsZertifikat) && $oZertifikat->kTrustedShopsZertifikat > 0) {
            if ($this->oKaeuferschutzProdukteDB === null) {
                $this->oKaeuferschutzProdukteDB = new stdClass();
            }

            $this->oKaeuferschutzProdukteDB->item = Shop::Container()->getDB()->query(
                'SELECT *
                    FROM ttrustedeshopsprodukt
                    WHERE kTrustedShopsZertifikat = ' . $oZertifikat->kTrustedShopsZertifikat . $cSQL . '
                    ORDER BY cWaehrung, nWert',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }

        if ($this->oKaeuferschutzProdukteDB !== null
            && is_array($this->oKaeuferschutzProdukteDB->item)
            && count($this->oKaeuferschutzProdukteDB->item) > 0
        ) {
            $cLandISO = $_SESSION['Lieferadresse']->cLand ?? null;
            $cLandISO = !$cLandISO && isset($_SESSION['Kunde']->cLand)
                ? $_SESSION['Kunde']->cLand
                : null;
            foreach ($this->oKaeuferschutzProdukteDB->item as $i => $oItem) {
                if ($bWaehrendBestellung) {
                    $fPreis = $oItem->fNetto;
                    $nWert  = $oItem->nWert;
                    // Std Währung
                    $oWaehrung = Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y');
                    // Nicht Standard im Shop?
                    if (Session::Currency()->getID() !== (int)$oWaehrung->kWaehrung) {
                        $fPreis = $oItem->fNetto / Session::Currency()->getConversionFactor();
                        $nWert  = $oItem->nWert / Session::Currency()->getConversionFactor();
                    }
                    if ($this->oKaeuferschutzProdukte === null) {
                        $this->oKaeuferschutzProdukte = new stdClass();
                    }
                    if (!isset($this->oKaeuferschutzProdukte->item)) {
                        $this->oKaeuferschutzProdukte->item = [];
                    }
                    if (!isset($this->oKaeuferschutzProdukte->item[$i])) {
                        $this->oKaeuferschutzProdukte->item[$i] = new stdClass();
                    }
                    $this->oKaeuferschutzProdukte->item[$i]->protectedAmountDecimalLocalized = Preise::getLocalizedPriceString($nWert);
                    $this->oKaeuferschutzProdukte->item[$i]->id                              = $oItem->nID;
                    $this->oKaeuferschutzProdukte->item[$i]->currency                        = $oItem->cWaehrung;
                    $this->oKaeuferschutzProdukte->item[$i]->grossFee                        = $oItem->fBrutto;
                    $this->oKaeuferschutzProdukte->item[$i]->netFee                          = $oItem->fNetto;
                    $this->oKaeuferschutzProdukte->item[$i]->protectedAmountDecimal          = $oItem->nWert;
                    $this->oKaeuferschutzProdukte->item[$i]->tsProductID                     = $oItem->cProduktID;

                    if (!Session::CustomerGroup()->isMerchant() && isset($_SESSION['Warenkorb'], $_SESSION['Steuersatz'])) {
                        $this->oKaeuferschutzProdukte->item[$i]->grossFeeLocalized = Preise::getLocalizedPriceString(
                            $fPreis *
                            ((100 + (float)$_SESSION['Steuersatz'][Session::Cart()->gibVersandkostenSteuerklasse($cLandISO)]) / 100)
                        );
                        $this->oKaeuferschutzProdukte->item[$i]->cFeeTxt           = Shop::Lang()->get('incl', 'productDetails') .
                            ' ' .
                            Shop::Lang()->get('vat', 'productDetails');
                    } else {
                        $this->oKaeuferschutzProdukte->item[$i]->grossFeeLocalized = Preise::getLocalizedPriceString($fPreis);
                        $this->oKaeuferschutzProdukte->item[$i]->cFeeTxt           = Shop::Lang()->get('excl', 'productDetails') .
                            ' ' .
                            Shop::Lang()->get('vat', 'productDetails');
                    }
                }
                $this->oKaeuferschutzProdukteDB->item[$i]->protectedAmountDecimalLocalized =
                    Preise::getLocalizedPriceString($oItem->protectedAmountDecimal ?? 0);
            }
        }

        return $this;
    }

    /**
     * Speichert Käuferschutzprodukte in die Datenbank
     *
     * @param int $certID
     * @return bool
     */
    public function speicherKaeuferschutzProdukteDB($certID): bool
    {
        if ($certID > 0
            && is_array($this->oKaeuferschutzProdukteDB->item)
            && count($this->oKaeuferschutzProdukteDB->item) > 0
        ) {
            Shop::Container()->getDB()->delete('ttrustedeshopsprodukt', 'kTrustedShopsZertifikat', (int)$certID);
            foreach ($this->oKaeuferschutzProdukteDB->item as $oKaeuferschutzProdukt) {
                $oKaeuferschutzProdukt->kTrustedShopsZertifikat = $certID;
                if (!isset($oKaeuferschutzProdukt->kSprache)) {
                    $oKaeuferschutzProdukt->kSprache = 0;
                }
                unset($oKaeuferschutzProdukt->protectedAmountDecimalLocalized);
                Shop::Container()->getDB()->insert('ttrustedeshopsprodukt', $oKaeuferschutzProdukt);
            }

            return true;
        }

        return false;
    }

    /**
     * Prüft über die API von Trusted Shops, ob die tsID gültig ist
     *
     * @param string $cISOSprache
     * @param bool   $bSaved
     * @return int
     */
    public function pruefeZertifikat($cISOSprache, $bSaved = false): int
    {
        //@todo: $cTSClassicID is undefined?
        //$cTSClassicID = filterXSS($cTSClassicID);
        $returnValue = null;
        $bForce      = $bSaved;
        if ($this->dChecked !== null && $this->dChecked !== '0000-00-00 00:00:00') {
            $oDateTime = new DateTime($this->dChecked);
            $oDateTime->modify('+1 day');
            if ($oDateTime->format('U') < time()) {
                $bForce = true;
            }
        }

        if ($this->dChecked === null || $this->dChecked === '0000-00-00 00:00:00' || $bForce) {
            ini_set('soap.wsdl_cache_enabled', 1);

            $wsdlUrl = TS_SERVER;
            $cTSID   = $this->tsId;

            if (PHPSettingsHelper::checkSOAP($wsdlUrl)) {
                $client      = new SoapClient($wsdlUrl, ['exceptions' => 0]);
                $returnValue = $client->checkCertificate($cTSID);
                if (is_soap_fault($returnValue)) {
                    $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                    Shop::Container()->getLogService()->error(
                        'Bei der Zertifikatsprüfung von TrustedShops ist ein Fehler aufgetreten! Error: ' .
                        $errorText
                    );

                    return 11; // SOAP Fehler
                }
                Shop::Container()->getLogService()->error(
                    'Die Zertifikatsprüfung von TrustedShops ergab folgendes Ergebnis: ' .
                    print_r($returnValue, true)
                );

                $this->dChecked = date('Y-m-d H:i:s');
                if (!$bSaved) {
                    Shop::Container()->getDB()->query(
                        "UPDATE ttrustedshopszertifikat 
                            SET dChecked = '{$this->dChecked}' 
                            WHERE kTrustedShopsZertifikat = {$this->kTrustedShopsZertifikat}",
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                }
            } else {
                Shop::Container()->getLogService()->error('Es ist kein SOAP möglich um eine Zertifikatsprüfung von TrustedShops durchzuführen!');

                return 11; // SOAP Fehler
            }
        } else {
            return 1;
        } // keine Prüfung, OK zurückgeben

        // Geaendert aufgrund Mail von Herrn van der Wielen
        // Quote: 'Tatsächlich jedoch sollten Zertifikate mit den Status 'PRODUCTION', 'INTEGRATION' (und 'TEST') akzeptiert werden.'
        if (($returnValue->stateEnum === 'PRODUCTION'
                || $returnValue->stateEnum === 'TEST'
                || $returnValue->stateEnum === 'INTEGRATION')
            && ($cISOSprache === null || $returnValue->certificationLanguage === $cISOSprache)
            && $returnValue->typeEnum === $this->eType
        ) {
            return 1;
        } // Alles O.K.
        if ($returnValue->stateEnum === 'INVALID_TS_ID') {
            Shop::Container()->getLogService()->error("TrustedShops Zertifikat {$cTSID} existiert nicht!");
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 2; // Das Zertifikat existiert nicht
        }
        if ($returnValue->stateEnum === 'CANCELLED') {
            Shop::Container()->getLogService()->error("TrustedShops Zertifikat {$cTSID} ist abgelaufen!");
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 3; // Das Zertifikat ist abgelaufen
        }
        if ($returnValue->stateEnum === 'DISABLED') {
            Shop::Container()->getLogService()->error("TrustedShops Zertifikat {$cTSID} ist gesperrt!");
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 4; // Das Zertifikat ist gesperrt
        }
        if (strlen($returnValue->certificationLanguage) > 0 
            && strtolower($returnValue->certificationLanguage) !== strtolower($cISOSprache)
        ) {
            Shop::Container()->getLogService()->error(
                "TrustedShops Zertifikat {$cTSID} wurde aufgrund falscher " .
                "Sprache {$cISOSprache} deaktiviert (erwartet: {$returnValue->certificationLanguage})!"
            );
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 7; // Falsche Sprache
        }
        if ($returnValue->typeEnum !== $this->eType) {
            Shop::Container()->getLogService()->error("TrustedShops Zertifikat {$cTSID} deaktiviert. (falsche TS-Variante)!");
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 10; // Falsche Variante
        }

        return 0;
    }

    /**
     * Prüft über die API von Trusted Shops, ob die Logindaten gültig sind
     *
     * @return bool
     */
    public function pruefeLogin(): bool
    {
        $returnValue = null;
        $wsdlUrl     = TS_SERVER;
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);

        if (PHPSettingsHelper::checkSOAP($wsdlUrl)) {
            $client = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            //call WS method
            $returnValue = $client->checkLogin($this->tsId, $this->wsUser, $this->wsPassword);

            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                Shop::Container()->getLogService()->error($errorText);
            }
        } else {
            Shop::Container()->getLogService()->error('SOAP could not be loaded.');
        }

        if ($returnValue == 1) {
            return true;
        }
        Shop::Container()->getLogService()->error(
            "TrustedShops Fehler {$returnValue} bei Client Authentifizierung mit tsId={$this->tsId}, " .
            "wsUser={$this->wsUser}, wsPasswort={$this->wsPassword}"
        );

        return false;
    }

    /**
     * Schau ob ein Zertifikat in der Datenbank vorhanden ist und deaktiviert dieses dann
     *
     * @param string $cTSID
     * @param string $cISOSprache
     * @return bool
     */
    public function deaktiviereZertifikat($cTSID, $cISOSprache): bool
    {
        if (strlen($cTSID) > 0) {
            $this->nAktiv = 0;
            // Prüfe ob das Zertifikat vorhanden ist
            $oZertifikat = Shop::Container()->getDB()->select(
                'ttrustedshopszertifikat', 
                'cTSID', 
                Shop::Container()->getDB()->escape($cTSID), 
                'cISOSprache', 
                $cISOSprache
            );
            if (isset($oZertifikat->kTrustedShopsZertifikat) && $oZertifikat->kTrustedShopsZertifikat > 0) {
                $nRow = Shop::Container()->getDB()->query(
                    'UPDATE ttrustedshopszertifikat
                        SET nAktiv = 0
                        WHERE kTrustedShopsZertifikat = ' . (int)$oZertifikat->kTrustedShopsZertifikat,
                    \DB\ReturnType::AFFECTED_ROWS
                );
                if ($nRow > 0) {
                    Shop::Container()->getLogService()->error(
                        'Das TrustedShops Zertifikat mit der ID ' .
                        $cTSID . ' wurde deaktiviert!'
                    );

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Holt anhand der cISOSprache das Trusted Shops Zertifikat aus der Datenbank
     *
     * @param string $cISOSprache
     * @param string $tsId
     * @return null|stdClass
     */
    public function gibTrustedShopsZertifikatISO($cISOSprache, $tsId = '')
    {
        $oZertifikat = null;
        if (strlen($cISOSprache) > 0 && strlen($tsId) > 0) {
            $oZertifikat = Shop::Container()->getDB()->select(
                'ttrustedshopszertifikat', 
                'cISOSprache', $cISOSprache, 
                'cTSID', $tsId
            );
            $oZertifikat = $this->entschluesselTSDaten($oZertifikat);
        } elseif (strlen($cISOSprache) > 0) {
            $oZertifikat = Shop::Container()->getDB()->select('ttrustedshopszertifikat', 'cISOSprache', $cISOSprache);
            $oZertifikat = $this->entschluesselTSDaten($oZertifikat);
        }

        return $oZertifikat;
    }

    /**
     * Holt anhand der tsID das Trusted Shops Zertifikat aus der Datenbank
     *
     * @param string $cTSID
     * @return null|stdClass
     */
    public function gibTrustedShopsZertifikatTSID($cTSID)
    {
        $oZertifikat = null;
        if (strlen($cTSID) > 0) {
            $oZertifikat = Shop::Container()->getDB()->select('ttrustedshopszertifikat', 'cTSID', $cTSID);
            $oZertifikat = $this->entschluesselTSDaten($oZertifikat);
        }

        return $oZertifikat;
    }

    // Speichert ein Zertifikat in die Datenbank
    // 1 = Alles O.K.
    // 2 = Das Zertifikat existiert nicht
    // 3 = Das Zertifikat ist abgelaufen
    // 4 = Das Zertifikat ist gesperrt
    // 5 = Shop befindet sich in der Zertifizierung
    // 6 = Keine Excellence-Variante mit Käuferschutz im Checkout-Prozess
    // 7 = Falsche Sprache
    // 8 = Benutzername & Passwort ungültig
    // 9 = Zertifikat konnte nicht gespeichert werden
    // 10 = Falsche Käuferschutzvariante
    // 11 = SOAP Fehler
    /**
     * @param stdClass $oZertifikat
     * @param int      $kTrustedShopsZertifikat
     * @return int
     */
    public function speicherTrustedShopsZertifikat($oZertifikat, $kTrustedShopsZertifikat = 0)
    {
        if (strlen($oZertifikat->cISOSprache) > 0 && strlen($oZertifikat->cTSID) > 0) {
            if ($kTrustedShopsZertifikat > 0) {
                $oZertifikat->kTrustedShopsZertifikat = $kTrustedShopsZertifikat;
                $this->kTrustedShopsZertifikat        = $kTrustedShopsZertifikat;
            }
            $this->tsId       = $oZertifikat->cTSID;
            $this->wsUser     = $oZertifikat->cWSUser;
            $this->wsPassword = $oZertifikat->cWSPasswort;
            $this->nAktiv     = $oZertifikat->nAktiv;
            $this->eType      = $oZertifikat->eType;

            // 1 = Alles O.K.
            // 2 = Das Zertifikat existiert nicht
            // 3 = Das Zertifikat ist abgelaufen
            // 4 = Das Zertifikat ist gesperrt
            // 5 = Shop befindet sich in der Zertifizierung
            // 6 = Keine Excellence-Variante mit Käuferschutz im Checkout-Prozess
            // 7 = Falsche Sprache
            // 10 = Falsche Käuferschutzvariante

            $nReturnValue = $this->pruefeZertifikat($oZertifikat->cISOSprache, true);

            $this->nAktiv        = 0;
            $oZertifikat->nAktiv = 0;

            if ($nReturnValue === 1) {
                if ($this->eType === TS_BUYERPROT_CLASSIC) {
                    $this->nAktiv        = 0;
                    $oZertifikat->nAktiv = 0;
                } elseif ($this->pruefeLogin()) {
                    $this->nAktiv        = 1;
                    $oZertifikat->nAktiv = 1;
                }
            }
            $oZertifikat = $this->verschluesselTSDaten($oZertifikat);
            Shop::Container()->getDB()->queryPrepared(
                'DELETE ttrustedshopszertifikat, ttrustedeshopsprodukt 
                    FROM ttrustedshopszertifikat
                    LEFT JOIN ttrustedeshopsprodukt 
                        ON ttrustedeshopsprodukt.kTrustedShopsZertifikat = ttrustedshopszertifikat.kTrustedShopsZertifikat
                        WHERE ttrustedshopszertifikat.cISOSprache = :lng',
                ['lng' => $oZertifikat->cISOSprache],
                \DB\ReturnType::DEFAULT
            );

            $oZertifikat->dChecked = $this->dChecked;
            if ($oZertifikat->dChecked === '') {
                $oZertifikat->dChecked = 'now()';
            }
            unset($oZertifikat->kTrustedShopsZertifikat);
            $kTrustedShopsZertifikat = Shop::Container()->getDB()->insert('ttrustedshopszertifikat', $oZertifikat);

            if ($kTrustedShopsZertifikat > 0) {
                if ($this->eType === TS_BUYERPROT_EXCELLENCE) {
                    $this->holeKaeuferschutzProdukte($kTrustedShopsZertifikat);
                }

                if ($nReturnValue === 2) {
                    return 2;
                } // Das Zertifikat existiert nich
                if ($nReturnValue === 3) {
                    return 3;
                } // Das Zertifikat ist abgelaufen
                if ($nReturnValue === 4) {
                    return 4;
                } // Das Zertifikat ist gesperrt
                if ($nReturnValue === 5) {
                    return 5;
                } // Shop befindet sich in der Zertifizierung
                if ($nReturnValue === 6) {
                    return 6;
                } // Keine Excellence-Variante mit Käuferschutz im Checkout-Prozess
                if ($nReturnValue === 7) {
                    return 7;
                } // Falsche Sprache
                if ($nReturnValue === 10) {
                    return 10;
                } // Falsche Variante
                if ($nReturnValue === 11) {
                    return 11;
                } // SOAP Fehler

                return -1;
            }

            return 9; // Zertifikat konnte nicht gespeichert werden
        }

        return 1;
    }

    /**
     * Löscht ein Zertifikat aus der Datenbank
     *
     * @param int $kTrustedShopsZertifikat
     * @return bool
     */
    public function loescheTrustedShopsZertifikat($kTrustedShopsZertifikat): bool
    {
        if ((int)$kTrustedShopsZertifikat > 0) {
            $nRows = Shop::Container()->getDB()->delete(
                'ttrustedshopszertifikat', 
                'kTrustedShopsZertifikat', 
                (int)$kTrustedShopsZertifikat
            );

            return $nRows > 0;
        }

        return false;
    }

    /**
     * Gibt den aktuellen Kundenbewertungsstatus aus der DB zurück
     *
     * @param string $cISOSprache
     * @return object|bool
     */
    public function holeKundenbewertungsstatus($cISOSprache)
    {
        if (strlen($cISOSprache) > 0) {
            $rating = Shop::Container()->getDB()->select(
                'ttrustedshopskundenbewertung', 
                'cISOSprache', 
                $cISOSprache
            );
            
            return isset($rating->kTrustedshopsKundenbewertung) && $rating->kTrustedshopsKundenbewertung > 0
                ? $rating
                : false;
        }

        return false;
    }

    /**
     * Prüft ob eine TSID bereits in anderen Sprachen vorhanden ist
     *
     * @param string $cTSID
     * @param string $cISOSprache
     * @return bool
     */
    public function pruefeKundenbewertungsstatusAndereSprache($cTSID, $cISOSprache): bool
    {
        $ratings = [];
        if (strlen($cTSID) > 0 && strlen($cISOSprache) > 0) {
            $ratings = Shop::Container()->getDB()->queryPrepared(
                'SELECT *
                    FROM ttrustedshopskundenbewertung
                    WHERE cTSID = :id
                        AND cISOSprache != :iso',
                ['id' => $cTSID, 'iso' => $cISOSprache],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }

        return count($ratings) > 0;
    }

    /**
     * @param int    $nStatus
     * @param string $cISOSprache
     * @return $this
     */
    public function aenderKundenbewertungsstatusDB($nStatus = 0, $cISOSprache): self
    {
        if (strlen($cISOSprache) > 0) {
            $rating = $this->holeKundenbewertungsstatus($cISOSprache);

            if (isset($rating->kTrustedshopsKundenbewertung) && $rating->kTrustedshopsKundenbewertung > 0) {
                $_upd                = new stdClass();
                $_upd->nStatus       = (int)$nStatus;
                $_upd->cISOSprache   = $cISOSprache;
                $_upd->dAktualisiert = 'now()';
                Shop::Container()->getDB()->update(
                    'ttrustedshopskundenbewertung', 
                    'kTrustedshopsKundenbewertung', 
                    (int)$rating->kTrustedshopsKundenbewertung, 
                    $_upd
                );
            } else {
                $rating                = new stdClass();
                $rating->nStatus       = $nStatus;
                $rating->cTSID         = '';
                $rating->cISOSprache   = $cISOSprache;
                $rating->dAktualisiert = 'now()';

                Shop::Container()->getDB()->insert('ttrustedshopskundenbewertung', $rating);
            }
        }

        return $this;
    }

    /**
     * @param string $cTSID
     * @param string $cISOSprache
     * @return $this
     */
    public function aenderKundenbewertungtsIDDB($cTSID, $cISOSprache): self
    {
        if (strlen($cISOSprache) > 0 && strlen($cTSID) > 0) {
            $rating = $this->holeKundenbewertungsstatus($cISOSprache);

            if (isset($rating->kTrustedshopsKundenbewertung) && $rating->kTrustedshopsKundenbewertung > 0) {
                // Updaten
                $_upd        = new stdClass();
                $_upd->cTSID = $cTSID;
                Shop::Container()->getDB()->update(
                    'ttrustedshopskundenbewertung',
                    'kTrustedshopsKundenbewertung',
                    (int)$rating->kTrustedshopsKundenbewertung,
                    $_upd
                );
            } else {
                $rating                = new stdClass();
                $rating->nStatus       = 0;
                $rating->cTSID         = $cTSID;
                $rating->cISOSprache   = $cISOSprache;
                $rating->dAktualisiert = 'now()';

                Shop::Container()->getDB()->insert('ttrustedshopskundenbewertung', $rating);
            }
        }

        return $this;
    }

    /**
     * @param string $cTSID
     * @param int    $nStatus
     * @param string $cISOSprache
     * @return int
     */
    public function aenderKundenbewertungsstatus($cTSID, $nStatus = 0, $cISOSprache = 'de'): int
    {
        $returnValue = null;
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);
        $wsdlUrl = TS_RATING_SERVER;

        if (PHPSettingsHelper::checkSOAP($wsdlUrl)) {
            $client = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            //set return value for the case if a SOAP exception occurs
            $returnValue = SOAP_ERROR;
            //call WS method
            $returnValue = $client->updateRatingWidgetState($cTSID, $nStatus, 'jtl-software', 'eKgxL2vm', 'JTL');
            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                Shop::Container()->getLogService()->error($errorText);
            } else {
                Shop::Container()->getLogService()->error(
                    "Der Kundenbewertungsstatus ('TSID: " . $cTSID .
                    "') wurde versucht zu ändern, ReturnCode: " . $returnValue
                );
            }
        } else {
            Shop::Container()->getLogService()->error('SOAP could not be loaded.');
        }
        if ($returnValue === 'OK') {
            $this->aenderKundenbewertungsstatusDB($nStatus, $cISOSprache);

            return 1;
        }
        if ($returnValue === SOAP_ERROR) {
            return 2;
        }
        if ($returnValue === 'INVALID_TSID') {
            return 3;
        }
        if ($returnValue === 'NOT_REGISTERED_FOR_TRUSTEDRATING') {
            return 4;
        }
        if ($returnValue === 'WRONG_WSUSERNAME_WSPASSWORD') {
            return 5;
        }

        return 0;
    }

    /**
     * @param stdClass $oZertifikat
     * @return stdClass
     */
    public function verschluesselTSDaten($oZertifikat)
    {
        if (!is_object($oZertifikat)) {
            $oZertifikat = new stdClass();
        }
        $oZertifikat->cWSUser     = trim(Shop::Container()->getCryptoService()->encryptXTEA($oZertifikat->cWSUser));
        $oZertifikat->cWSPasswort = trim(Shop::Container()->getCryptoService()->encryptXTEA($oZertifikat->cWSPasswort));

        return $oZertifikat;
    }

    /**
     * @param stdClass $oZertifikat
     * @return stdClass
     */
    public function entschluesselTSDaten($oZertifikat)
    {
        if ($oZertifikat === false || $oZertifikat === null) {
            $oZertifikat              = new stdClass();
            $oZertifikat->cWSUser     = null;
            $oZertifikat->cWSPasswort = null;

            return $oZertifikat;
        }
        $oZertifikat->cWSUser     = trim(Shop::Container()->getCryptoService()->decryptXTEA($oZertifikat->cWSUser));
        $oZertifikat->cWSPasswort = trim(Shop::Container()->getCryptoService()->decryptXTEA($oZertifikat->cWSPasswort));

        return $oZertifikat;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public static function ladeKundenbewertungsWidgetNeu($filename)
    {
        // Load fresh widget from trustedshops Website
        // and write in local file
        // Open the file to get existing content
        $bMoeglich = false;
        $current   = null;
        if (TS_MODUS > 0) {
            $cURL = 'https://www.trustedshops.com/bewertung/widget/widgets/' . $filename; // Produktiv

            if (PHPSettingsHelper::checkAllowFopen()) {
                $current = @file_get_contents($cURL); // Produktiv
                if ($current) {
                    $bMoeglich = true;
                }
            } elseif (PHPSettingsHelper::checkCURL()) {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $cURL);
                curl_setopt($curl, CURLOPT_TIMEOUT, 15);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

                $current = curl_exec($curl);
                curl_close($curl);

                if ($current) {
                    $bMoeglich = true;
                }
            }
        } else {
            $cURL = 'https://qa.trustedshops.com/bewertung/widget/widgets/' . $filename; // Test

            if (PHPSettingsHelper::checkAllowFopen()) {
                $current = @file_get_contents($cURL); // Test
                if ($current) {
                    $bMoeglich = true;
                }
            } elseif (PHPSettingsHelper::checkCURL()) {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $cURL);
                curl_setopt($curl, CURLOPT_TIMEOUT, 15);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

                $current = curl_exec($curl);
                curl_close($curl);

                if ($current) {
                    $bMoeglich = true;
                }
            }
        }

        // Write the contents back to the file
        if ($bMoeglich) {
            @file_put_contents(PFAD_ROOT . PFAD_GFX_TRUSTEDSHOPS . $filename, $current);
        }

        return $bMoeglich;
    }

    /**
     * @return stdClass
     */
    private function holeKundenbewertungsStatistik()
    {
        $content = null;

        $url = sprintf(
            'http://www.trustedshops.com/api/ratings/v1/%s.xml',
            trim($this->tsId)
        );

        if (PHPSettingsHelper::checkAllowFopen()) {
            $content = @file_get_contents($url); // Test
        } elseif (PHPSettingsHelper::checkCURL()) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

            $content = curl_exec($curl);
            curl_close($curl);
        }

        $xml             = simplexml_load_string($content);
        $rating          = new stdClass();
        $rating->nAnzahl = (int)$xml->ratings['amount'];

        $dDurchschnitt = null;
        foreach ($xml->ratings->result as $result) {
            if ($result['name'] === 'average') {
                $dDurchschnitt = (double)$result;
                break;
            }
        }

        if ($dDurchschnitt === null) {
            $dDurchschnitt = (double)$xml->ratings->result[0];
        }
        $rating->dDurchschnitt = $dDurchschnitt;

        return $rating;
    }

    /**
     * @return stdClass
     */
    public function gibKundenbewertungsStatistik(): stdClass
    {
        $arrStatistik = Shop::Container()->getDB()->selectAll('ttrustedshopsstatistik', 'cTSID', trim($this->tsId));

        if (count($arrStatistik) === 0) {
            // Erstimport
            $oData = $this->holeKundenbewertungsStatistik();

            $oData->cTSID    = trim($this->tsId);
            $oData->dUpdated = 'now()';
            Shop::Container()->getDB()->insert('ttrustedshopsstatistik', $oData);

            $oStatistik                = new stdClass();
            $oStatistik->nAnzahl       = $oData->nAnzahl;
            $oStatistik->dMaximum      = 5.0;
            $oStatistik->dDurchschnitt = $oData->dDurchschnitt;
        } else {
            $oStatistikDB = $arrStatistik[0];
            $updateTime   = new DateTime($oStatistikDB->dUpdated);
            $updateTime->modify('+1 day');
            if ($updateTime < new DateTime()) {
                // Update
                $oData = $this->holeKundenbewertungsStatistik();

                $oStatistikDB->nAnzahl       = $oData->nAnzahl;
                $oStatistikDB->dDurchschnitt = $oData->dDurchschnitt;
                $oStatistikDB->dUpdated      = 'now()';
                Shop::Container()->getDB()->update(
                    'ttrustedshopsstatistik',
                    'kStatistik',
                    $oStatistikDB->kStatistik,
                    $oStatistikDB
                );
            }

            $oStatistik                = new stdClass();
            $oStatistik->nAnzahl       = $oStatistikDB->nAnzahl;
            $oStatistik->dMaximum      = 5.0;
            $oStatistik->dDurchschnitt = $oStatistikDB->dDurchschnitt;
        }

        return $oStatistik;
    }

    /**
     * @param string $cMail
     * @param string $cBestellNr
     * @return null|stdClass
     * @former gibTrustedShopsBewertenButton()
     * @since 5.0.0
     */
    public static function getRatingButton(string $cMail, string $cBestellNr)
    {
        $button = null;
        if (strlen($cMail) > 0 && strlen($cBestellNr) > 0) {
            $languageCode = StringHandler::convertISO2ISO639(Shop::getLanguageCode());
            $langCodes    = ['de', 'en', 'fr', 'pl', 'es'];
            if (in_array($languageCode, $langCodes, true)) {
                $ts       = new TrustedShops(-1, $languageCode);
                $tsRating = $ts->holeKundenbewertungsstatus($languageCode);

                if (!empty($tsRating->cTSID)
                    && $tsRating->kTrustedshopsKundenbewertung > 0
                    && (int)$tsRating->nStatus === 1
                ) {
                    $button       = new stdClass();
                    $imageBaseURL = Shop::getImageBaseURL() .
                        PFAD_TEMPLATES .
                        Template::getInstance()->getDir() .
                        '/themes/base/images/trustedshops/rate_now_';
                    $images       = [
                        'de' => $imageBaseURL . 'de.png',
                        'en' => $imageBaseURL . 'en.png',
                        'fr' => $imageBaseURL . 'fr.png',
                        'es' => $imageBaseURL . 'es.png',
                        'nl' => $imageBaseURL . 'nl.png',
                        'pl' => $imageBaseURL . 'pl.png'
                    ];

                    $button->cURL    = 'https://www.trustedshops.com/buyerrating/rate_' .
                        $tsRating->cTSID .
                        'html&buyerEmail=' . urlencode(base64_encode($cMail)) .
                        '&shopOrderID=' . urlencode(base64_encode($cBestellNr));
                    $button->cPicURL = $images[$languageCode];
                }
            }
        }

        return $button;
    }

    /**
     * @return stdClass
     * @since 5.0.0
     */
    public static function getTrustedShops(): stdClass
    {
        unset($_SESSION['TrustedShops'], $oTrustedShops);
        $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639(Shop::getLanguageCode()));
        $oTrustedShops->holeKaeuferschutzProdukteDB(StringHandler::convertISO2ISO639(Shop::getLanguageCode()), true);
        // Hole alle Käuferschutzprodukte, die in der DB hinterlegt sind
        $ts       = new stdClass();
        $cLandISO = $_SESSION['Lieferadresse']->cLand;
        if (!$cLandISO) {
            $cLandISO = $_SESSION['Kunde']->cLand;
        }
        // Prüfe, ob TS ID noch gültig ist
        if ($oTrustedShops->pruefeZertifikat(StringHandler::convertISO2ISO639(Shop::getLanguageCode())) === 1) {
            // Gib nur die Informationen weiter, die das Template auch braucht
            $ts->nAktiv                   = $oTrustedShops->nAktiv;
            $ts->eType                    = $oTrustedShops->eType;
            $ts->cId                      = $oTrustedShops->tsId;
            $ts->cISOSprache              = $oTrustedShops->oZertifikat->cISOSprache;
            $ts->oKaeuferschutzProdukteDB = $oTrustedShops->oKaeuferschutzProdukteDB;
            $ts->oKaeuferschutzProdukte   = $oTrustedShops->oKaeuferschutzProdukte;
            if (isset($ts->oKaeuferschutzProdukte->item)) {
                $ts->oKaeuferschutzProdukte->item = self::filterNichtGebrauchteKaeuferschutzProdukte(
                    $oTrustedShops->oKaeuferschutzProdukte->item,
                    Session::Cart()->gibGesamtsummeWaren(false) *
                    ((100 + (float)$_SESSION['Steuersatz'][Session::Cart()->gibVersandkostenSteuerklasse($cLandISO)]) / 100)
                );
            }
            $ts->cLogoURL                 = $oTrustedShops->cLogoURL;
            $ts->cSpeicherungURL          = $oTrustedShops->cSpeicherungURL;
            $ts->cBedingungURL            = $oTrustedShops->cBedingungURL;
            $ts->cBoxText                 = $oTrustedShops->cBoxText;
            $ts->cVorausgewaehltesProdukt = isset($oTrustedShops->oKaeuferschutzProdukte->item)
                ? self::getPreSelectedProduct(
                    $oTrustedShops->oKaeuferschutzProdukte->item,
                    Session::Cart()->gibGesamtsummeWaren(false) *
                    ((100 + (float)$_SESSION['Steuersatz'][Session::Cart()->gibVersandkostenSteuerklasse($cLandISO)]) / 100)
                )
                : '';
        }
        $logger = Shop::Container()->getLogService();
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->debug(
                'Der TrustedShops Käuferschutz im Bestellvorgang wurde mit folgendem Ergebnis geladen: ' .
                print_r($ts, true)
            );
        }

        return $ts;
    }

    /**
     * Filter alle Käuferschutzprodukte aus den Produkten in der DB, die für die Warensumme keinen Sinn machen
     *
     * @param array $products
     * @param float $fGesamtSumme
     * @return array
     * @since 5.0.0
     */
    public static function filterNichtGebrauchteKaeuferschutzProdukte($products, $fGesamtSumme): array
    {
        $filtered = [];
        if (is_array($products) && count($products) > 0) {
            foreach ($products as $oKaeuferschutzProdukte) {
                $filtered[] = $oKaeuferschutzProdukte;
                if ((float)$fGesamtSumme < (float)$oKaeuferschutzProdukte->protectedAmountDecimal) {
                    break;
                }
            }
        }

        return $filtered;
    }

    /**
     * Liefer ein Assoc Array mit tsProductID als Keys + Preisen als Werte
     *
     * @param array $products
     * @return array
     * @since 5.0.0
     */
    public static function gibKaeuferschutzProdukteAssocID($products): array
    {
        $assoc = [];
        if (is_array($products) && count($products) > 0) {
            foreach ($products as $oKaeuferschutzProdukte) {
                $assoc[$oKaeuferschutzProdukte->tsProductID] = $oKaeuferschutzProdukte->netFee;
            }
        }

        return $assoc;
    }

    /**
     * Liefer das Käuferschutzprodukt (tsProductID), welches vorausgewählt werden soll anhand der Warenkorb Summe
     *
     * @param array $products
     * @param float $totalSum
     * @return string
     * @former gibVorausgewaehltesProdukt()
     * @since 5.0.0
     */
    public static function getPreSelectedProduct($products, $totalSum): string
    {
        $tsProductID = '';
        $lastValue   = 0.0;
        if (is_array($products) && count($products) > 0) {
            foreach ($products as $product) {
                if ((float)$totalSum <= (float)$product->protectedAmountDecimal
                    && ((float)$product->protectedAmountDecimal < $lastValue || $lastValue === 0.0)
                ) {
                    $tsProductID = $product->tsProductID;
                    $lastValue   = (float)$product->protectedAmountDecimal;
                }
            }
        }

        return $tsProductID;
    }
}
