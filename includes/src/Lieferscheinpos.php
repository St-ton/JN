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
     * Constructor
     *
     * @param int $kLieferscheinPos primarykey
     */
    public function __construct($kLieferscheinPos = 0)
    {
        if ((int)$kLieferscheinPos > 0) {
            $this->loadFromDB($kLieferscheinPos);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kLieferscheinPos
     * @return $this
     */
    private function loadFromDB($kLieferscheinPos = 0)
    {
        $oObj = Shop::Container()->getDB()->select('tlieferscheinpos', 'kLieferscheinPos', (int)$kLieferscheinPos);
        if ($oObj !== null && $oObj->kLieferscheinPos > 0) {
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

        unset($oObj->kLieferscheinPos, $oObj->oLieferscheinPosInfo_arr);
        $kPrim = Shop::Container()->getDB()->insert('tlieferscheinpos', $oObj);

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
        $_upd->kLieferschein = $this->getLieferschein();
        $_upd->kBestellPos   = $this->getBestellPos();
        $_upd->kWarenlager   = $this->getWarenlager();
        $_upd->fAnzahl       = $this->getAnzahl();

        return Shop::Container()->getDB()->update('tlieferscheinpos', 'kLieferscheinPos', $this->getLieferscheinPos(), $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     */
    public function delete()
    {
        return Shop::Container()->getDB()->delete('tlieferscheinpos', 'kLieferscheinPos', $this->getLieferscheinPos());
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
     * @param int $kLieferschein
     * @return $this
     */
    public function setLieferschein($kLieferschein)
    {
        $this->kLieferschein = (int)$kLieferschein;

        return $this;
    }

    /**
     * @param int $kBestellPos
     * @return $this
     */
    public function setBestellPos($kBestellPos)
    {
        $this->kBestellPos = (int)$kBestellPos;

        return $this;
    }

    /**
     * @param int $kWarenlager
     * @return $this
     */
    public function setWarenlager($kWarenlager)
    {
        $this->kWarenlager = (int)$kWarenlager;

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
