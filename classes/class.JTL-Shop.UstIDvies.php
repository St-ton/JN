<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * ToDos:
 *
 * - prüfen auf
 *     - Anzahl Zeichen
 *     - korrekte Länge
 *     - korrektes Länderkürzel
 * - Verfügbarkeitszeiten der Steuerverwaltungen in den Ländern berücksichtigen
 *   (ggf. entsprechende Hinweise ausgeben)
 * - ggf. hilfe anbieten, falls eine ID nicht geprüft werden konnte,
 *   durch angabe des betreffenden steueramtes (siehe http://ec.europa.eu/taxation_customs/vies/faq.html#item_7)
 *
 */

/**
 * Class UstIDvies
 *
 * External documentation
 *
 * @link http://ec.europa.eu/taxation_customs/vies/faq.html
 *
 * European Commission
 * VIES (VAT Information Exchange System)
 * @link https://ec.europa.eu/taxation_customs/business/vat/eu-vat-rules-topic/vies-vat-information-exchange-system-enquiries_en
 */
class UstIDvies
{
    /**
     * string zero-terminated
     * URL of the MIAS
     */
    private $szViesWSDL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * array
     * answers of the MIAS-system  --TO-CHECK-- may it's not needed here this way
     */
    private $vMiasAnswerStrings = [
           0 => 'MwSt-Nummer gültig.'
        , 10 => 'MwSt-Nummer ungültig.' // (D.h. die eingegebene Nummer ist zumindest an dem angegebenen Tag ungültig)
        , 20 => 'Bearbeitung derzeit nicht möglich. Bitte wiederholen Sie Ihre Anfrage später.' // (D.h. es gibt ein Problem mit dem Netz oder mit der Web-Anwendung)
        , 30 => 'Bearbeitung im Mitgliedstaat derzeit nicht möglich. Bitte wiederholen Sie Ihre Anfrage später.' // (D.h. die Anwendung ist in dem Mitgliedstaat, der die von Ihnen eingegebene MwSt-Nummer erteilt hat, derzeit nicht möglich)
        , 40 => 'Unvollständige oder fehlerhafte Dateneingabe' // (MwSt-Nummer + Mitgliedstaat)
        , 50 => 'Zeitüberschreitung. Bitte wiederholen Sie Ihre Anfrage später.'
    ];

    /**
     * array
     * errors of the internal pre-check (length, starting chars, aso.)
     */
    private $vPreCheckErrors = [
          100 => 'Es wurde keine UstID übergeben!'
        , 110 => 'Die UstID beginnt nicht mit zwei Großbuchstaben als Länderkennung!'
        , 120 => 'Die UstID hat eine ungültige Länge!'
        , 130 => 'Die UstID entspricht nicht den Vorschriften des betreffenden Landes!'
    ];

    /**
     * string zero-terminated
     */
    private $szErrorStr = '';

    /**
     * object
     * UstIDviesDownSlots
     */
    private $oDownTimes = null;

    /**
     * string zero-terminated
     */
    private $szVATid;


    /* --DEBUG-- */
    private $oLogger = null; // --DEBUG--


    public function __construct($szUstID = '')
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--

        $this->szVATid = $szUstID;
        $this->oDownTimes = new UstIDviesDownSlots();
    }


    public function getErrorStr()
    {
        return $this->szErrorStr;
    }

    /**
     * ask the remote APIs of the VIES-online-system
     *
     * @param $szUstID
     * @return bool
     */
    public function doCheckID($szUstID = '')
    {
        $szMWstID = '';
        if ('' === $szUstID && '' === $this->szVATid) {
            // error: no szUstID was given
            $this->szErrorStr = $this->vPreCheckErrors[100];
            return false;
        } else {
            $szMWstID = ('' === $szUstID)
                ? $this->szVATid
                : $szUstID
            ;
        }
        $this->oLogger->debug('internal MwStID: '.$szMWstID); // --DEBUG--


        // --TO-CHECK--
        // 2 character, 9 digits is the specification,
        // but if we scan it hard this way (and cut out overhangs), we can not inform the user to correct his input ...
        $oVatParser = new UstIDviesVatParser();
        $vParams    = $oVatParser->getIdAsParams($szMWstID); // --TODO-- build it multiple-call-resistent
        if (!is_array($vParams)) {
            // error-handling (in the case, we got a number, it is a error-code)
            if (is_int($vParams)) {
                $this->szErrorStr = (isset($this->vPreCheckErrors[$vParams])) ? ($this->vPreCheckErrors[$vParams]) : '';
                return false;
            }
        }
        $this->oLogger->debug('VAT as PARAMS: '.print_r($vParams ,true )); // --DEBUG--

        list($szCountryCode, $szVatNumber) = $vParams;
        if (! $this->oDownTimes->isDown($szCountryCode)) {

            // ask the remote service
            $this->oLogger->debug('asking the remote service..'); // --DEBUG--
            /*
             *$oSoapClient = new SoapClient($this->szViesWSDL);
             *$result = $oSoapClient->checkVat(['countryCode' => $szCountryCode, 'vatNumber' => $szVatNumber]); // --TODO--
             */

        } // else ...
        // --TODO-- : inform the user, the VAT-office in this country has closed this time
        // log that event, and offer a methode to fetch it elsewhere
        // (maybe write a specified Exception ...)

        // return
    }

}
