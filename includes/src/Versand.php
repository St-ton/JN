<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
    public function __construct(int $kVersand = 0, $oData = null)
    {
        if ($kVersand > 0) {
            $this->loadFromDB($kVersand, $oData);
        }
    }

    /**
     * @param int         $kVersand
     * @param null|object $oData
     */
    private function loadFromDB(int $kVersand = 0, $oData = null)
    {
        $oObj = Shop::Container()->getDB()->select('tversand', 'kVersand', $kVersand);

        $this->oData = $oData;

        if (!empty($oObj->kVersand)) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }

        unset($oObj->kVersand);

        $kPrim = Shop::Container()->getDB()->insert('tversand', $oObj);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $_upd                = new stdClass();
        $_upd->kLieferschein = (int)$this->kLieferschein;
        $_upd->cLogistik     = $this->cLogistik;
        $_upd->cLogistikURL  = $this->cLogistikURL;
        $_upd->cIdentCode    = $this->cIdentCode;
        $_upd->cHinweis      = $this->cHinweis;
        $_upd->dErstellt     = $this->dErstellt;

        return Shop::Container()->getDB()->update('tversand', 'kVersand', (int)$this->kVersand, $_upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tversand', 'kVersand', (int)$this->kVersand);
    }

    /**
     * @param int $kVersand
     * @return $this
     */
    public function setVersand(int $kVersand): self
    {
        $this->kVersand = $kVersand;

        return $this;
    }

    /**
     * @param int $kLieferschein
     * @return $this
     */
    public function setLieferschein(int $kLieferschein): self
    {
        $this->kLieferschein = $kLieferschein;

        return $this;
    }

    /**
     * @param string $cLogistik
     * @return $this
     */
    public function setLogistik($cLogistik): self
    {
        $this->cLogistik = Shop::Container()->getDB()->escape($cLogistik);

        return $this;
    }

    /**
     * @param string $cLogistikURL
     * @return $this
     */
    public function setLogistikURL($cLogistikURL): self
    {
        $this->cLogistikURL = Shop::Container()->getDB()->escape($cLogistikURL);

        return $this;
    }

    /**
     * @param string $cIdentCode
     * @return $this
     */
    public function setIdentCode($cIdentCode): self
    {
        $this->cIdentCode = Shop::Container()->getDB()->escape($cIdentCode);

        return $this;
    }

    /**
     * @param string $cHinweis
     * @return $this
     */
    public function setHinweis($cHinweis): self
    {
        $this->cHinweis = Shop::Container()->getDB()->escape($cHinweis);

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = Shop::Container()->getDB()->escape($dErstellt);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVersand()
    {
        return $this->kVersand;
    }

    /**
     * @return int|null
     */
    public function getLieferschein()
    {
        return $this->kLieferschein;
    }

    /**
     * @return string|null
     */
    public function getLogistik()
    {
        return $this->cLogistik;
    }

    /**
     * @return string|null
     */
    public function getLogistikURL()
    {
        return $this->cLogistikURL;
    }

    /**
     * @return string|null
     */
    public function getIdentCode()
    {
        return $this->cIdentCode;
    }

    /**
     * @return string|null
     */
    public function getHinweis()
    {
        return $this->cHinweis;
    }

    /**
     * @return string|null
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * @return string|null
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
