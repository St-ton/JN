<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Exportformat
 */
class Exportformat
{
    /**
     * @access protected
     * @var int
     */
    protected $kExportformat;

    /**
     * @access protected
     * @var int
     */
    protected $kKundengruppe;

    /**
     * @access protected
     * @var int
     */
    protected $kSprache;

    /**
     * @access protected
     * @var int
     */
    protected $kWaehrung;

    /**
     * @access protected
     * @var int
     */
    protected $kKampagne;

    /**
     * @access protected
     * @var int
     */
    protected $kPlugin;

    /**
     * @access protected
     * @var string
     */
    protected $cName;

    /**
     * @access protected
     * @var string
     */
    protected $cDateiname;

    /**
     * @access protected
     * @var string
     */
    protected $cKopfzeile;

    /**
     * @access protected
     * @var string
     */
    protected $cContent;

    /**
     * @access protected
     * @var string
     */
    protected $cFusszeile;

    /**
     * @access protected
     * @var string
     */
    protected $cKodierung;

    /**
     * @access protected
     * @var int
     */
    protected $nSpecial;

    /**
     * @access protected
     * @var int
     */
    protected $nVarKombiOption;

    /**
     * @access protected
     * @var int
     */
    protected $nSplitgroesse;

    /**
     * @access protected
     * @var string
     */
    protected $dZuletztErstellt;

    /**
     * Constructor
     *
     * @param int $kExportformat
     * @access public
     */
    public function __construct($kExportformat = 0)
    {
        if (intval($kExportformat) > 0) {
            $this->loadFromDB($kExportformat);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kExportformat
     * @return $this
     */
    public function loadFromDB($kExportformat = 0)
    {
        $oObj = Shop::DB()->select('texportformat', 'kExportformat', (int)$kExportformat);
        if (isset($oObj->kExportformat) && $oObj->kExportformat > 0) {
            foreach (get_object_vars($oObj) as $k => $v) {
                $this->$k = $v;
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
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }
        unset($oObj->kExportformat);
        $kPrim = Shop::DB()->insert('texportformat', $oObj);
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
        $_upd                   = new stdClass();
        $_upd->kKundengruppe    = (int)$this->kKundengruppe;
        $_upd->kSprache         = (int)$this->kSprache;
        $_upd->kWaehrung        = (int)$this->kWaehrung;
        $_upd->kKampagne        = (int)$this->kKampagne;
        $_upd->kPlugin          = (int)$this->kPlugin;
        $_upd->cName            = $this->cName;
        $_upd->cDateiname       = $this->cDateiname;
        $_upd->cKopfzeile       = $this->cKopfzeile;
        $_upd->cContent         = $this->cContent;
        $_upd->cFusszeile       = $this->cFusszeile;
        $_upd->cKodierung       = $this->cKodierung;
        $_upd->nSpecial         = (int)$this->nSpecial;
        $_upd->nVarKombiOption  = (int)$this->nVarKombiOption;
        $_upd->nSplitgroesse    = (int)$this->nSplitgroesse;
        $_upd->dZuletztErstellt = $this->dZuletztErstellt;

        return Shop::DB()->update('texportformat', 'kExportformat', $this->getExportformat(), $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->query("DELETE FROM texportformat WHERE kExportformat = " . $this->getExportformat(), 3);
    }

    /**
     * Sets the kExportformat
     *
     * @access public
     * @param int $kExportformat
     * @return $this
     */
    public function setExportformat($kExportformat)
    {
        $this->kExportformat = (int)$kExportformat;

        return $this;
    }

    /**
     * Sets the kKundengruppe
     *
     * @access public
     * @param int $kKundengruppe
     * @return $this
     */
    public function setKundengruppe($kKundengruppe)
    {
        $this->kKundengruppe = (int)$kKundengruppe;

        return $this;
    }

    /**
     * Sets the kSprache
     *
     * @access public
     * @param int $kSprache
     * @return $this
     */
    public function setSprache($kSprache)
    {
        $this->kSprache = (int)$kSprache;

        return $this;
    }

    /**
     * Sets the kWaehrung
     *
     * @access public
     * @param int $kWaehrung
     * @return $this
     */
    public function setWaehrung($kWaehrung)
    {
        $this->kWaehrung = (int)$kWaehrung;

        return $this;
    }

    /**
     * Sets the kKampagne
     *
     * @access public
     * @param int $kKampagne
     * @return $this
     */
    public function setKampagne($kKampagne)
    {
        $this->kKampagne = (int)$kKampagne;

        return $this;
    }

    /**
     * Sets the kPlugin
     *
     * @access public
     * @param int $kPlugin
     * @return $this
     */
    public function setPlugin($kPlugin)
    {
        $this->kPlugin = (int)$kPlugin;

        return $this;
    }

    /**
     * Sets the cName
     *
     * @access public
     * @param string $cName
     * @return $this
     */
    public function setName($cName)
    {
        $this->cName = Shop::DB()->escape($cName);

        return $this;
    }

    /**
     * Sets the cDateiname
     *
     * @access public
     * @param string $cDateiname
     * @return $this
     */
    public function setDateiname($cDateiname)
    {
        $this->cDateiname = Shop::DB()->escape($cDateiname);

        return $this;
    }

    /**
     * Sets the cKopfzeile
     *
     * @access public
     * @param string $cKopfzeile
     * @return $this
     */
    public function setKopfzeile($cKopfzeile)
    {
        $this->cKopfzeile = Shop::DB()->escape($cKopfzeile);

        return $this;
    }

    /**
     * Sets the cContent
     *
     * @access public
     * @param string $cContent
     * @return $this
     */
    public function setContent($cContent)
    {
        $this->cContent = Shop::DB()->escape($cContent);

        return $this;
    }

    /**
     * Sets the cFusszeile
     *
     * @access public
     * @param string $cFusszeile
     * @return $this
     */
    public function setFusszeile($cFusszeile)
    {
        $this->cFusszeile = Shop::DB()->escape($cFusszeile);

        return $this;
    }

    /**
     * Sets the cKodierung
     *
     * @access public
     * @param string $cKodierung
     * @return $this
     */
    public function setKodierung($cKodierung)
    {
        $this->cKodierung = Shop::DB()->escape($cKodierung);

        return $this;
    }

    /**
     * Sets the nSpecial
     *
     * @access public
     * @param int $nSpecial
     * @return $this
     */
    public function setSpecial($nSpecial)
    {
        $this->nSpecial = (int)$nSpecial;

        return $this;
    }

    /**
     * Sets the nVarKombiOption
     *
     * @access public
     * @param int $nVarKombiOption
     * @return $this
     */
    public function setVarKombiOption($nVarKombiOption)
    {
        $this->nVarKombiOption = (int)$nVarKombiOption;

        return $this;
    }

    /**
     * Sets the nSplitgroesse
     *
     * @access public
     * @param int $nSplitgroesse
     * @return $this
     */
    public function setSplitgroesse($nSplitgroesse)
    {
        $this->nSplitgroesse = (int)$nSplitgroesse;

        return $this;
    }

    /**
     * Sets the dZuletztErstellt
     *
     * @access public
     * @param string $dZuletztErstellt
     * @return $this
     */
    public function setZuletztErstellt($dZuletztErstellt)
    {
        $this->dZuletztErstellt = Shop::DB()->escape($dZuletztErstellt);

        return $this;
    }

    /**
     * Gets the kExportformat
     *
     * @access public
     * @return int
     */
    public function getExportformat()
    {
        return (int)$this->kExportformat;
    }

    /**
     * Gets the kKundengruppe
     *
     * @access public
     * @return int
     */
    public function getKundengruppe()
    {
        return (int)$this->kKundengruppe;
    }

    /**
     * Gets the kSprache
     *
     * @access public
     * @return int
     */
    public function getSprache()
    {
        return (int)$this->kSprache;
    }

    /**
     * Gets the kWaehrung
     *
     * @access public
     * @return int
     */
    public function getWaehrung()
    {
        return (int)$this->kWaehrung;
    }

    /**
     * Gets the kKampagne
     *
     * @access public
     * @return int
     */
    public function getKampagne()
    {
        return (int)$this->kKampagne;
    }

    /**
     * Gets the kPlugin
     *
     * @access public
     * @return int
     */
    public function getPlugin()
    {
        return (int)$this->kPlugin;
    }

    /**
     * Gets the cName
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * Gets the cDateiname
     *
     * @access public
     * @return string
     */
    public function getDateiname()
    {
        return $this->cDateiname;
    }

    /**
     * Gets the cKopfzeile
     *
     * @access public
     * @return string
     */
    public function getKopfzeile()
    {
        return $this->cKopfzeile;
    }

    /**
     * Gets the cContent
     *
     * @access public
     * @return string
     */
    public function getContent()
    {
        return $this->cContent;
    }

    /**
     * Gets the cFusszeile
     *
     * @access public
     * @return string
     */
    public function getFusszeile()
    {
        return $this->cFusszeile;
    }

    /**
     * Gets the cKodierung
     *
     * @access public
     * @return string
     */
    public function getKodierung()
    {
        return $this->cKodierung;
    }

    /**
     * Gets the nSpecial
     *
     * @access public
     * @return int
     */
    public function getSpecial()
    {
        return $this->nSpecial;
    }

    /**
     * Gets the nVarKombiOption
     *
     * @access public
     * @return int
     */
    public function getVarKombiOption()
    {
        return $this->nVarKombiOption;
    }

    /**
     * Gets the nSplitgroesse
     *
     * @access public
     * @return int
     */
    public function getSplitgroesse()
    {
        return $this->nSplitgroesse;
    }

    /**
     * Gets the dZuletztErstellt
     *
     * @access public
     * @return string
     */
    public function getZuletztErstellt()
    {
        return $this->dZuletztErstellt;
    }

    /**
     * @param array $einstellungenAssoc_arr
     * @return bool
     */
    public function insertEinstellungen($einstellungenAssoc_arr)
    {
        $ok = false;
        if (isset($einstellungenAssoc_arr) && is_array($einstellungenAssoc_arr)) {
            $ok = true;
            foreach ($einstellungenAssoc_arr as $einstellungAssoc_arr) {
                $oObj        = new stdClass();
                $cMember_arr = array_keys($einstellungAssoc_arr);
                if (is_array($einstellungAssoc_arr) && count($einstellungAssoc_arr) > 0) {
                    foreach ($cMember_arr as $cMember) {
                        $oObj->$cMember = $einstellungAssoc_arr[$cMember];
                    }
                    $oObj->kExportformat = $this->kExportformat;
                }
                $ok = $ok && (Shop::DB()->insert('texportformateinstellungen', $oObj) > 0);
            }
        }

        return $ok;
    }

    /**
     * @param array $einstellungenAssoc_arr
     * @return bool
     */
    public function updateEinstellungen($einstellungenAssoc_arr)
    {
        $ok = false;
        if (isset($einstellungenAssoc_arr) && is_array($einstellungenAssoc_arr)) {
            $ok = true;
            foreach ($einstellungenAssoc_arr as $einstellungAssoc_arr) {
                //Array mit zu importierenden Exportformateinstellungen
                $cExportEinstellungenToImport_arr = array('exportformate_semikolon', 'exportformate_equot', 'exportformate_quot');

                if (in_array($einstellungAssoc_arr['cName'], $cExportEinstellungenToImport_arr)) {
                    $_upd        = new stdClass();
                    $_upd->cWert = $einstellungAssoc_arr['cWert'];
                    $ok          = $ok && (Shop::DB()->update('tboxensichtbar', array('kExportformat', 'cName'), array($this->getExportformat(), $einstellungAssoc_arr['cName']), $_upd) >= 0);
                }
            }
        }

        return $ok;
    }
}
