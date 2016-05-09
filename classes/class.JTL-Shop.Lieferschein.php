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
     * @access protected
     * @var int
     */
    protected $kLieferschein;

    /**
     * @access protected
     * @var int
     */
    protected $kInetBestellung;

    /**
     * @access protected
     * @var string
     */
    protected $cLieferscheinNr;

    /**
     * @access protected
     * @var string
     */
    protected $cHinweis;

    /**
     * @access protected
     * @var int
     */
    protected $nFulfillment;

    /**
     * @access protected
     * @var int
     */
    protected $nStatus;

    /**
     * @access protected
     * @var string
     */
    protected $dErstellt;

    /**
     * @access protected
     * @var bool
     */
    protected $bEmailVerschickt;

    /**
     * @access protected
     * @var array
     */
    public $oLieferscheinPos_arr = array();

    /**
     * @access protected
     * @var array
     */
    public $oVersand_arr = array();

    /**
     * @var array
     */
    public $oPosition_arr = array();

    /**
     * Constructor
     *
     * @param int    $kLieferschein
     * @param object $oData
     * @access public
     */
    public function __construct($kLieferschein = 0, $oData = null)
    {
        if (intval($kLieferschein) > 0) {
            $this->loadFromDB($kLieferschein, $oData);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int    $kLieferschein primary key
     * @param object $oData
     * @return $this
     * @access private
     */
    private function loadFromDB($kLieferschein = 0, $oData = null)
    {
        $kLieferschein = intval($kLieferschein);
        $oObj          = Shop::DB()->query("SELECT * FROM tlieferschein WHERE kLieferschein = " . $kLieferschein, 1);
        if ($oObj->kLieferschein > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }

            $kLieferscheinPos_arr = Shop::DB()->query("SELECT kLieferscheinPos FROM tlieferscheinpos WHERE kLieferschein = " . $kLieferschein, 2);

            foreach ($kLieferscheinPos_arr as $oLieferscheinPos) {
                $oLieferscheinpos                           = new Lieferscheinpos($oLieferscheinPos->kLieferscheinPos);
                $oLieferscheinpos->oLieferscheinPosInfo_arr = array();

                $kLieferscheinPosInfo_arr = Shop::DB()->query("SELECT kLieferscheinPosInfo FROM tlieferscheinposinfo WHERE kLieferscheinPos = " . intval($oLieferscheinPos->kLieferscheinPos), 2);
                if (is_array($kLieferscheinPosInfo_arr) && !empty($kLieferscheinPosInfo_arr)) {
                    foreach ($kLieferscheinPosInfo_arr as $oLieferscheinPosInfo) {
                        $oLieferscheinpos->oLieferscheinPosInfo_arr[] = new Lieferscheinposinfo($oLieferscheinPosInfo->kLieferscheinPosInfo);
                    }
                }

                $this->oLieferscheinPos_arr[] = $oLieferscheinpos;
            }

            $kVersand_arr = Shop::DB()->query("SELECT kVersand FROM tversand WHERE kLieferschein = " . $kLieferschein, 2);

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
     * @access public
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
     * @access public
     */
    public function update()
    {
        return Shop::DB()->query(
            "UPDATE tlieferschein
               SET kLieferschein = " . $this->kLieferschein . ",
                   kInetBestellung = " . $this->kInetBestellung . ",
                   cLieferscheinNr = '" . $this->cLieferscheinNr . "',
                   cHinweis = '" . $this->cHinweis . "',
                   nFulfillment = " . $this->nFulfillment . ",
                   nStatus = " . $this->nStatus . ",
                   dErstellt = '" . $this->dErstellt . "',
                   bEmailVerschickt = '" . $this->bEmailVerschickt . "'
               WHERE kLieferschein = " . intval($this->kLieferschein), 3
        );
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->delete('tlieferschein', 'kLieferschein', (int)$this->getLieferschein());
    }

    /**
     * Sets the kLieferschein
     *
     * @access public
     * @param int $kLieferschein
     * @return $this
     */
    public function setLieferschein($kLieferschein)
    {
        $this->kLieferschein = intval($kLieferschein);

        return $this;
    }

    /**
     * Sets the kInetBestellung
     *
     * @access public
     * @param int $kInetBestellung
     * @return $this
     */
    public function setInetBestellung($kInetBestellung)
    {
        $this->kInetBestellung = intval($kInetBestellung);

        return $this;
    }

    /**
     * Sets the cLieferscheinNr
     *
     * @access public
     * @param string $cLieferscheinNr
     * @return $this
     */
    public function setLieferscheinNr($cLieferscheinNr)
    {
        $this->cLieferscheinNr = Shop::DB()->escape($cLieferscheinNr);

        return $this;
    }

    /**
     * Sets the cHinweis
     *
     * @access public
     * @param string $cHinweis
     * @return $this
     */
    public function setHinweis($cHinweis)
    {
        $this->cHinweis = Shop::DB()->escape($cHinweis);

        return $this;
    }

    /**
     * Sets the nFulfillment
     *
     * @access public
     * @param int $nFulfillment
     * @return $this
     */
    public function setFulfillment($nFulfillment)
    {
        $this->nFulfillment = intval($nFulfillment);

        return $this;
    }

    /**
     * Sets the nStatus
     *
     * @access public
     * @param int $nStatus
     * @return $this
     */
    public function setStatus($nStatus)
    {
        $this->nStatus = intval($nStatus);

        return $this;
    }

    /**
     * Sets the dErstellt
     *
     * @access public
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = Shop::DB()->escape($dErstellt);

        return $this;
    }

    /**
     * Sets the bEmaiLVerschickt
     *
     * @access public
     * @param bool $bEmailVerschickt
     * @return $this
     */
    public function setEmailVerschickt($bEmailVerschickt)
    {
        $this->bEmailVerschickt = (bool) $bEmailVerschickt;

        return $this;
    }

    /**
     * Gets the kLieferschein
     *
     * @access public
     * @return int
     */
    public function getLieferschein()
    {
        return intval($this->kLieferschein);
    }

    /**
     * Gets the kInetBestellung
     *
     * @access public
     * @return int
     */
    public function getInetBestellung()
    {
        return $this->kInetBestellung;
    }

    /**
     * Gets the cLieferscheinNr
     *
     * @access public
     * @return string
     */
    public function getLieferscheinNr()
    {
        return $this->cLieferscheinNr;
    }

    /**
     * Gets the cHinweis
     *
     * @access public
     * @return string
     */
    public function getHinweis()
    {
        return $this->cHinweis;
    }

    /**
     * Gets the nFulfillment
     *
     * @access public
     * @return int
     */
    public function getFulfillment()
    {
        return $this->nFulfillment;
    }

    /**
     * Gets the nStatus
     *
     * @access public
     * @return int
     */
    public function getStatus()
    {
        return $this->nStatus;
    }

    /**
     * Gets the dErstellt
     *
     * @access public
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * Gets the bEmailVerschickt
     *
     * @access public
     * @return string
     */
    public function getEmailVerschickt()
    {
        return $this->bEmailVerschickt;
    }
}
