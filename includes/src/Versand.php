<?php

/**
 * Class Versand
 */
class Versand
{
    /**
     * @var int
     */
    protected $kVersand;

    /**
     * @var int
     */
    protected $kLieferschein;

    /**
     * @var string
     */
    protected $cLogistik;

    /**
     * @var string
     */
    protected $cLogistikURL;

    /**
     * @var string
     */
    protected $cIdentCode;

    /**
     * @var string
     */
    protected $cHinweis;

    /**
     * @var string
     */
    protected $dErstellt;

    /**
     * @var object
     */
    protected $oData;

    /**
     * Constructor
     *
     * @param int         $kVersand
     * @param null|object $oData
     */
    public function __construct($kVersand = 0, $oData = null)
    {
        if ((int)$kVersand > 0) {
            $this->loadFromDB($kVersand, $oData);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int         $kVersand
     * @param null|object $oData
     */
    private function loadFromDB($kVersand = 0, $oData = null)
    {
        $oObj = Shop::DB()->select('tversand', 'kVersand', (int)$kVersand);

        $this->oData = $oData;

        if (!empty($oObj->kVersand)) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }
    }

    /**
     * Store the class in the database
     *
     * @param bool $bPrim - Controls the return of the method
     * @return bool|int
     */
    public function save($bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }

        unset($oObj->kVersand);

        $kPrim = Shop::DB()->insert('tversand', $oObj);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * Update the class in the database
     *
     * @return int
     */
    public function update()
    {
        $_upd                = new stdClass();
        $_upd->kLieferschein = (int)$this->kLieferschein;
        $_upd->cLogistik     = $this->cLogistik;
        $_upd->cLogistikURL  = $this->cLogistikURL;
        $_upd->cIdentCode    = $this->cIdentCode;
        $_upd->cHinweis      = $this->cHinweis;
        $_upd->dErstellt     = $this->dErstellt;

        return Shop::DB()->update('tversand', 'kVersand', (int)$this->kVersand, $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     */
    public function delete()
    {
        return Shop::DB()->delete('tversand', 'kVersand', (int)$this->kVersand);
    }

    /**
     * @param int $kVersand
     * @return $this
     */
    public function setVersand($kVersand)
    {
        $this->kVersand = (int)$kVersand;

        return $this;
    }

    /**
     * @param int $kLieferschein
     * @return $this
     */
    public function setLieferschein($kLieferschein)
    {
        $this->kLieferschein = (int)$kLieferschein;

        return $this;
    }

    /**
     * @param string $cLogistik
     * @return $this
     */
    public function setLogistik($cLogistik)
    {
        $this->cLogistik = Shop::DB()->escape($cLogistik);

        return $this;
    }

    /**
     * @param string $cLogistikURL
     * @return $this
     */
    public function setLogistikURL($cLogistikURL)
    {
        $this->cLogistikURL = Shop::DB()->escape($cLogistikURL);

        return $this;
    }

    /**
     * @param string $cIdentCode
     * @return $this
     */
    public function setIdentCode($cIdentCode)
    {
        $this->cIdentCode = Shop::DB()->escape($cIdentCode);

        return $this;
    }

    /**
     * @param string $cHinweis
     * @return $this
     */
    public function setHinweis($cHinweis)
    {
        $this->cHinweis = Shop::DB()->escape($cHinweis);

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = Shop::DB()->escape($dErstellt);

        return $this;
    }

    /**
     * @return int
     */
    public function getVersand()
    {
        return $this->kVersand;
    }

    /**
     * @return int
     */
    public function getLieferschein()
    {
        return $this->kLieferschein;
    }

    /**
     * @return string
     */
    public function getLogistik()
    {
        return $this->cLogistik;
    }

    /**
     * @return string
     */
    public function getLogistikURL()
    {
        return $this->cLogistikURL;
    }

    /**
     * @return string
     */
    public function getIdentCode()
    {
        return $this->cIdentCode;
    }

    /**
     * @return string
     */
    public function getHinweis()
    {
        return $this->cHinweis;
    }

    /**
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * @return string
     */
    public function getLogistikVarUrl()
    {
        $cVarUrl = $this->cLogistikURL;

        if (isset($this->oData->cPLZ)) {
            $cVarUrl = str_replace(
                ['#PLZ#', '#IdentCode#'],
                [$this->oData->cPLZ, $this->cIdentCode],
                $this->cLogistikURL
            );
        }

        return $cVarUrl;
    }
}
