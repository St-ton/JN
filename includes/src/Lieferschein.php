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
    public function __construct(int $kLieferschein = 0, $oData = null)
    {
        if ($kLieferschein > 0) {
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
    private function loadFromDB(int $kLieferschein = 0, $oData = null)
    {
        $oObj = Shop::Container()->getDB()->select('tlieferschein', 'kLieferschein', $kLieferschein);
        if ($oObj !== null && $oObj->kLieferschein > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $setter = 'set' . substr($cMember, 1);
                if (is_callable([$this, $setter])) {
                    $this->$setter($oObj->$cMember);
                } else {
                    $this->$cMember = $oObj->$cMember;
                }
            }

            $kLieferscheinPos_arr = Shop::Container()->getDB()->selectAll(
                'tlieferscheinpos', 
                'kLieferschein',
                $kLieferschein, 
                'kLieferscheinPos'
            );
            foreach ($kLieferscheinPos_arr as $oLieferscheinPos) {
                $pos                           = new Lieferscheinpos($oLieferscheinPos->kLieferscheinPos);
                $pos->oLieferscheinPosInfo_arr = [];

                $posInfos = Shop::Container()->getDB()->selectAll(
                    'tlieferscheinposinfo',
                    'kLieferscheinPos',
                    (int)$oLieferscheinPos->kLieferscheinPos,
                    'kLieferscheinPosInfo'
                );
                if (is_array($posInfos) && !empty($posInfos)) {
                    foreach ($posInfos as $posInfo) {
                        $pos->oLieferscheinPosInfo_arr[] = new Lieferscheinposinfo($posInfo->kLieferscheinPosInfo);
                    }
                }

                $this->oLieferscheinPos_arr[] = $pos;
            }

            $kVersand_arr = Shop::Container()->getDB()->selectAll(
                'tversand',
                'kLieferschein',
                $kLieferschein,
                'kVersand'
            );
            foreach ($kVersand_arr as $oVersand) {
                $this->oVersand_arr[] = new Versand($oVersand->kVersand, $oData);
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
        $oObj                   = new stdClass();
        $oObj->kInetBestellung  = $this->kInetBestellung;
        $oObj->cLieferscheinNr  = $this->cLieferscheinNr;
        $oObj->cHinweis         = $this->cHinweis;
        $oObj->nFulfillment     = $this->nFulfillment;
        $oObj->nStatus          = $this->nStatus;
        $oObj->dErstellt        = $this->dErstellt;
        $oObj->bEmailVerschickt = $this->bEmailVerschickt ? 1 : 0;
        $kPrim                  = Shop::Container()->getDB()->insert('tlieferschein', $oObj);
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
        $upd                   = new stdClass();
        $upd->kInetBestellung  = $this->kInetBestellung;
        $upd->cLieferscheinNr  = $this->cLieferscheinNr;
        $upd->cHinweis         = $this->cHinweis;
        $upd->nFulfillment     = $this->nFulfillment;
        $upd->nStatus          = $this->nStatus;
        $upd->dErstellt        = $this->dErstellt;
        $upd->bEmailVerschickt = $this->bEmailVerschickt ? 1 : 0;

        return Shop::Container()->getDB()->update('tlieferschein', 'kLieferschein', (int)$this->kLieferschein, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tlieferschein', 'kLieferschein', (int)$this->getLieferschein());
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
     * @param int $kInetBestellung
     * @return $this
     */
    public function setInetBestellung(int $kInetBestellung)
    {
        $this->kInetBestellung = $kInetBestellung;

        return $this;
    }

    /**
     * @param string $cLieferscheinNr
     * @return $this
     */
    public function setLieferscheinNr($cLieferscheinNr)
    {
        $this->cLieferscheinNr = Shop::Container()->getDB()->escape($cLieferscheinNr);

        return $this;
    }

    /**
     * @param string $cHinweis
     * @return $this
     */
    public function setHinweis($cHinweis)
    {
        $this->cHinweis = Shop::Container()->getDB()->escape($cHinweis);

        return $this;
    }

    /**
     * @param int $nFulfillment
     * @return $this
     */
    public function setFulfillment(int $nFulfillment)
    {
        $this->nFulfillment = $nFulfillment;

        return $this;
    }

    /**
     * @param int $nStatus
     * @return $this
     */
    public function setStatus(int $nStatus)
    {
        $this->nStatus = $nStatus;

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = Shop::Container()->getDB()->escape($dErstellt);

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
     * @return bool
     */
    public function getEmailVerschickt()
    {
        return $this->bEmailVerschickt;
    }
}
