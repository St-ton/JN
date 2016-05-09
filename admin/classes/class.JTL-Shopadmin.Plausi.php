<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Plausi
 */
class Plausi
{
    protected $xPostVar_arr;
    protected $xPlausiVar_arr;

    /**
     *
     */
    public function __construct()
    {
        $this->xPostVar_arr   = array();
        $this->xPlausiVar_arr = array();
    }

    /**
     * @return array
     */
    public function getPostVar()
    {
        return $this->xPostVar_arr;
    }

    /**
     * @return array
     */
    public function getPlausiVar()
    {
        return $this->xPlausiVar_arr;
    }

    /**
     * @param array $xVar_arr
     * @return bool
     */
    public function setPostVar($xVar_arr)
    {
        if (is_array($xVar_arr) && count($xVar_arr) > 0) {
            $this->xPostVar_arr = StringHandler::filterXSS($xVar_arr);

            return true;
        }

        return false;
    }

    /**
     * @param array $xVar_arr
     * @return bool
     */
    public function setPlausiVar($xVar_arr)
    {
        if (is_array($xVar_arr) && count($xVar_arr) > 0) {
            $this->xPlausiVar_arr = $xVar_arr;

            return true;
        }

        return false;
    }

    /**
     * @param null $cTyp
     * @param bool $bUpdate
     */
    public function doPlausi($cTyp = null, $bUpdate = false)
    {
    }
}
