<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

use Exception;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Cron\QueueEntry;
use JTL\Customer\Kundengruppe;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Category;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Session\Frontend;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\SmartyResourceNiceDB;
use Psr\Log\LoggerInterface;
use SmartyException;
use stdClass;
use function Functional\first;
use function Functional\map;

/**
 * Class Exportformat
 * @package JTL
 */
class Exportformat
{
    /**
     * @var int
     */
    protected $kExportformat;

    /**
     * @var int
     */
    protected $kKundengruppe;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var int
     */
    protected $kWaehrung;

    /**
     * @var int
     */
    protected $kKampagne;

    /**
     * @var int
     */
    protected $kPlugin;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @var string
     */
    protected $cDateiname;

    /**
     * @var string
     */
    protected $cKopfzeile;

    /**
     * @var string
     */
    protected $cContent;

    /**
     * @var string
     */
    protected $cFusszeile;

    /**
     * @var string
     */
    protected $cKodierung;

    /**
     * @var int
     */
    protected $nSpecial;

    /**
     * @var int
     */
    protected $nVarKombiOption;

    /**
     * @var int
     */
    protected $nSplitgroesse;

    /**
     * @var string
     */
    protected $dZuletztErstellt;

    /**
     * @var int
     */
    protected $nUseCache = 1;

    /**
     * @var \JTL\Smarty\JTLSmarty
     */
    protected $smarty;

    /**
     * @var object|null
     */
    private $oldSession;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var QueueEntry
     */
    protected $queue;

    /**
     * @var object
     */
    protected $currency;

    /**
     * @var string|null
     */
    private $campaignParameter;

    /**
     * @var string|null
     */
    private $campaignValue;

    /**
     * @var bool
     */
    private $isOk = false;

    /**
     * @var string
     */
    private $tempFileName;

    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Exportformat constructor.
     *
     * @param int              $kExportformat
     * @param DbInterface|null $db
     */
    public function __construct(int $kExportformat = 0, DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
        if ($kExportformat > 0) {
            $this->loadFromDB($kExportformat);
        }
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param string     $msg
     * @param null|array $context
     */
    private function log($msg, array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->log(\JTLLOG_LEVEL_NOTICE, $msg, $context);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kExportformat
     * @return $this
     */
    private function loadFromDB(int $kExportformat = 0): self
    {
        $oObj = $this->db->query(
            'SELECT texportformat.*, tkampagne.cParameter AS campaignParameter, tkampagne.cWert AS campaignValue
               FROM texportformat
               LEFT JOIN tkampagne 
                  ON tkampagne.kKampagne = texportformat.kKampagne
                  AND tkampagne.nAktiv = 1
               WHERE texportformat.kExportformat = ' . $kExportformat,
            ReturnType::SINGLE_OBJECT
        );
        if (isset($oObj->kExportformat) && $oObj->kExportformat > 0) {
            foreach (\get_object_vars($oObj) as $k => $v) {
                $this->$k = $v;
            }
            $this->setConfig($kExportformat);
            if (!$this->getKundengruppe()) {
                $this->setKundengruppe(Kundengruppe::getDefaultGroupID());
            }
            $this->isOk         = true;
            $this->tempFileName = 'tmp_' . $this->cDateiname;
        }

        return $this;
    }

    /**
     * @param int $kExportformat
     */
    private function setConfig(int $kExportformat): void
    {
        $confObj = $this->db->selectAll(
            'texportformateinstellungen',
            'kExportformat',
            $kExportformat
        );
        foreach ($confObj as $conf) {
            $this->config[$conf->cName] = $conf->cWert;
        }
        if (!isset($this->config['exportformate_lager_ueber_null'])) {
            $this->config['exportformate_lager_ueber_null'] = 'N';
        }
        if (!isset($this->config['exportformate_preis_ueber_null'])) {
            $this->config['exportformate_preis_ueber_null'] = 'N';
        }
        if (!isset($this->config['exportformate_beschreibung'])) {
            $this->config['exportformate_beschreibung'] = 'N';
        }
        if (!isset($this->config['exportformate_quot'])) {
            $this->config['exportformate_quot'] = 'N';
        }
        if (!isset($this->config['exportformate_equot'])) {
            $this->config['exportformate_equot'] = 'N';
        }
        if (!isset($this->config['exportformate_semikolon'])) {
            $this->config['exportformate_semikolon'] = 'N';
        }
    }

    /**
     * @return bool
     */
    public function isOK(): bool
    {
        return $this->isOk;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins                   = new stdClass();
        $ins->kKundengruppe    = (int)$this->kKundengruppe;
        $ins->kSprache         = (int)$this->kSprache;
        $ins->kWaehrung        = (int)$this->kWaehrung;
        $ins->kKampagne        = (int)$this->kKampagne;
        $ins->kPlugin          = (int)$this->kPlugin;
        $ins->cName            = $this->cName;
        $ins->cDateiname       = $this->cDateiname;
        $ins->cKopfzeile       = $this->cKopfzeile;
        $ins->cContent         = $this->cContent;
        $ins->cFusszeile       = $this->cFusszeile;
        $ins->cKodierung       = $this->cKodierung;
        $ins->nSpecial         = (int)$this->nSpecial;
        $ins->nVarKombiOption  = (int)$this->nVarKombiOption;
        $ins->nSplitgroesse    = (int)$this->nSplitgroesse;
        $ins->dZuletztErstellt = empty($this->dZuletztErstellt) ? '_DBNULL_' : $this->dZuletztErstellt;
        $ins->nUseCache        = $this->nUseCache;

        $this->kExportformat = $this->db->insert('texportformat', $ins);
        if ($this->kExportformat > 0) {
            return $bPrim ? $this->kExportformat : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                   = new stdClass();
        $upd->kKundengruppe    = (int)$this->kKundengruppe;
        $upd->kSprache         = (int)$this->kSprache;
        $upd->kWaehrung        = (int)$this->kWaehrung;
        $upd->kKampagne        = (int)$this->kKampagne;
        $upd->kPlugin          = (int)$this->kPlugin;
        $upd->cName            = $this->cName;
        $upd->cDateiname       = $this->cDateiname;
        $upd->cKopfzeile       = $this->cKopfzeile;
        $upd->cContent         = $this->cContent;
        $upd->cFusszeile       = $this->cFusszeile;
        $upd->cKodierung       = $this->cKodierung;
        $upd->nSpecial         = (int)$this->nSpecial;
        $upd->nVarKombiOption  = (int)$this->nVarKombiOption;
        $upd->nSplitgroesse    = (int)$this->nSplitgroesse;
        $upd->dZuletztErstellt = empty($this->dZuletztErstellt) ? '_DBNULL_' : $this->dZuletztErstellt;
        $upd->nUseCache        = $this->nUseCache;

        return $this->db->update('texportformat', 'kExportformat', $this->getExportformat(), $upd);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setTempFileName(string $name): self
    {
        $this->tempFileName = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return $this->db->delete('texportformat', 'kExportformat', $this->getExportformat());
    }

    /**
     * @param int $kExportformat
     * @return $this
     */
    public function setExportformat(int $kExportformat): self
    {
        $this->kExportformat = $kExportformat;

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return $this
     */
    public function setKundengruppe(int $kKundengruppe): self
    {
        $this->kKundengruppe = $kKundengruppe;

        return $this;
    }

    /**
     * /**
     * @param int $kSprache
     * @return $this
     */
    public function setSprache(int $kSprache): self
    {
        $this->kSprache = $kSprache;

        return $this;
    }

    /**
     * @param int $kWaehrung
     * @return $this
     */
    public function setWaehrung(int $kWaehrung): self
    {
        $this->kWaehrung = $kWaehrung;

        return $this;
    }

    /**
     * @param int $kKampagne
     * @return $this
     */
    public function setKampagne(int $kKampagne): self
    {
        $this->kKampagne = $kKampagne;

        return $this;
    }

    /**
     * @param int $kPlugin
     * @return $this
     */
    public function setPlugin(int $kPlugin): self
    {
        $this->kPlugin = $kPlugin;

        return $this;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName(string $cName): self
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @param string $cDateiname
     * @return $this
     */
    public function setDateiname(string $cDateiname): self
    {
        $this->cDateiname = $cDateiname;

        return $this;
    }

    /**
     * @param string $cKopfzeile
     * @return $this
     */
    public function setKopfzeile($cKopfzeile): self
    {
        $this->cKopfzeile = $cKopfzeile;

        return $this;
    }

    /**
     * @param string $cContent
     * @return $this
     */
    public function setContent($cContent): self
    {
        $this->cContent = $cContent;

        return $this;
    }

    /**
     * @param string $cFusszeile
     * @return $this
     */
    public function setFusszeile($cFusszeile): self
    {
        $this->cFusszeile = $cFusszeile;

        return $this;
    }

    /**
     * @param string $cKodierung
     * @return $this
     */
    public function setKodierung($cKodierung): self
    {
        $this->cKodierung = $cKodierung;

        return $this;
    }

    /**
     * @param int $nSpecial
     * @return $this
     */
    public function setSpecial(int $nSpecial): self
    {
        $this->nSpecial = $nSpecial;

        return $this;
    }

    /**
     * @param int $nVarKombiOption
     * @return $this
     */
    public function setVarKombiOption(int $nVarKombiOption): self
    {
        $this->nVarKombiOption = $nVarKombiOption;

        return $this;
    }

    /**
     * @param int $nSplitgroesse
     * @return $this
     */
    public function setSplitgroesse(int $nSplitgroesse): self
    {
        $this->nSplitgroesse = $nSplitgroesse;

        return $this;
    }

    /**
     * @param string $dZuletztErstellt
     * @return $this
     */
    public function setZuletztErstellt($dZuletztErstellt): self
    {
        $this->dZuletztErstellt = $dZuletztErstellt;

        return $this;
    }

    /**
     * @return int
     */
    public function getExportformat(): int
    {
        return (int)$this->kExportformat;
    }

    /**
     * @return int
     */
    public function getKundengruppe(): int
    {
        return (int)$this->kKundengruppe;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return (int)$this->kSprache;
    }

    /**
     * @return int
     */
    public function getWaehrung(): int
    {
        return (int)$this->kWaehrung;
    }

    /**
     * @return int
     */
    public function getKampagne(): int
    {
        return (int)$this->kKampagne;
    }

    /**
     * @return int
     */
    public function getPlugin(): int
    {
        return (int)$this->kPlugin;
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
    public function getDateiname(): ?string
    {
        return $this->cDateiname;
    }

    /**
     * @return string|null
     */
    public function getKopfzeile(): ?string
    {
        return $this->cKopfzeile;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->cContent;
    }

    /**
     * @return string|null
     */
    public function getFusszeile(): ?string
    {
        return $this->cFusszeile;
    }

    /**
     * @return string|null
     */
    public function getKodierung(): ?string
    {
        return $this->cKodierung;
    }

    /**
     * @return int|null
     */
    public function getSpecial(): ?int
    {
        return $this->nSpecial;
    }

    /**
     * @return int|null
     */
    public function getVarKombiOption(): ?int
    {
        return $this->nVarKombiOption;
    }

    /**
     * @return int|null
     */
    public function getSplitgroesse(): ?int
    {
        return $this->nSplitgroesse;
    }

    /**
     * @return string|null
     */
    public function getZuletztErstellt(): ?string
    {
        return $this->dZuletztErstellt;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return bool
     * @deprecated since 5.0.0
     */
    public function insertEinstellungen(array $config): bool
    {
        $ok = true;
        foreach ($config as $item) {
            $ins = new stdClass();
            if (\is_array($item) && \count($item) > 0) {
                foreach (\array_keys($item) as $cMember) {
                    $ins->$cMember = $item[$cMember];
                }
                $ins->kExportformat = $this->getExportformat();
            }
            $ok = $ok && ($this->db->insert('texportformateinstellungen', $ins) > 0);
        }

        return $ok;
    }

    /**
     * @param array $config
     * @return bool
     * @deprecated since 5.0.0
     */
    public function updateEinstellungen(array $config): bool
    {
        $ok = true;
        foreach ($config as $conf) {
            $import = [
                'exportformate_semikolon',
                'exportformate_equot',
                'exportformate_quot'
            ];
            if (\in_array($conf['cName'], $import, true)) {
                $_upd        = new stdClass();
                $_upd->cWert = $conf['cWert'];
                $ok          = $ok && ($this->db->update(
                    'tboxensichtbar',
                    ['kExportformat', 'cName'],
                    [$this->getExportformat(), $conf['cName']],
                    $_upd
                ) >= 0);
            }
        }

        return $ok;
    }

    /**
     * @return Exportformat
     * @throws SmartyException
     */
    private function initSmarty(): self
    {
        $this->smarty = (new JTLSmarty(true, ContextType::EXPORT))
            ->setCaching(0)
            ->setTemplateDir(\PFAD_TEMPLATES)
            ->setCompileDir(\PFAD_ROOT . \PFAD_ADMIN . \PFAD_COMPILEDIR)
            ->registerResource('db', new SmartyResourceNiceDB($this->db, ContextType::EXPORT))
            ->assign('URL_SHOP', Shop::getURL())
            ->assign('Waehrung', Frontend::getCurrency())
            ->assign('Einstellungen', $this->getConfig());
        // disable php execution in export format templates for security
        if (\EXPORTFORMAT_USE_SECURITY) {
            $this->smarty->activateBackendSecurityMode();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function initSession(): self
    {
        if (isset($_SESSION['Kundengruppe'])) {
            $this->oldSession               = new stdClass();
            $this->oldSession->Kundengruppe = $_SESSION['Kundengruppe'];
            $this->oldSession->kSprache     = $_SESSION['kSprache'];
            $this->oldSession->cISO         = $_SESSION['cISOSprache'];
            $this->oldSession->Waehrung     = Frontend::getCurrency();
        }
        $this->currency = $this->kWaehrung > 0
            ? new Currency($this->kWaehrung)
            : (new Currency())->getDefault();
        Tax::setTaxRates();
        $net       = $this->db->select('tkundengruppe', 'kKundengruppe', $this->getKundengruppe());
        $languages = map($this->db->query(
            'SELECT *  FROM tsprache',
            ReturnType::ARRAY_OF_OBJECTS
        ), function ($lang) {
            $lang->kSprache = (int)$lang->kSprache;

            return $lang;
        });
        $langISO   = first($languages, function ($l) {
            return $l->kSprache === $this->getSprache();
        });

        $_SESSION['Kundengruppe']  = (new Kundengruppe($this->getKundengruppe()))
            ->setMayViewPrices(1)
            ->setMayViewCategories(1)
            ->setIsMerchant($net !== null ? $net->nNettoPreise : 0);
        $_SESSION['kKundengruppe'] = $this->getKundengruppe();
        $_SESSION['kSprache']      = $this->getSprache();
        $_SESSION['Sprachen']      = $languages;
        $_SESSION['Waehrung']      = $this->currency;
        Shop::setLanguage($this->getSprache(), $langISO->cISO ?? null);

        return $this;
    }

    /**
     * @return $this
     */
    private function restoreSession(): self
    {
        if ($this->oldSession !== null) {
            $_SESSION['Kundengruppe'] = $this->oldSession->Kundengruppe;
            $_SESSION['Waehrung']     = $this->oldSession->Waehrung;
            $_SESSION['kSprache']     = $this->oldSession->kSprache;
            $_SESSION['cISOSprache']  = $this->oldSession->cISO;
            Shop::setLanguage($this->oldSession->kSprache, $this->oldSession->cISO);
        }

        return $this;
    }

    /**
     * @param bool $countOnly
     * @return string
     */
    private function getExportSQL(bool $countOnly = false): string
    {
        $where = '';
        $join  = '';
        $limit = '';

        switch ($this->getVarKombiOption()) {
            case 2:
                $where = ' AND kVaterArtikel = 0';
                break;
            case 3:
                $where = ' AND (tartikel.nIstVater != 1 OR tartikel.kEigenschaftKombi > 0)';
                break;
            default:
                break;
        }
        if ($this->config['exportformate_lager_ueber_null'] === 'Y') {
            $where .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y'))";
        } elseif ($this->config['exportformate_lager_ueber_null'] === 'O') {
            $where .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                            OR tartikel.cLagerKleinerNull = 'Y')";
        }

        if ($this->config['exportformate_preis_ueber_null'] === 'Y') {
            $join .= ' JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                            AND tpreise.kKundengruppe = ' . $this->getKundengruppe() . '
                            AND tpreise.fVKNetto > 0';
        }

        if ($this->config['exportformate_beschreibung'] === 'Y') {
            $where .= " AND tartikel.cBeschreibung != ''";
        }

        $condition = 'AND (tartikel.dErscheinungsdatum IS NULL OR NOT (DATE(tartikel.dErscheinungsdatum) > CURDATE()))';
        $conf      = Shop::getSettings([\CONF_GLOBAL]);
        if (isset($conf['global']['global_erscheinende_kaeuflich'])
            && $conf['global']['global_erscheinende_kaeuflich'] === 'Y'
        ) {
            $condition = "AND (
                tartikel.dErscheinungsdatum IS NULL 
                OR NOT (DATE(tartikel.dErscheinungsdatum) > CURDATE())
                OR  (
                        DATE(tartikel.dErscheinungsdatum) > CURDATE()
                        AND (tartikel.cLagerBeachten = 'N' 
                            OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = 'Y')
                    )
            )";
        }

        if ($countOnly === true) {
            $select = 'COUNT(*) AS nAnzahl';
        } else {
            $select     = 'tartikel.kArtikel';
            $limit      = ' ORDER BY tartikel.kArtikel LIMIT ' . $this->getQueue()->taskLimit;
            $condition .= ' AND tartikel.kArtikel > ' . $this->getQueue()->lastProductID;
        }

        return 'SELECT ' . $select . "
            FROM tartikel
            LEFT JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . \FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            " . $join . '
            LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = ' . $this->getKundengruppe() . '
            WHERE tartikelattribut.kArtikelAttribut IS NULL' . $where . '
                AND tartikelsichtbarkeit.kArtikel IS NULL ' . $condition . $limit;
    }

    /**
     * @param QueueEntry $queue
     * @return $this
     */
    private function setQueue(QueueEntry $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * @return QueueEntry
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return bool
     */
    public function useCache(): bool
    {
        return (int)$this->nUseCache === 1;
    }

    /**
     * @param int $caching
     * @return $this
     */
    public function setCaching(int $caching): self
    {
        $this->nUseCache = $caching;

        return $this;
    }

    /**
     * @return int
     */
    public function getCaching(): int
    {
        return (int)$this->nUseCache;
    }

    /**
     * @param resource $handle
     * @return int
     */
    private function writeHeader($handle): int
    {
        $header = $this->getKopfzeile();
        if (\mb_strlen($header) === 0) {
            return 0;
        }
        $encoding = $this->getKodierung();
        if ($encoding === 'UTF-8') {
             \fwrite($handle, "\xEF\xBB\xBF");
        }
        if ($encoding === 'UTF-8' || $encoding === 'UTF-8noBOM') {
            $header = Text::convertUTF8($header);
        }

        return  \fwrite($handle, $header . "\n");
    }

    /**
     * @param resource $handle
     * @return int
     */
    private function writeFooter($handle): int
    {
        $footer = $this->getFusszeile();
        if (\mb_strlen($footer) === 0) {
            return 0;
        }
        $encoding = $this->getKodierung();
        if ($encoding === 'UTF-8' || $encoding === 'UTF-8noBOM') {
            $footer = Text::convertUTF8($footer);
        }

        return  \fwrite($handle, $footer);
    }

    /**
     * @return $this
     */
    private function splitFile(): self
    {
        if ((int)$this->nSplitgroesse <= 0 || !\file_exists(\PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname)) {
            return $this;
        }
        $fileCounter  = 1;
        $splits       = [];
        $nFileTypePos = \mb_strrpos($this->cDateiname, '.');
        // Dateiname splitten nach Name + Typ
        if ($nFileTypePos === false) {
            $splits[0] = $this->cDateiname;
        } else {
            $splits[0] = \mb_substr($this->cDateiname, 0, $nFileTypePos);
            $splits[1] = \mb_substr($this->cDateiname, $nFileTypePos);
        }
        // Ist die angelegte Datei größer als die Einstellung im Exportformat?
        \clearstatcache();
        if (\filesize(\PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname) >= ($this->nSplitgroesse * 1024 * 1024 - 102400)) {
            \sleep(2);
            $this->cleanupFiles($this->cDateiname, $splits[0]);
            $handle     = \fopen(\PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname, 'r');
            $nZeile     = 1;
            $new_handle = \fopen($this->getFileName($splits, $fileCounter), 'w');
            $nSizeDatei = 0;
            while (($cContent = \fgets($handle)) !== false) {
                if ($nZeile > 1) {
                    $nSizeZeile = \mb_strlen($cContent) + 2;
                    // Schwelle erreicht?
                    if ($nSizeDatei <= ($this->nSplitgroesse * 1024 * 1024 - 102400)) {
                        // Schreibe Content
                         \fwrite($new_handle, $cContent);
                        $nSizeDatei += $nSizeZeile;
                    } else {
                        // neue Datei
                        $this->writeFooter($new_handle);
                        \fclose($new_handle);
                        ++$fileCounter;
                        $new_handle = \fopen($this->getFileName($splits, $fileCounter), 'w');
                        $this->writeHeader($new_handle);
                        // Schreibe Content
                         \fwrite($new_handle, $cContent);
                        $nSizeDatei = $nSizeZeile;
                    }
                } elseif ($nZeile === 1) {
                    $this->writeHeader($new_handle);
                }
                ++$nZeile;
            }
            \fclose($new_handle);
            \fclose($handle);
            \unlink(\PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname);
        }

        return $this;
    }

    /**
     * @param array $splits
     * @param int   $fileCounter
     * @return string
     */
    private function getFileName($splits, $fileCounter): string
    {
        $fn = (\is_array($splits) && \count($splits) > 1)
            ? $splits[0] . $fileCounter . $splits[1]
            : $splits[0] . $fileCounter;

        return \PFAD_ROOT . \PFAD_EXPORT . $fn;
    }

    /**
     * @param string $fileName
     * @param string $fileNameSplit
     * @return $this
     */
    private function cleanupFiles(string $fileName, string $fileNameSplit): self
    {
        if (\is_dir(\PFAD_ROOT . \PFAD_EXPORT) && ($dir = \opendir(\PFAD_ROOT . \PFAD_EXPORT)) !== false) {
            while (($fdir = \readdir($dir)) !== false) {
                if ($fdir !== $fileName && \mb_strpos($fdir, $fileNameSplit) !== false) {
                    \unlink(\PFAD_ROOT . \PFAD_EXPORT . $fdir);
                }
            }
            \closedir($dir);
        }

        return $this;
    }

    /**
     * @param QueueEntry $queueObject
     * @param bool       $isAsync
     * @param bool       $back
     * @param bool       $isCron
     * @param int|null   $max
     * @return bool
     */
    public function startExport(
        QueueEntry $queueObject,
        bool $isAsync = false,
        bool $back = false,
        bool $isCron = false,
        int $max = null
    ): bool {
        $started = false;
        if (!$this->isOK()) {
            $this->log('Export is not ok.');

            return !$started;
        }
        $this->setQueue($queueObject)->initSession()->initSmarty();
        if ($this->getPlugin() > 0 && \mb_strpos($this->getContent(), \PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            $this->log('Starting plugin exportformat "' . $this->getName() .
                '" for language ' . $this->getSprache() . ' and customer group ' . $this->getKundengruppe() .
                ' with caching ' . ((Shop::Container()->getCache()->isActive() && $this->useCache())
                    ? 'enabled'
                    : 'disabled'));
            $loader  = Plugin\Helper::getLoaderByPluginID($this->getPlugin(), $this->db);
            $oPlugin = $loader->init($this->getPlugin());
            if ($isCron === true) {
                global $oJobQueue;
                $oJobQueue = $queueObject;
            } else {
                global $queue;
                $queue = $queueObject;
            }
            global $exportformat, $ExportEinstellungen;
            $exportformat                   = new stdClass();
            $exportformat->kKundengruppe    = $this->getKundengruppe();
            $exportformat->kExportformat    = $this->getExportformat();
            $exportformat->kSprache         = $this->getSprache();
            $exportformat->kWaehrung        = $this->getWaehrung();
            $exportformat->kKampagne        = $this->getKampagne();
            $exportformat->kPlugin          = $this->getPlugin();
            $exportformat->cName            = $this->getName();
            $exportformat->cDateiname       = $this->getDateiname();
            $exportformat->cKopfzeile       = $this->getKopfzeile();
            $exportformat->cContent         = $this->getContent();
            $exportformat->cFusszeile       = $this->getFusszeile();
            $exportformat->cKodierung       = $this->getKodierung();
            $exportformat->nSpecial         = $this->getSpecial();
            $exportformat->nVarKombiOption  = $this->getVarKombiOption();
            $exportformat->nSplitgroesse    = $this->getSplitgroesse();
            $exportformat->dZuletztErstellt = $this->getZuletztErstellt();
            $exportformat->nUseCache        = $this->getCaching();
            // needed by Google Shopping export format plugin
            $exportformat->tkampagne_cParameter = $this->campaignParameter;
            $exportformat->tkampagne_cWert      = $this->campaignValue;
            // needed for plugin exports
            $ExportEinstellungen = $this->getConfig();
            include $oPlugin->getPaths()->getExportPath() .
                \str_replace(\PLUGIN_EXPORTFORMAT_CONTENTFILE, '', $this->getContent());

            if ($queueObject->jobQueueID > 0 && empty($queueObject->cronID)) {
                $this->db->delete('texportqueue', 'kExportqueue', $queueObject->jobQueueID);
            }
            if (isset($_GET['back']) && $_GET['back'] === 'admin') {
                \header('Location: exportformate.php?action=exported&token=' .
                    $_SESSION['jtl_token'] . '&kExportformat=' . (int)$this->queue->foreignKeyID);
                exit;
            }
            $this->log('Finished export');

            return !$started;
        }
        $start        = \microtime(true);
        $cacheHits    = 0;
        $cacheMisses  = 0;
        $cOutput      = '';
        $errorMessage = '';
        if ((int)$this->queue->tasksExecuted === 0 && \file_exists(\PFAD_ROOT . \PFAD_EXPORT . $this->tempFileName)) {
            \unlink(\PFAD_ROOT . \PFAD_EXPORT . $this->tempFileName);
        }
        $datei = \fopen(\PFAD_ROOT . \PFAD_EXPORT . $this->tempFileName, 'a');
        if ($max === null) {
            $maxObj = $this->db->executeQuery(
                $this->getExportSQL(true),
                ReturnType::SINGLE_OBJECT
            );
            $max    = (int)$maxObj->nAnzahl;
        }

        $this->log('Starting exportformat "' . Text::convertUTF8($this->getName()) .
            '" for language ' . $this->getSprache() . ' and customer group ' . $this->getKundengruppe() .
            ' with caching ' . ((Shop::Container()->getCache()->isActive() && $this->useCache())
                ? 'enabled'
                : 'disabled') .
            ' - ' . $queueObject->tasksExecuted . '/' . $max . ' products exported');
        // Kopfzeile schreiben
        if ((int)$this->queue->tasksExecuted === 0) {
            $this->writeHeader($datei);
        }
        $content          = $this->getContent();
        $categoryFallback = (\mb_strpos($content, '->oKategorie_arr') !== false);
        $options          = Artikel::getExportOptions();
        $helper           = Category::getInstance($this->getSprache(), $this->getKundengruppe());
        $shopURL          = Shop::getURL();
        $imageBaseURL     = Shop::getImageBaseURL();
        $find             = ['<br />', '<br>', '</'];
        $replace          = [' ', ' ', ' </'];
        $findTwo          = ["\r\n", "\r", "\n", "\x0B", "\x0"];
        $replaceTwo       = [' ', ' ', ' ', ' ', ''];

        if (isset($this->config['exportformate_quot']) && $this->config['exportformate_quot'] !== 'N') {
            $findTwo[] = '"';
            if ($this->config['exportformate_quot'] === 'q' || $this->config['exportformate_quot'] === 'bq') {
                $replaceTwo[] = '\"';
            } elseif ($this->config['exportformate_quot'] === 'qq') {
                $replaceTwo[] = '""';
            } else {
                $replaceTwo[] = $this->config['exportformate_quot'];
            }
        }
        if (isset($this->config['exportformate_quot']) && $this->config['exportformate_equot'] !== 'N') {
            $findTwo[] = "'";
            if ($this->config['exportformate_equot'] === 'q' || $this->config['exportformate_equot'] === 'bq') {
                $replaceTwo[] = '"';
            } else {
                $replaceTwo[] = $this->config['exportformate_equot'];
            }
        }
        if (isset($this->config['exportformate_semikolon']) && $this->config['exportformate_semikolon'] !== 'N') {
            $findTwo[]    = ';';
            $replaceTwo[] = $this->config['exportformate_semikolon'];
        }
        foreach ($this->db->query(
            $this->getExportSQL(),
            ReturnType::QUERYSINGLE
        ) as $productData) {
            $product = new Artikel();
            $product->fuelleArtikel(
                $productData['kArtikel'],
                $options,
                $this->kKundengruppe,
                $this->kSprache,
                !$this->useCache()
            );
            $productCategoryID = $product->gibKategorie();
            if ($categoryFallback === true) {
                // since 4.05 the product class only stores category IDs in Artikel::oKategorie_arr
                // but old google base exports rely on category attributes that wouldn't be available anymore
                // so in that case we replace oKategorie_arr with an array of real Kategorie objects
                $categories = [];
                foreach ($product->oKategorie_arr as $categoryID) {
                    $categories[] = new Kategorie(
                        (int)$categoryID,
                        $this->kSprache,
                        $this->kKundengruppe,
                        !$this->useCache()
                    );
                }
                $product->oKategorie_arr = $categories;
            }

            if ($product->kArtikel <= 0) {
                continue;
            }
            $started = true;
            ++$this->queue->tasksExecuted;
            $this->queue->lastProductID = $product->kArtikel;

            if ($product->cacheHit === true) {
                ++$cacheHits;
            } else {
                ++$cacheMisses;
            }
            $product->cBeschreibungHTML     = Text::removeWhitespace(
                \str_replace(
                    $findTwo,
                    $replaceTwo,
                    \str_replace('"', '&quot;', $product->cBeschreibung)
                )
            );
            $product->cKurzBeschreibungHTML = Text::removeWhitespace(
                \str_replace(
                    $findTwo,
                    $replaceTwo,
                    \str_replace('"', '&quot;', $product->cKurzBeschreibung)
                )
            );
            $product->cName                 = Text::removeWhitespace(
                \str_replace(
                    $findTwo,
                    $replaceTwo,
                    Text::unhtmlentities(\strip_tags(\str_replace($find, $replace, $product->cName)))
                )
            );
            $product->cBeschreibung         = Text::removeWhitespace(
                \str_replace(
                    $findTwo,
                    $replaceTwo,
                    Text::unhtmlentities(\strip_tags(\str_replace($find, $replace, $product->cBeschreibung)))
                )
            );
            $product->cKurzBeschreibung     = Text::removeWhitespace(
                \str_replace(
                    $findTwo,
                    $replaceTwo,
                    Text::unhtmlentities(
                        \strip_tags(\str_replace($find, $replace, $product->cKurzBeschreibung))
                    )
                )
            );
            $product->fUst                  = Tax::getSalesTax($product->kSteuerklasse);
            $product->Preise->fVKBrutto     = Tax::getGross(
                $product->Preise->fVKNetto * $this->currency->getConversionFactor(),
                $product->fUst
            );
            $product->Preise->fVKNetto      = \round($product->Preise->fVKNetto, 2);
            $product->Kategorie             = new Kategorie(
                $productCategoryID,
                $this->kSprache,
                $this->kKundengruppe,
                !$this->useCache()
            );
            // calling gibKategoriepfad() should not be necessary
            // since it has already been called in Kategorie::loadFromDB()
            $product->Kategoriepfad = $product->Kategorie->cKategoriePfad ?? $helper->getPath($product->Kategorie);
            $product->Versandkosten = ShippingMethod::getLowestShippingFees(
                $this->config['exportformate_lieferland'] ?? '',
                $product,
                0,
                $this->kKundengruppe
            );
            if ($product->Versandkosten !== -1) {
                $price = Currency::convertCurrency($product->Versandkosten, null, $this->kWaehrung);
                if ($price !== false) {
                    $product->Versandkosten = $price;
                }
            }
            // Kampagne URL
            if (!empty($this->campaignParameter)) {
                $cSep           = (\mb_strpos($product->cURL, '.php') !== false) ? '&' : '?';
                $product->cURL .= $cSep . $this->campaignParameter . '=' . $this->campaignValue;
            }

            $product->cDeeplink    = $shopURL . '/' . $product->cURL;
            $product->Artikelbild  = $product->Bilder[0]->cPfadGross
                ? $imageBaseURL . $product->Bilder[0]->cPfadGross
                : '';
            $product->Lieferbar    = $product->fLagerbestand <= 0 ? 'N' : 'Y';
            $product->Lieferbar_01 = $product->fLagerbestand <= 0 ? 0 : 1;

            $_out = $this->smarty->assign('Artikel', $product)->fetch('db:' . $this->getExportformat());
            if (!empty($_out)) {
                $cOutput .= $_out . "\n";
            }

            \executeHook(\HOOK_DO_EXPORT_OUTPUT_FETCHED);
            if (!$isAsync && ($queueObject->tasksExecuted % \max(\round($queueObject->taskLimit / 10), 10)) === 0) {
                //max. 10 status updates per run
                $this->log($queueObject->tasksExecuted . '/' . $max . ' products exported');
            }
        }
        if (\mb_strlen($cOutput) > 0) {
             \fwrite($datei, (($this->cKodierung === 'UTF-8' || $this->cKodierung === 'UTF-8noBOM')
                ? Text::convertUTF8($cOutput)
                : $cOutput));
        }

        if ($isCron === false) {
            if ($started === true) {
                // One or more products have been exported
                \fclose($datei);
                $this->db->queryPrepared(
                    'UPDATE texportqueue SET
                        nLimit_n       = nLimit_n + :nLimitM,
                        nLastArticleID = :nLastArticleID
                        WHERE kExportqueue = :kExportqueue',
                    [
                        'nLimitM'        => $this->queue->taskLimit,
                        'nLastArticleID' => $this->queue->lastProductID,
                        'kExportqueue'   => (int)$this->queue->jobQueueID,
                    ],
                    ReturnType::DEFAULT
                );
                $protocol = ((isset($_SERVER['HTTPS']) && \mb_convert_case($_SERVER['HTTPS'], \MB_CASE_LOWER) === 'on')
                    || Request::checkSSL() === 2)
                    ? 'https://'
                    : 'http://';
                if ($isAsync) {
                    $oCallback                 = new stdClass();
                    $oCallback->kExportformat  = $this->getExportformat();
                    $oCallback->kExportqueue   = $this->queue->jobQueueID;
                    $oCallback->nMax           = $max;
                    $oCallback->nCurrent       = $this->queue->tasksExecuted;
                    $oCallback->nLastArticleID = $this->queue->lastProductID;
                    $oCallback->bFinished      = false;
                    $oCallback->bFirst         = ((int)$this->queue->tasksExecuted === 0);
                    $oCallback->cURL           = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                    $oCallback->cacheMisses    = $cacheMisses;
                    $oCallback->cacheHits      = $cacheHits;
                    echo \json_encode($oCallback);
                } else {
                    $cURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] .
                        '?e=' . (int)$this->queue->jobQueueID .
                        '&back=admin&token=' . $_SESSION['jtl_token'] . '&max=' . $max;
                    \header('Location: ' . $cURL);
                }
            } else {
                // There are no more articles to export
                $this->db->query(
                    'UPDATE texportformat 
                        SET dZuletztErstellt = NOW() 
                        WHERE kExportformat = ' . $this->getExportformat(),
                    ReturnType::DEFAULT
                );
                $this->db->delete('texportqueue', 'kExportqueue', (int)$this->queue->foreignKeyID);

                $this->writeFooter($datei);
                \fclose($datei);
                if (\copy(
                    \PFAD_ROOT . \PFAD_EXPORT . $this->tempFileName,
                    \PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname
                )
                ) {
                    \unlink(\PFAD_ROOT . \PFAD_EXPORT . $this->tempFileName);
                } else {
                    $errorMessage = 'Konnte Export-Datei ' .
                        \PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname .
                        ' nicht erstellen. Fehlende Schreibrechte?';
                }
                // Versucht (falls so eingestellt) die erstellte Exportdatei in mehrere Dateien zu splitten
                $this->splitFile();
                if ($back === true) {
                    if ($isAsync) {
                        $oCallback                 = new stdClass();
                        $oCallback->kExportformat  = $this->getExportformat();
                        $oCallback->nMax           = $max;
                        $oCallback->nCurrent       = $this->queue->tasksExecuted;
                        $oCallback->nLastArticleID = $this->queue->lastProductID;
                        $oCallback->bFinished      = true;
                        $oCallback->cacheMisses    = $cacheMisses;
                        $oCallback->cacheHits      = $cacheHits;
                        $oCallback->errorMessage   = $errorMessage;

                        echo \json_encode($oCallback);
                    } else {
                        \header(
                            'Location: exportformate.php?action=exported&token=' .
                            $_SESSION['jtl_token'] .
                            '&kExportformat=' . $this->getExportformat() .
                            '&max=' . $max .
                            '&hasError=' . (int)($errorMessage !== '')
                        );
                    }
                }
            }
        } else {
            //finalize job when there are no more products to export
            if ($started === false) {
                $this->log('Finalizing job...');
                $this->db->update(
                    'texportformat',
                    'kExportformat',
                    (int)$queueObject->foreignKeyID,
                    (object)['dZuletztErstellt' => 'NOW()']
                );
                if (\file_exists(\PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname)) {
                    $this->log('Deleting old export file ' . \PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname);
                    \unlink(\PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname);
                }
                // Schreibe Fusszeile
                $this->writeFooter($datei);
                \fclose($datei);
                if (\copy(
                    \PFAD_ROOT . \PFAD_EXPORT . $this->tempFileName,
                    \PFAD_ROOT . \PFAD_EXPORT . $this->cDateiname
                )
                ) {
                    \unlink(\PFAD_ROOT . \PFAD_EXPORT . $this->tempFileName);
                }
                // Versucht (falls so eingestellt) die erstellte Exportdatei in mehrere Dateien zu splitten
                $this->splitFile();
            }
            $this->log('Finished after ' . \round(\microtime(true) - $start, 4) .
                's. Product cache hits: ' . $cacheHits . ', misses: ' . $cacheMisses);
        }
        $this->restoreSession();

        if ($isAsync) {
            exit();
        }

        return !$started;
    }

    /**
     * @param array $post
     * @return array|bool
     */
    public function check(array $post)
    {
        $validation = [];
        if (empty($post['cName'])) {
            $validation['cName'] = 1;
        } else {
            $this->setName($post['cName']);
        }
        $pathinfo           = \pathinfo(\PFAD_ROOT . \PFAD_EXPORT . $post['cDateiname']);
        $extensionWhitelist = \array_map('\strtolower', \explode(',', \EXPORTFORMAT_ALLOWED_FORMATS));
        if (empty($post['cDateiname'])) {
            $validation['cDateiname'] = 1;
        } elseif (\mb_strpos($post['cDateiname'], '.') === false) { // Dateiendung fehlt
            $validation['cDateiname'] = 2;
        } elseif (\mb_strpos(\realpath($pathinfo['dirname']), \realpath(\PFAD_ROOT)) === false) {
            $validation['cDateiname'] = 3;
        } elseif (!\in_array(\mb_convert_case($pathinfo['extension'], \MB_CASE_LOWER), $extensionWhitelist, true)) {
            $validation['cDateiname'] = 4;
        } else {
            $this->setDateiname($post['cDateiname']);
        }
        if (!isset($post['nSplitgroesse'])) {
            $post['nSplitgroesse'] = 0;
        }
        if (empty($post['cContent'])) {
            $validation['cContent'] = 1;
        } elseif (!\EXPORTFORMAT_ALLOW_PHP
            && (
                \mb_strpos($post['cContent'], '{php}') !== false
                || \mb_strpos($post['cContent'], '<?php') !== false
                || \mb_strpos($post['cContent'], '<%') !== false
                || \mb_strpos($post['cContent'], '<%=') !== false
                || \mb_strpos($post['cContent'], '<script language="php">') !== false
            )
        ) {
            $validation['cContent'] = 2;
        } else {
            $this->setContent(\str_replace('<tab>', "\t", $post['cContent']));
        }
        if (!isset($post['kSprache']) || (int)$post['kSprache'] === 0) {
            $validation['kSprache'] = 1;
        } else {
            $this->setSprache($post['kSprache']);
        }
        if (!isset($post['kWaehrung']) || (int)$post['kWaehrung'] === 0) {
            $validation['kWaehrung'] = 1;
        } else {
            $this->setWaehrung($post['kWaehrung']);
        }
        if (!isset($post['kKundengruppe']) || (int)$post['kKundengruppe'] === 0) {
            $validation['kKundengruppe'] = 1;
        } else {
            $this->setKundengruppe($post['kKundengruppe']);
        }
        if (\count($validation) === 0) {
            $this->setCaching((int)$post['nUseCache'])
                 ->setVarKombiOption((int)$post['nVarKombiOption'])
                 ->setSplitgroesse((int)$post['nSplitgroesse'])
                 ->setSpecial(0)
                 ->setKodierung($post['cKodierung'])
                 ->setPlugin((int)($post['kPlugin'] ?? 0))
                 ->setExportformat((int)($post['kExportformat'] ?? 0))
                 ->setKampagne((int)($post['kKampagne'] ?? 0));
            if (isset($post['cFusszeile'])) {
                $this->setFusszeile(\str_replace('<tab>', "\t", $post['cFusszeile']));
            }
            if (isset($post['cKopfzeile'])) {
                $this->setKopfzeile(\str_replace('<tab>', "\t", $post['cKopfzeile']));
            }

            return true;
        }

        return $validation;
    }

    /**
     * @return bool|string
     */
    public function checkSyntax()
    {
        $this->initSession()->initSmarty();
        $error = false;
        try {
            $product     = null;
            $productData = $this->db->query(
                "SELECT * 
                    FROM tartikel 
                    WHERE kVaterArtikel = 0 
                    AND (cLagerBeachten = 'N' OR fLagerbestand > 0) LIMIT 1",
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($productData->kArtikel)) {
                $options                            = new stdClass();
                $options->nMerkmale                 = 1;
                $options->nAttribute                = 1;
                $options->nArtikelAttribute         = 1;
                $options->nKategorie                = 1;
                $options->nKeinLagerbestandBeachten = 1;
                $options->nMedienDatei              = 1;

                $product = new Artikel();
                $product->fuelleArtikel($productData->kArtikel, $options);
                $product->cDeeplink             = '';
                $product->Artikelbild           = '';
                $product->Lieferbar             = '';
                $product->Lieferbar_01          = '';
                $product->cBeschreibungHTML     = '';
                $product->cKurzBeschreibungHTML = '';
                $product->fUst                  = 0;
                $product->Kategorie             = new Kategorie();
                $product->Kategoriepfad         = '';
                $product->Versandkosten         = -1;
            }
            $this->smarty->assign('Artikel', $product)
                         ->fetch('db:' . $this->kExportformat);
        } catch (Exception $e) {
            $error  = '<strong>Smarty-Syntaxfehler:</strong><br />';
            $error .= '<pre>' . $e->getMessage() . '</pre>';
        }

        return $error;
    }
}
