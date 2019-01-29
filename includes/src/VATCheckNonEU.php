<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class VATCheckNonEU
 */
class VATCheckNonEU implements VATCheckInterface
{
    /**
     *
     */
    public function __construct()
    {
    }

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
     * @param string $szUstID
     */
    public function doCheckID($szUstID)
    {
        $oVatParser = new VATCheckVatParserNonEU($szUstID);
        if (true === $oVatParser->parseVatId()) {
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
            'errorinfo' => '' !== ($szErrorInfo = $oVatParser->getErrorInfo()) ? $szErrorInfo : ''
        ];
    }
}
