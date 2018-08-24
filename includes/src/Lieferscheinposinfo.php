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
     * Lieferscheinposinfo constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    private function loadFromDB(int $id = 0): self
    {
        $oObj = Shop::Container()->getDB()->select('tlieferscheinposinfo', 'kLieferscheinPosInfo', $id);

        if ($oObj !== null && $oObj->kLieferscheinPosInfo > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
            $this->kLieferscheinPos     = (int)$this->kLieferscheinPos;
            $this->kLieferscheinPosInfo = (int)$this->kLieferscheinPosInfo;
        }

        return $this;
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

        unset($oObj->kLieferscheinPosInfo);

        $kPrim = Shop::Container()->getDB()->insert('tlieferscheinposinfo', $oObj);

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
        $_upd                   = new stdClass();
        $_upd->kLieferscheinPos = $this->getLieferscheinPos();
        $_upd->cSeriennummer    = $this->getSeriennummer();
        $_upd->cChargeNr        = $this->getChargeNr();
        $_upd->dMHD             = $this->getMHD();

        return Shop::Container()->getDB()->update(
            'tlieferscheinposinfo',
            'kLieferscheinPosInfo',
            $this->getLieferscheinPosInfo(),
            $_upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete(
            'tlieferscheinposinfo',
            'kLieferscheinPosInfo',
            $this->getLieferscheinPosInfo()
        );
    }

    /**
     * @param int $kLieferscheinPosInfo
     * @return $this
     */
    public function setLieferscheinPosInfo(int $kLieferscheinPosInfo): self
    {
        $this->kLieferscheinPosInfo = $kLieferscheinPosInfo;

        return $this;
    }

    /**
     * @param int $kLieferscheinPos
     * @return $this
     */
    public function setLieferscheinPos(int $kLieferscheinPos): self
    {
        $this->kLieferscheinPos = $kLieferscheinPos;

        return $this;
    }

    /**
     * @param string $cSeriennummer
     * @return $this
     */
    public function setSeriennummer($cSeriennummer): self
    {
        $this->cSeriennummer = Shop::Container()->getDB()->escape($cSeriennummer);

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setChargeNr($cChargeNr): self
    {
        $this->cChargeNr = Shop::Container()->getDB()->escape($cChargeNr);

        return $this;
    }

    /**
     * @param string $dMHD
     * @return $this
     */
    public function setMHD($dMHD): self
    {
        $this->dMHD = Shop::Container()->getDB()->escape($dMHD);

        return $this;
    }

    /**
     * @return int
     */
    public function getLieferscheinPosInfo(): int
    {
        return (int)$this->kLieferscheinPosInfo;
    }

    /**
     * @return int
     */
    public function getLieferscheinPos(): int
    {
        return (int)$this->kLieferscheinPos;
    }

    /**
     * @return string|null
     */
    public function getSeriennummer()
    {
        return $this->cSeriennummer;
    }

    /**
     * @return string|null
     */
    public function getChargeNr()
    {
        return $this->cChargeNr;
    }

    /**
     * @return string|null
     */
    public function getMHD()
    {
        return $this->dMHD;
    }
}
