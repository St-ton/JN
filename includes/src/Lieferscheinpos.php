<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferscheinpos
 */
class Lieferscheinpos
{
    /**
     * @var int
     */
    protected $kLieferscheinPos;

    /**
     * @var int
     */
    protected $kLieferschein;

    /**
     * @var int
     */
    protected $kBestellPos;

    /**
     * @var int
     */
    protected $kWarenlager;

    /**
     * @var float
     */
    protected $fAnzahl;

    /**
     * @var array
     */
    public $oLieferscheinPosInfo_arr;

    /**
     * Lieferscheinpos constructor.
     * @param int $kLieferscheinPos
     */
    public function __construct(int $kLieferscheinPos = 0)
    {
        if ($kLieferscheinPos > 0) {
            $this->loadFromDB($kLieferscheinPos);
        }
    }

    /**
     * @param int $kLieferscheinPos
     * @return $this
     */
    private function loadFromDB(int $kLieferscheinPos = 0)
    {
        $oObj = Shop::Container()->getDB()->select('tlieferscheinpos', 'kLieferscheinPos', $kLieferscheinPos);
        if ($oObj !== null && $oObj->kLieferscheinPos > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
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

        unset($oObj->kLieferscheinPos, $oObj->oLieferscheinPosInfo_arr);
        $kPrim = Shop::Container()->getDB()->insert('tlieferscheinpos', $oObj);

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
        $_upd->kLieferschein = $this->getLieferschein();
        $_upd->kBestellPos   = $this->getBestellPos();
        $_upd->kWarenlager   = $this->getWarenlager();
        $_upd->fAnzahl       = $this->getAnzahl();

        return Shop::Container()->getDB()->update('tlieferscheinpos', 'kLieferscheinPos', $this->getLieferscheinPos(), $_upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tlieferscheinpos', 'kLieferscheinPos', $this->getLieferscheinPos());
    }

    /**
     * @param int $kLieferscheinPos
     * @return $this
     */
    public function setLieferscheinPos(int $kLieferscheinPos)
    {
        $this->kLieferscheinPos = $kLieferscheinPos;

        return $this;
    }

    /**
     * @param int $kLieferschein
     * @return $this
     */
    public function setLieferschein(int $kLieferschein)
    {
        $this->kLieferschein = $kLieferschein;

        return $this;
    }

    /**
     * @param int $kBestellPos
     * @return $this
     */
    public function setBestellPos(int $kBestellPos)
    {
        $this->kBestellPos = $kBestellPos;

        return $this;
    }

    /**
     * @param int $kWarenlager
     * @return $this
     */
    public function setWarenlager(int $kWarenlager)
    {
        $this->kWarenlager = $kWarenlager;

        return $this;
    }

    /**
     * @param float $fAnzahl
     * @return $this
     */
    public function setAnzahl($fAnzahl)
    {
        $this->fAnzahl = (float)$fAnzahl;

        return $this;
    }

    /**
     * @return int
     */
    public function getLieferscheinPos()
    {
        return (int)$this->kLieferscheinPos;
    }

    /**
     * @return int
     */
    public function getLieferschein()
    {
        return (int)$this->kLieferschein;
    }

    /**
     * @return int
     */
    public function getBestellPos()
    {
        return (int)$this->kBestellPos;
    }

    /**
     * @return int
     */
    public function getWarenlager()
    {
        return (int)$this->kWarenlager;
    }

    /**
     * @return float
     */
    public function getAnzahl()
    {
        return $this->fAnzahl;
    }
}
