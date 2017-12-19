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
     * @var string
     */
    private $szViesWSDL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * @var UstIDviesDownSlots
     */
    private $oDownTimes;

    /**
     * At this moment, the VIES-system, does not return any information other than "valid" or "invalid"
     * by giving a boolean value back via SOAP.
     * So we keep this error-string only for a possible future usage - currently they are not used.
     *
     * @var array
     */
    private $vMiasAnswerStrings = [
        0  => 'MwSt-Nummer gültig.',
        10 => 'MwSt-Nummer ungültig.', // (D.h. die eingegebene Nummer ist zumindest an dem angegebenen Tag ungültig)
        20 => 'Bearbeitung derzeit nicht möglich. Bitte wiederholen Sie Ihre Anfrage später.', // (D.h. es gibt ein Problem mit dem Netz oder mit der Web-Anwendung)
        30 => 'Bearbeitung im Mitgliedstaat derzeit nicht möglich. Bitte wiederholen Sie Ihre Anfrage später.', // (D.h. die Anwendung ist in dem Mitgliedstaat, der die von Ihnen eingegebene MwSt-Nummer erteilt hat, derzeit nicht möglich)
        40 => 'Unvollständige oder fehlerhafte Dateneingabe', // (MwSt-Nummer + Mitgliedstaat)
        50 => 'Zeitüberschreitung. Bitte wiederholen Sie Ihre Anfrage später.'
    ];

    /**
     *
     */
    public function __construct()
    {
        $this->oDownTimes = new UstIDviesDownSlots();
    }

    /**
     * spaces can't handled by the VIES-system,
     * so we condense the ID-string here and let them out
     *
     * @param string $szString
     * @return string
     */
    public function condenseSpaces($szString) {
        return str_replace(' ', '', $szString);
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
     * @param string $szUstID
     * @return array
     */
    public function doCheckID($szUstID = '')
    {
        if ('' === $szUstID) {
            return [
                'success'   => false,
                'errortype' => 'parse',
                'errorcode' => 1 // error: no $szUstID was given
            ];
        }

        // parse the ID-string
        $oVatParser = new UstIDviesVatParser($this->condenseSpaces($szUstID));
        if (true === $oVatParser->parseVatId()) {
            list($szCountryCode, $szVatNumber) = $oVatParser->getIdAsParams();
        } else {
            return [
                'success'   => false,
                'errortype' => 'parse',
                'errorcode' => $oVatParser->getErrorCode(),
                'errorinfo' => '' !== ($szErrorInfo = $oVatParser->getErrorInfo()) ? $szErrorInfo : ''
            ];
        }

        // asking the remote service, if the VAT-office is reachable
        if (false === $this->oDownTimes->isDown($szCountryCode)) {
            $oSoapClient = new SoapClient($this->szViesWSDL);
            $oViesResult = null;
            try {
                $oViesResult = $oSoapClient->checkVat(['countryCode' => $szCountryCode, 'vatNumber' => $szVatNumber]);
            } catch (Exception $e) {
                Jtllog::writeLog('MwStID Problem: '.$e->getMessage());
            }

            if (null !== $oViesResult && true === $oViesResult->valid) {
                Jtllog::writeLog('MwStID valid. (' . print_r($oViesResult, true) . ')',
                    JTLLOG_LEVEL_NOTICE); // sometimes we get the address too

                return [
                    'success'   => true,
                    'errortype' => 'vies',
                    'errorcode' => ''
                ];
            }
            Jtllog::writeLog('MwStID invalid! (' . print_r($oViesResult, true) . ')', JTLLOG_LEVEL_NOTICE);

            return [
                'success'   => false,
                'errortype' => 'vies',
                'errorcode' => 5 // error: ID is invalid according to the VIES-system
            ];

        }
        // inform the user:"The VAT-office in this country has closed this time."
        Jtllog::writeLog('MIAS-Amt aktuell nicht erreichbar. (ID: '.$szUstID.')', JTLLOG_LEVEL_NOTICE);

        return [
            'success'   => false,
            'errortype' => 'time',
            'errorcode' => 200,
            'errorinfo' => $this->oDownTimes->getDownInfo() // the time, till which the office has closed
        ];
    }

}
