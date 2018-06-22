<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Emailvorlage
 */
class Emailvorlage
{
    /**
     * @var int
     */
    protected $kEmailvorlage;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @var string
     */
    protected $cBeschreibung;

    /**
     * @var string
     */
    protected $cMailTyp;

    /**
     * @var string
     */
    protected $cModulId;

    /**
     * @var string
     */
    protected $cDateiname;

    /**
     * @var string
     */
    protected $cAktiv;

    /**
     * @var int
     */
    protected $nAKZ;

    /**
     * @var int
     */
    protected $nAGB;

    /**
     * @var int
     */
    protected $nWRB;

    /**
     * @var int
     */
    protected $nWRBForm;

    /**
     * @var int
     */
    protected $nDSE;

    /**
     * @var int
     */
    protected $nFehlerhaft;

    /**
     * @var array
     */
    protected $oEinstellung_arr;

    /**
     * @var array
     */
    protected $oEinstellungAssoc_arr;

    /**
     * Constructor
     *
     * @param int  $kEmailvorlage
     * @param bool $bPlugin
     */
    public function __construct(int $kEmailvorlage = 0, bool $bPlugin = false)
    {
        if ($kEmailvorlage > 0) {
            $this->loadFromDB($kEmailvorlage, $bPlugin);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int  $kEmailvorlage
     * @param bool $bPlugin
     * @return $this
     */
    private function loadFromDB(int $kEmailvorlage, bool $bPlugin): self
    {
        $cTable        = $bPlugin ? 'tpluginemailvorlage' : 'temailvorlage';
        $cTableSetting = $bPlugin ? 'tpluginemailvorlageeinstellungen' : 'temailvorlageeinstellungen';
        $oObj          = Shop::Container()->getDB()->select($cTable, 'kEmailvorlage', $kEmailvorlage);

        if (isset($oObj->kEmailvorlage) && $oObj->kEmailvorlage > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
            // Settings
            $this->oEinstellung_arr = Shop::Container()->getDB()->selectAll(
                $cTableSetting,
                'kEmailvorlage',
                $this->kEmailvorlage
            );
            // Assoc bauen
            if (is_array($this->oEinstellung_arr) && count($this->oEinstellung_arr) > 0) {
                $this->oEinstellungAssoc_arr = [];
                foreach ($this->oEinstellung_arr as $oEinstellung) {
                    $this->oEinstellungAssoc_arr[$oEinstellung->cKey] = $oEinstellung->cValue;
                }
            }
        }

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setEmailvorlage(int $kEmailvorlage): self
    {
        $this->kEmailvorlage = $kEmailvorlage;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setName($cName): self
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setBeschreibung($cBeschreibung): self
    {
        $this->cBeschreibung = $cBeschreibung;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setMailTyp($cMailTyp): self
    {
        $this->cMailTyp = $cMailTyp;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setModulId($cModulId): self
    {
        $this->cModulId = $cModulId;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setDateiname($cDateiname): self
    {
        $this->cDateiname = $cDateiname;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setAktiv($cAktiv): self
    {
        $this->cAktiv = $cAktiv;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setAKZ(int $nAKZ): self
    {
        $this->nAKZ = $nAKZ;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setAGB(int $nAGB): self
    {
        $this->nAGB = $nAGB;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setWRB(int $nWRB): self
    {
        $this->nWRB = $nWRB;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setDSE(int $nDSE): self
    {
        $this->nDSE = $nDSE;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setWRBForm(int $nWRBForm): self
    {
        $this->nWRBForm = $nWRBForm;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setFehlerhaft(int $nFehlerhaft): self
    {
        $this->nFehlerhaft = $nFehlerhaft;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmailvorlage(): int
    {
        return (int)$this->kEmailvorlage;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @return string
     */
    public function getBeschreibung()
    {
        return $this->cBeschreibung;
    }

    /**
     * @return string
     */
    public function getMailTyp()
    {
        return $this->cMailTyp;
    }

    /**
     * @return string
     */
    public function getModulId()
    {
        return $this->cModulId;
    }

    /**
     * @return string
     */
    public function getDateiname()
    {
        return $this->cDateiname;
    }

    /**
     * @return string
     */
    public function getAktiv()
    {
        return $this->cAktiv;
    }

    /**
     * @return int
     */
    public function getAKZ()
    {
        return $this->nAKZ;
    }

    /**
     * @return int
     */
    public function getAGB()
    {
        return $this->nAGB;
    }

    /**
     * @return int
     */
    public function getWRB()
    {
        return $this->nWRB;
    }

    /**
     * @return int
     */
    public function getWRBForm()
    {
        return $this->nWRBForm;
    }

    /**
     * @return int
     */
    public function getDSE()
    {
        return $this->nDSE;
    }

    /**
     * @return int
     */
    public function getFehlerhaft()
    {
        return $this->nFehlerhaft;
    }

    /**
     * @param string $modulId
     * @param bool   $isPlugin
     * @return Emailvorlage|null
     */
    public static function load(string $modulId, $isPlugin = false)
    {
        $table   = $isPlugin ? 'tpluginemailvorlage' : 'temailvorlage';
        $obj     = Shop::Container()->getDB()->select(
            $table,
            'cModulId', $modulId,
            null, null,
            null, null,
            false,
            'kEmailvorlage'
        );

        return ($obj !== null && isset($obj->kEmailvorlage) && (int)$obj->kEmailvorlage > 0)
            ? new self((int)$obj->kEmailvorlage, $isPlugin)
            : null;
    }
}
