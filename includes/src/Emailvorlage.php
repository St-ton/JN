<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Hydrator\TestHydrator;
use Exception;

/**
 * Class Emailvorlage
 * @package JTL
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
            foreach (\array_keys(\get_object_vars($oObj)) as $member) {
                $this->$member = $oObj->$member;
            }
            // Settings
            $this->oEinstellung_arr = Shop::Container()->getDB()->selectAll(
                $cTableSetting,
                'kEmailvorlage',
                $this->kEmailvorlage
            );
            // Assoc bauen
            if (\is_array($this->oEinstellung_arr) && \count($this->oEinstellung_arr) > 0) {
                $this->oEinstellungAssoc_arr = [];
                foreach ($this->oEinstellung_arr as $conf) {
                    $this->oEinstellungAssoc_arr[$conf->cKey] = $conf->cValue;
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
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return string|null
     */
    public function getBeschreibung(): ?string
    {
        return $this->cBeschreibung;
    }

    /**
     * @return string|null
     */
    public function getMailTyp(): ?string
    {
        return $this->cMailTyp;
    }

    /**
     * @return string|null
     */
    public function getModulId(): ?string
    {
        return $this->cModulId;
    }

    /**
     * @return string|null
     */
    public function getDateiname(): ?string
    {
        return $this->cDateiname;
    }

    /**
     * @return string|null
     */
    public function getAktiv(): ?string
    {
        return $this->cAktiv;
    }

    /**
     * @return int|null
     */
    public function getAKZ(): ?int
    {
        return $this->nAKZ;
    }

    /**
     * @return int|null
     */
    public function getAGB(): ?int
    {
        return $this->nAGB;
    }

    /**
     * @return int|null
     */
    public function getWRB(): ?int
    {
        return $this->nWRB;
    }

    /**
     * @return int|null
     */
    public function getWRBForm(): ?int
    {
        return $this->nWRBForm;
    }

    /**
     * @return int|null
     */
    public function getDSE(): ?int
    {
        return $this->nDSE;
    }

    /**
     * @return int|null
     */
    public function getFehlerhaft(): ?int
    {
        return $this->nFehlerhaft;
    }

    /**
     * @param string $modulId
     * @param bool   $isPlugin
     * @return Emailvorlage|null
     */
    public static function load(string $modulId, $isPlugin = false): ?self
    {
        $table = $isPlugin ? 'tpluginemailvorlage' : 'temailvorlage';
        $obj   = Shop::Container()->getDB()->select(
            $table,
            'cModulId',
            $modulId,
            null,
            null,
            null,
            null,
            false,
            'kEmailvorlage'
        );

        return ($obj !== null && isset($obj->kEmailvorlage) && (int)$obj->kEmailvorlage > 0)
            ? new self((int)$obj->kEmailvorlage, $isPlugin)
            : null;
    }

    /**
     * @param bool $error
     * @param bool $force
     * @param int $pluginID
     */
    public function updateError(bool $error = true, bool $force = false, int $pluginID = 0): void
    {
        $upd              = new \stdClass();
        $upd->nFehlerhaft = (int)$error;
        if (!$force) {
            $upd->cAktiv = $error ? 'N' : 'Y';
        }
        Shop::Container()->getDB()->update(
            $pluginID > 0 ? 'tpluginemailvorlage' : 'temailvorlage',
            'kEmailvorlage',
            $this->kEmailvorlage,
            $upd
        );

        $_SESSION['emailSyntaxErrorCount'] = count(
            Shop::Container()->getDB()->selectAll('temailvorlage', 'nFehlerhaft', 1)
        ) + count(
            Shop::Container()->getDB()->selectAll('tpluginemailvorlage', 'nFehlerhaft', 1)
        );
    }

    /**
     * @param int|null $pluginID
     * @return string
     * @throws \SmartyException
     */
    public function checkSyntax(int $pluginID = 0): string
    {
        $db           = Shop::Container()->getDB();
        $renderer     = new SmartyRenderer($db);
        $settings     = Shopsetting::getInstance();
        $hydrator     = new TestHydrator($renderer->getSmarty(), $db, $settings);
        $templateType = $pluginID ?: '_temailvorlagesprache';
        foreach (Sprache::getInstance()->gibVerfuegbareSprachen() as $lang) {
            try {
                $hydrator->hydrate(null, $lang);
                $id = $this->kEmailvorlage . '_' . $lang->kSprache . '_' . $templateType;
                $renderer->renderHTML($id);
                $renderer->renderText($id);
            } catch (Exception $e) {
                $this->updateError(true, false, $pluginID);

                return $e->getMessage();
            }
        }

        return '';
    }
}
