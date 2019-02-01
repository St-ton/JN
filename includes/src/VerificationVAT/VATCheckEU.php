<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace VerificationVAT;

/**
 * Class VATCheckEU
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
class VATCheckEU implements VATCheckInterface
{
    /**
     * @var string
     */
    private $viesWSDL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * @var VATCheckDownSlots
     */
    private $downTimes;

    /**
     * At this moment, the VIES-system, does not return any information other than "valid" or "invalid"
     * by giving a boolean value back via SOAP.
     * So we keep this error-string only for a possible future usage - currently they are not used.
     *
     * @var array
     */
    private $miasAnswerStrings = [
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
        $this->downTimes = new VATCheckDownSlots();
    }

    /**
     * spaces can't handled by the VIES-system,
     * so we condense the ID-string here and let them out
     *
     * @param string $sourceString
     * @return string
     */
    public function condenseSpaces($sourceString): string
    {
        return str_replace(' ', '', $sourceString);
    }

    /**
     * ask the remote APIs of the VIES-online-system
     *
     * return a array of check-results
     * [
     *        success   : boolean, "true" = all checks were fine, "false" somthing went wrong
     *      , errortype : string, which type of error was occure, time- or parse-error
     *      , errorcode : string, numerical code to identify the error
     *      , errorinfo : additional information to show it the user in the frontend
     * ]
     *
     * @param string $ustID
     * @return array
     */
    public function doCheckID($ustID): array
    {
        // parse the ID-string
        $VatParser = new VATCheckVatParser($this->condenseSpaces($ustID));
        if ($VatParser->parseVatId() === true) {
            list($countryCode, $vatNumber) = $VatParser->getIdAsParams();
        } else {
            return [
                'success'   => false,
                'errortype' => 'parse',
                'errorcode' => $VatParser->getErrorCode(),
                'errorinfo' => '' !== ($errorInfo = $VatParser->getErrorInfo()) ? $errorInfo : ''
            ];
        }

        // asking the remote service, if the VAT-office is reachable
        if ($this->downTimes->isDown($countryCode) === false) {
            $SoapClient = new SoapClient($this->viesWSDL);
            $ViesResult = null;

            try {
                $ViesResult = $SoapClient->checkVat(['countryCode' => $countryCode, 'vatNumber' => $vatNumber]);
            } catch (Exception $e) {
                Shop::Container()->getLogService()->warn('MwStID Problem: ' . $e->getMessage());
            }

            if ($ViesResult !== null && $ViesResult->valid === true) {
                Shop::Container()->getLogService()->notice('MwStID valid. (' . print_r($ViesResult, true) . ')');

                return [
                    'success'   => true,
                    'errortype' => 'vies',
                    'errorcode' => ''
                ];
            }
            Shop::Container()->getLogService()->notice('MwStID invalid! (' . print_r($ViesResult, true) . ')');

            return [
                'success'   => false,
                'errortype' => 'vies',
                'errorcode' => 5 // error: ID is invalid according to the VIES-system
            ];
        }
        // inform the user:"The VAT-office in this country has closed this time."
        Shop::Container()->getLogService()->notice('MIAS-Amt aktuell nicht erreichbar. (ID: ' . $ustID . ')');

        return [
            'success'   => false,
            'errortype' => 'time',
            'errorcode' => 200,
            'errorinfo' => $this->downTimes->getDownInfo() // the time, till which the office has closed
        ];
    }
}
