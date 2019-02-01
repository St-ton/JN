<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace VerificationVAT;

/**
 * Class VATCheckNonEU
 * @package VerificationVAT
 */
class VATCheckNonEU implements VATCheckInterface
{
    /**
     * parse the non-EU string by convention
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
    public function doCheckID(string $ustID): array
    {
        $VatParser = new VATCheckVatParserNonEU($ustID);
        if ($VatParser->parseVatId() === true) {
            return [
                'success'   => true,
                'errortype' => 'parse',
                'errorcode' => '',
            ];
        }

        return [
            'success'   => false,
            'errortype' => 'parse',
            'errorcode' => VATCheckInterface::ERR_PATTERN_MISMATCH,
            'errorinfo' => '' !== ($szErrorInfo = $VatParser->getErrorInfo()) ? $szErrorInfo : ''
        ];
    }
}
