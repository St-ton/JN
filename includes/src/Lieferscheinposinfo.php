<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferscheinposinfo
 */
class Lieferscheinposinfo
{
    /**
     * @var int
     */
    protected $kLieferscheinPosInfo;

    /**
     * @var int
     */
    protected $kLieferscheinPos;

    /**
     * @var string
     */
    protected $cSeriennummer;

    /**
     * @var string
     */
    protected $cChargeNr;

    /**
     * @var string
     */
    protected $dMHD;

    /**
     * Constructor
     *
     * @param int $kLieferscheinPosInfo
     */
    public function __construct($kLieferscheinPosInfo = 0)
    {
        if ((int)$kLieferscheinPosInfo > 0) {
            $this->loadFromDB($kLieferscheinPosInfo);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kLieferscheinPosInfo
     * @return $this
     */
    private function loadFromDB($kLieferscheinPosInfo = 0)
    {
        $oObj = Shop::Container()->getDB()->select('tlieferscheinposinfo', 'kLieferscheinPosInfo', (int)$kLieferscheinPosInfo);

        if ($oObj !== null && $oObj->kLieferscheinPosInfo > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }

        return $this;
    }

    /**
     * Store the class in the database
     *
     * @param bool $bPrim Controls the return of the method
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

        unset($oObj->kLieferscheinPosInfo);

        $kPrim = Shop::Container()->getDB()->insert('tlieferscheinposinfo', $oObj);

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
        $_upd                   = new stdClass();
        $_upd->kLieferscheinPos = $this->getLieferscheinPos();
        $_upd->cSeriennummer    = $this->getSeriennummer();
        $_upd->cChargeNr        = $this->getChargeNr();
        $_upd->dMHD             = $this->getMHD();

        return Shop::Container()->getDB()->update('tlieferscheinposinfo', 'kLieferscheinPosInfo', $this->getLieferscheinPosInfo(), $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     */
    public function delete()
    {
        return Shop::Container()->getDB()->delete('tlieferscheinposinfo', 'kLieferscheinPosInfo', $this->getLieferscheinPosInfo());
    }

    /**
     * @param int $kLieferscheinPosInfo
     * @return $this
     */
    public function setLieferscheinPosInfo($kLieferscheinPosInfo)
    {
        $this->kLieferscheinPosInfo = (int)$kLieferscheinPosInfo;

        return $this;
    }

    /**
     * @param int $kLieferscheinPos
     * @return $this
     */
    public function setLieferscheinPos($kLieferscheinPos)
    {
        $this->kLieferscheinPos = (int)$kLieferscheinPos;

        return $this;
    }

    /**
     * @param string $cSeriennummer
     * @return $this
     */
    public function setSeriennummer($cSeriennummer)
    {
        $this->cSeriennummer = Shop::Container()->getDB()->escape($cSeriennummer);

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setChargeNr($cChargeNr)
    {
        $this->cChargeNr = Shop::Container()->getDB()->escape($cChargeNr);

        return $this;
    }

    /**
     * @param string $dMHD
     * @return $this
     */
    public function setMHD($dMHD)
    {
        $this->dMHD = Shop::Container()->getDB()->escape($dMHD);

        return $this;
    }

    /**
     * @return int
     */
    public function getLieferscheinPosInfo()
    {
        return (int)$this->kLieferscheinPosInfo;
    }

    /**
     * @return int
     */
    public function getLieferscheinPos()
    {
        return (int)$this->kLieferscheinPos;
    }

    /**
     * @return string
     */
    public function getSeriennummer()
    {
        return $this->cSeriennummer;
    }

    /**
     * @return string
     */
    public function getChargeNr()
    {
        return $this->cChargeNr;
    }

    /**
     * @return string
     */
    public function getMHD()
    {
        return $this->dMHD;
    }
}
