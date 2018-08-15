<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Kupon
 */
class KuponBestellung
{
    /**
     * @var int
     */
    public $kKupon;

    /**
     * @var int
     */
    public $kBestellung;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cBestellNr;

    /**
     * @var float
     */
    public $fGesamtsummeBrutto;

    /**
     * @var float
     */
    public $fKuponwertBrutto;

    /**
     * @var string
     */
    public $cKuponTyp;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * KuponBestellung constructor.
     * @param int $kKupon
     * @param int $kBestellung
     */
    public function __construct(int $kKupon = 0, int $kBestellung = 0)
    {
        if ($kKupon > 0 && $kBestellung > 0) {
            $this->loadFromDB($kKupon, $kBestellung);
        }
    }

    /**
     * @param int $kKupon
     * @param int $kBestellung
     * @return $this
     */
    private function loadFromDB(int $kKupon = 0, int $kBestellung = 0): self
    {
        $oObj = Shop::Container()->getDB()->select(
            'tkuponbestelllung',
            'kKupon', $kKupon,
            'kBestellung', $kBestellung
        );

        if (isset($oObj->kKupon) && $oObj->kKupon > 0) {
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

        $kPrim = Shop::Container()->getDB()->insert('tkuponbestellung', $oObj);

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
        $_upd                      = new stdClass();
        $_upd->kKupon              = $this->kKupon;
        $_upd->kBestellung         = $this->kBestellung;
        $_upd->kKunde              = $this->kKunde;
        $_upd->cBestellNr          = $this->cBestellNr;
        $_upd->fGesammtsummeBrutto = $this->fGesamtsummeBrutto;
        $_upd->fKuponwertBrutto    = $this->fKuponwertBrutto;
        $_upd->cKuponTyp           = $this->cKuponTyp;
        $_upd->dErstellt           = $this->dErstellt;

        return Shop::Container()->getDB()->update(
            'tkuponbestellung',
            ['kKupon','kBestellung'],
            [(int)$this->kKupon,(int)$this->kBestellung],
            $_upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkupon', ['kKupon','kBestellung'], [(int)$this->kKupon,(int)$this->kBestellung]);
    }

    /**
     * @param int $kKupon
     * @return $this
     */
    public function setKupon(int $kKupon): self
    {
        $this->kKupon = $kKupon;

        return $this;
    }

    /**
     * @param int $kBestellung
     * @return $this
     */
    public function setBestellung(int $kBestellung): self
    {
        $this->kBestellung = $kBestellung;

        return $this;
    }

    /**
     * @param int $kKunde
     * @return $this
     */
    public function setKunden(int $kKunde): self
    {
        $this->kKunde = $kKunde;

        return $this;
    }

    /**
     * @param string $cBestellNr
     * @return $this
     */
    public function setBestellNr($cBestellNr): self
    {
        $this->cBestellNr = Shop::Container()->getDB()->escape($cBestellNr);

        return $this;
    }

    /**
     * @param float $fGesamtsummeBrutto
     * @return $this
     */
    public function setGesamtsummeBrutto($fGesamtsummeBrutto): self
    {
        $this->fGesamtsummeBrutto = (float)$fGesamtsummeBrutto;

        return $this;
    }

    /**
     * @param float $fKuponwertBrutto
     * @return $this
     */
    public function setKuponwertBrutto($fKuponwertBrutto): self
    {
        $this->fKuponwertBrutto = (float)$fKuponwertBrutto;

        return $this;
    }

    /**
     * @param string $cKuponTyp
     * @return $this
     */
    public function setKuponTyp($cKuponTyp): self
    {
        $this->cKuponTyp = Shop::Container()->getDB()->escape($cKuponTyp);

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
    public function getKupon()
    {
        return $this->kKupon;
    }

    /**
     * @return int|null
     */
    public function getBestellung()
    {
        return $this->kBestellung;
    }

    /**
     * @return int|null
     */
    public function getKunde()
    {
        return $this->kKunde;
    }

    /**
     * @return string|null
     */
    public function getBestellNr()
    {
        return $this->cBestellNr;
    }

    /**
     * @return float|null
     */
    public function getGesamtsummeBrutto()
    {
        return $this->fGesamtsummeBrutto;
    }

    /**
     * @return float|null
     */
    public function getKuponwertBrutto()
    {
        return $this->fKuponwertBrutto;
    }

    /**
     * @return string|null
     */
    public function getKuponTyp()
    {
        return $this->cKuponTyp;
    }

    /**
     * @return string|null
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * Gets used coupons from orders
     *
     * @param string $dStart
     * @param string $dEnd
     * @param int    $kKupon
     * @return array
     */
    public static function getOrdersWithUsedCoupons($dStart, $dEnd, int $kKupon = 0): array
    {
        return Shop::Container()->getDB()->query(
            "SELECT kbs.*, wkp.cName, kp.kKupon
                FROM tkuponbestellung AS kbs
                LEFT JOIN tbestellung AS bs 
                   ON kbs.kBestellung = bs.kBestellung
                LEFT JOIN twarenkorbpos AS wkp 
                    ON bs.kWarenkorb = wkp.kWarenkorb
                LEFT JOIN tkupon AS kp 
                    ON kbs.kKupon = kp.kKupon
                WHERE kbs.dErstellt BETWEEN '" . $dStart . "'
                    AND '" . $dEnd . "'
                    AND bs.cStatus != " . BESTELLUNG_STATUS_STORNO . "
                    AND (wkp.nPosTyp = 3 OR wkp.nPosTyp = 7) " .
                ($kKupon > 0 ? " AND kp.kKupon = " . $kKupon : '') . "
                ORDER BY kbs.dErstellt DESC",
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
    }
}
