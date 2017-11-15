<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class UstIDvies
 *
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
     * object
     * UstIDviesDownSlots-object
     */
    private $oDownTimes = null;

    /**
     * string zero-terminated
     * given VAT-number to check
     */
    private $szVATid;


    /**
     * At this moment, the VIES-syste, did not return any information other than "valid" or "invalid"
     * by giving a boolean value back via SOAP.
     * So we keep this error-string only for a possible future usage - currently they are not used.
     *
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

    /* --DEBUG-- */
    private $oLogger = null; // --DEBUG--



    /**
     * __construct an instance of this object
     */
    public function __construct()
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--

        $this->oDownTimes = new UstIDviesDownSlots();
    }


    /**
     * ask the remote APIs of the VIES-online-system
     *
     * return a array of check-results
     * [
     *        success   : boolean, "true" = all checks were fine, "false" somthing went wrong
     *      , errortype : string, which type of error was occure, time- or parse-error
     *      , errorcode : string, numerical code to identify the error
     *      , errorinfo : addition information to show it the user in the frontend
     * ]
     *
     * @param string  the VAT-ID
     * @return array  array containing information about the check-results
     */
    public function doCheckID($szUstID = '')
    {
        if ('' === $szUstID) {
            return [
                  'success'   => false
                , 'errortype' => 'parse'
                , 'errorcode' => 1          // error: no szUstID was given
            ];
        }

        // 2 character, 9 digits is the specification,
        // but if we scan it hard this way (and cut out overhangs), we can not inform the user to correct his input ...
        $oVatParser = new UstIDviesVatParser($szUstID);
        if (true === $oVatParser->parseVatId()) {
            list($szCountryCode, $szVatNumber) = $oVatParser->getIdAsParams();
            $this->oLogger->debug('VAT as PARAMS: '.print_r($oVatParser->getIdAsParams(),true )); // --DEBUG--
        } else {
            return [
                  'success'   => false
                , 'errortype' => 'parse'
                , 'errorcode' => $oVatParser->getErrorCode() // --TODO-- return the error-position....
                , 'errorinfo' => ('' !== ($szErrorInfo = $oVatParser->getErrorInfo()) ? $szErrorInfo : '')
            ];
        }

        if (false === $this->oDownTimes->isDown($szCountryCode)) {

            $this->oLogger->debug('asking the remote service..'); // --DEBUG--

            // asking the remote service
            $oSoapClient = new SoapClient($this->szViesWSDL);
            $oViesResult = null;
            try {
                $oViesResult = $oSoapClient->checkVat(['countryCode' => $szCountryCode, 'vatNumber' => $szVatNumber]); // --TODO--
            } catch (Exception $e) {
                $this->oLogger->debug('MwStID Problem: '. $e->getMessage()); // --DEBUG--
                Jtllog::writeLog('MwStID Problem: '.$e->getMessage(), JTLLOG_LEVEL_ERROR);
            }
            $this->oLogger->debug('VIES-RESULT (SOAP) : '.print_r( $oViesResult ,true )); // --DEBUG--

            if (null !== $oViesResult && true === $oViesResult->valid) {
                //Jtllog::writeLog('MwStID valid. ('.print_r($oViesResult, true).')', JTLLOG_LEVEL_NOTICE);  // success, logging optional
                return [
                      'success'   => true
                    , 'errortype' => 'vies'
                    , 'errorcode' => ''
                ];
            } else {
                Jtllog::writeLog('MwStID invalid! ('.print_r($oViesResult, true).')', JTLLOG_LEVEL_NOTICE);
                return [
                      'success'   => false
                    , 'errortype' => 'vies'
                    , 'errorcode' => 5 // error: ID is invalid according to the VIES-system
                ];
            }

        } else {
            // inform the user, the VAT-office in this country has closed this time.
            Jtllog::writeLog('MIAS-Amt aktuell nicht erreichbar. (ID: '.$szUstID.')', JTLLOG_LEVEL_NOTICE);
            return [
                  'success'   => false
                , 'errortype' => 'time'
                , 'errorcode' => 200
                , 'errorinfo' => $this->oDownTimes->getDownInfo() // the time, till which the office has closed
            ];
        }
    }

}
