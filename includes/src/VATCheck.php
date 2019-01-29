<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class VATCheck
 */
class VATCheck
{
    /**
     * @var object
     */
    private $oLocation;

    /**
     * @var string
     */
    private $szUstID;

    /**
     * @param string $szUstID
     */
    public function __construct(string $szUstID = '')
    {
        $this->szUstID = $szUstID;

        switch (true) {
            case $this->startsWith($this->szUstID, 'CHE'):
                $this->oLocation = new VATCheckNonEU();
                break;
            default:
                $this->oLocation = new VATCheckEU();
        }
    }

    /**
     * check the UstID
     *
     * return a array of check-results
     * [
     *        success   : boolean, "true" = all checks were fine, "false" somthing went wrong
     *      , errortype : string, which type of error was occure, time- or parse-error
     *      , errorcode : string, numerical code to identify the error
     *      , errorinfo : additional information to show it the user in the frontend
     * ]
     *
     * @return array
     */
    public function doCheckID()
    {
        // if there was nothing given, we tell the caller this
        if ('' === $this->szUstID) {
            return [
                'success'   => false,
                'errortype' => 'parse',
                'errorcode' => VATCheckInterface::ERR_NO_ID_GIVEN,  // error: no $szUstID was given
                'errorinfo' => ''
            ];
        }

        return $this->oLocation->doCheckID($this->szUstID);
    }

    /**
     * @param string $szString
     * @param string $szPattern
     * @return bool
     */
    public function startsWith(string $szString = '', string $szPattern = '') : bool
    {
        if ('' === $szString) {
            return false
        };
        if ('' === $szPattern) {
            return true
        };

        return ($szPattern === substr($szString, 0, strlen($szPattern))) ?: false;
    }
}
