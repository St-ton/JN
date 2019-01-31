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
     * @var VATCheckInterface
     */
    private $Location;

    /**
     * @var string
     */
    private $ustID;

    /**
     * @param string $ustID
     */
    public function __construct(string $ustID = '')
    {
        $this->ustID = $ustID;

        switch (true) {
            case $this->startsWith($this->ustID, 'CHE'):
                $this->Location = new VATCheckNonEU();
                break;
            default:
                $this->Location = new VATCheckEU();
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
        if ($this->ustID === '') {
            return [
                'success'   => false,
                'errortype' => 'parse',
                'errorcode' => VATCheckInterface::ERR_NO_ID_GIVEN,  // error: no $ustID was given
                'errorinfo' => ''
            ];
        }

        return $this->Location->doCheckID($this->ustID);
    }

    /**
     * @param string $sourceString
     * @param string $pattern
     * @return bool
     */
    public function startsWith(string $sourceString = '', string $pattern = '') : bool
    {
        if ($sourceString === '') {
            return false
        };
        if ($pattern === '') {
            return true
        };

        return ($pattern === substr($sourceString, 0, strlen($pattern))) ?: false;
    }
}
