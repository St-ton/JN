<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferschein
 */
class Lieferschein
{
    /**
     * @var int
     */
    protected $kLieferschein;

    /**
     * @var int
     */
    protected $kInetBestellung;

    /**
     * @var string
     */
    protected $cLieferscheinNr;

    /**
     * @var string
     */
    protected $cHinweis;

    /**
     * @var int
     */
    protected $nFulfillment;

    /**
     * @var int
     */
    protected $nStatus;

    /**
     * @var string
     */
    protected $dErstellt;

    /**
     * @var bool
     */
    protected $bEmailVerschickt;

    /**
     * @var array
     */
    public $oLieferscheinPos_arr = [];

    /**
     * @var array
     */
    public $oVersand_arr = [];

    /**
     * @var array
     */
    public $oPosition_arr = [];

    /**
     * Constructor
     *
     * @param int    $kLieferschein
     * @param object $oData
     */
    public function __construct($kLieferschein = 0, $oData = null)
    {
        if ((int)$kLieferschein > 0) {
            $this->loadFromDB($kLieferschein, $oData);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int    $kLieferschein primary key
     * @param object $oData
     * @return $this
     */
    private function loadFromDB($kLieferschein = 0, $oData = null)
    {
        $kLieferschein = (int)$kLieferschein;
        $oObj          = Shop::DB()->select('tlieferschein', 'kLieferschein', $kLieferschein);
        if ($oObj !== null && $oObj->kLieferschein > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }

            $kLieferscheinPos_arr = Shop::DB()->selectAll('tlieferscheinpos', 'kLieferschein', $kLieferschein, 'kLieferscheinPos');

            foreach ($kLieferscheinPos_arr as $oLieferscheinPos) {
                $oLieferscheinpos                           = new Lieferscheinpos($oLieferscheinPos->kLieferscheinPos);
                $oLieferscheinpos->oLieferscheinPosInfo_arr = [];

                $kLieferscheinPosInfo_arr = Shop::DB()->selectAll(
                    'tlieferscheinposinfo',
                    'kLieferscheinPos',
                    (int)$oLieferscheinPos->kLieferscheinPos,
                    'kLieferscheinPosInfo'
                );
                if (is_array($kLieferscheinPosInfo_arr) && !empty($kLieferscheinPosInfo_arr)) {
                    foreach ($kLieferscheinPosInfo_arr as $oLieferscheinPosInfo) {
                        $oLieferscheinpos->oLieferscheinPosInfo_arr[] = new Lieferscheinposinfo($oLieferscheinPosInfo->kLieferscheinPosInfo);
                    }
                }

                $this->oLieferscheinPos_arr[] = $oLieferscheinpos;
            }

            $kVersand_arr = Shop::DB()->selectAll('tversand', 'kLieferschein', $kLieferschein, 'kVersand');

            foreach ($kVersand_arr as $oVersand) {
                $this->oVersand_arr[] = new Versand($oVersand->kVersand, $oData);
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
        $oObj                   = new stdClass();
        $oObj->kInetBestellung  = $this->kInetBestellung;
        $oObj->cLieferscheinNr  = $this->cLieferscheinNr;
        $oObj->cHinweis         = $this->cHinweis;
        $oObj->nFulfillment     = $this->nFulfillment;
        $oObj->nStatus          = $this->nStatus;
        $oObj->dErstellt        = $this->dErstellt;
        $oObj->bEmailVerschickt = $this->bEmailVerschickt;
        $kPrim                  = Shop::DB()->insert('tlieferschein', $oObj);
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
        $upd                   = new stdClass();
        $upd->kInetBestellung  = $this->kInetBestellung;
        $upd->cLieferscheinNr  = $this->cLieferscheinNr;
        $upd->cHinweis         = $this->cHinweis;
        $upd->nFulfillment     = $this->nFulfillment;
        $upd->nStatus          = $this->nStatus;
        $upd->dErstellt        = $this->dErstellt;
        $upd->bEmailVerschickt = $this->bEmailVerschickt;

        return Shop::DB()->update('tlieferschein', 'kLieferschein', (int)$this->kLieferschein, $upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     */
    public function delete()
    {
        return Shop::DB()->delete('tlieferschein', 'kLieferschein', (int)$this->getLieferschein());
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
     * @param int $kInetBestellung
     * @return $this
     */
    public function setInetBestellung($kInetBestellung)
    {
        $this->kInetBestellung = (int)$kInetBestellung;

        return $this;
    }

    /**
     * @param string $cLieferscheinNr
     * @return $this
     */
    public function setLieferscheinNr($cLieferscheinNr)
    {
        $this->cLieferscheinNr = Shop::DB()->escape($cLieferscheinNr);

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
     * @param int $nFulfillment
     * @return $this
     */
    public function setFulfillment($nFulfillment)
    {
        $this->nFulfillment = (int)$nFulfillment;

        return $this;
    }

    /**
     * @param int $nStatus
     * @return $this
     */
    public function setStatus($nStatus)
    {
        $this->nStatus = (int)$nStatus;

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
     * @param bool $bEmailVerschickt
     * @return $this
     */
    public function setEmailVerschickt($bEmailVerschickt)
    {
        $this->bEmailVerschickt = (bool)$bEmailVerschickt;

        return $this;
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
    public function getInetBestellung()
    {
        return $this->kInetBestellung;
    }

    /**
     * @return string
     */
    public function getLieferscheinNr()
    {
        return $this->cLieferscheinNr;
    }

    /**
     * @return string
     */
    public function getHinweis()
    {
        return $this->cHinweis;
    }

    /**
     * @return int
     */
    public function getFulfillment()
    {
        return $this->nFulfillment;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->nStatus;
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
    public function getEmailVerschickt()
    {
        return $this->bEmailVerschickt;
    }
}
