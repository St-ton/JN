<?php

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
     * Constructor
     *
     * @param int $kKupon - primarykey
     * @param int $kBestellung - primarykey
     */
    public function __construct($kKupon = 0, $kBestellung = 0)
    {
        if ((int)$kKupon > 0 && (int)$kBestellung > 0) {
            $this->loadFromDB($kKupon, $kBestellung);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kKupon
     * @param int $kBestellung
     * @return $this
     */
    private function loadFromDB($kKupon = 0, $kBestellung = 0)
    {
        $oObj = Shop::DB()->select(
            'tkuponbestelllung',
            'kKupon', (int)$kKupon,
            'kBestellung', (int)$kBestellung
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

        $kPrim = Shop::DB()->insert('tkuponbestellung', $oObj);

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
        $_upd                      = new stdClass();
        $_upd->kKupon              = $this->kKupon;
        $_upd->kBestellung         = $this->kBestellung;
        $_upd->kKunde              = $this->kKunde;
        $_upd->cBestellNr          = $this->cBestellNr;
        $_upd->fGesammtsummeBrutto = $this->fGesamtsummeBrutto;
        $_upd->fKuponwertBrutto    = $this->fKuponwertBrutto;
        $_upd->cKuponTyp           = $this->cKuponTyp;
        $_upd->dErstellt           = $this->dErstellt;

        return Shop::DB()->update(
            'tkuponbestellung',
            ['kKupon','kBestellung'],
            [(int)$this->kKupon,(int)$this->kBestellung],
            $_upd
        );
    }

    /**
     * Delete the class in the database
     *
     * @return int
     */
    public function delete()
    {
        return Shop::DB()->delete('tkupon', ['kKupon','kBestellung'], [(int)$this->kKupon,(int)$this->kBestellung]);
    }

    /**
     * @param int $kKupon
     * @return $this
     */
    public function setKupon($kKupon)
    {
        $this->kKupon = (int)$kKupon;

        return $this;
    }

    /**
     * @param int $kBestellung
     * @return $this
     */
    public function setBestellung($kBestellung)
    {
        $this->kBestellung = (int)$kBestellung;

        return $this;
    }

    /**
     * @param int $kKunde
     * @return $this
     */
    public function setKunden($kKunde)
    {
        $this->kKunde = (int)$kKunde;

        return $this;
    }

    /**
     * @param string $cBestellNr
     * @return $this
     */
    public function setBestellNr($cBestellNr)
    {
        $this->cBestellNr = Shop::DB()->escape($cBestellNr);

        return $this;
    }

    /**
     * @param float $fGesamtsummeBrutto
     * @return $this
     */
    public function setGesamtsummeBrutto($fGesamtsummeBrutto)
    {
        $this->fGesamtsummeBrutto = (float)$fGesamtsummeBrutto;

        return $this;
    }

    /**
     * @param float $fKuponwertBrutto
     * @return $this
     */
    public function setKuponwertBrutto($fKuponwertBrutto)
    {
        $this->fKuponwertBrutto = (float)$fKuponwertBrutto;

        return $this;
    }

    /**
     * @param string $cKuponTyp
     * @return $this
     */
    public function setKuponTyp($cKuponTyp)
    {
        $this->cKuponTyp = Shop::DB()->escape($cKuponTyp);

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
    public function getKupon()
    {
        return $this->kKupon;
    }

    /**
     * @return int
     */
    public function getBestellung()
    {
        return $this->kBestellung;
    }

    /**
     * @return int
     */
    public function getKunde()
    {
        return $this->kKunde;
    }

    /**
     * @return string
     */
    public function getBestellNr()
    {
        return $this->cBestellNr;
    }

    /**
     * @return float
     */
    public function getGesamtsummeBrutto()
    {
        return $this->fGesamtsummeBrutto;
    }

    /**
     * @return float
     */
    public function getKuponwertBrutto()
    {
        return $this->fKuponwertBrutto;
    }

    /**
     * @return string
     */
    public function getKuponTyp()
    {
        return $this->cKuponTyp;
    }

    /**
     * @return string
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
     * @return array
     */
    public static function getOrdersWithUsedCoupons($dStart, $dEnd)
    {
        $ordersWithUsedCoupons = Shop::DB()->query(
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
                    AND (wkp.nPosTyp = 3 OR wkp.nPosTyp = 7)
                ORDER BY kbs.dErstellt DESC", 9
        );

        return $ordersWithUsedCoupons;
    }
}
