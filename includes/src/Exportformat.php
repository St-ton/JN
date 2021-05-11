<?php

namespace JTL;

use Exception;
use InvalidArgumentException;
use JTL\Backend\AdminIO;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Cron\QueueEntry;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Category;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageModel;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Smarty\ExportSmarty;
use JTL\Smarty\JTLSmarty;
use Psr\Log\LoggerInterface;
use SmartyException;
use stdClass;
use function Functional\first;

/**
 * Class Exportformat
 * @package JTL
 */
class Exportformat
{
    public const SYNTAX_FAIL        = 1;
    public const SYNTAX_NOT_CHECKED = -1;
    public const SYNTAX_OK          = 0;

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
     * @var JTLSmarty
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
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var int
     */
    protected $nFehlerhaft = 0;

    /**
     * Exportformat constructor.
     *
     * @param int              $id
     * @param DbInterface|null $db
     */
    public function __construct(int $id = 0, DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id);
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
     * @param array|null $context
     */
    private function log(string $msg, ?array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->log(\JTLLOG_LEVEL_NOTICE, $msg, $context);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $id
     * @return $this
     */
    private function loadFromDB(int $id = 0): self
    {
        $data = $this->db->query(
            'SELECT texportformat.*, tkampagne.cParameter AS campaignParameter, tkampagne.cWert AS campaignValue
               FROM texportformat
               LEFT JOIN tkampagne 
                  ON tkampagne.kKampagne = texportformat.kKampagne
                  AND tkampagne.nAktiv = 1
               WHERE texportformat.kExportformat = ' . $id,
            ReturnType::SINGLE_OBJECT
        );
        if (isset($data->kExportformat) && $data->kExportformat > 0) {
            foreach (\get_object_vars($data) as $k => $v) {
                $this->$k = $v;
            }
            $this->setConfig($id);
            if (!$this->getKundengruppe()) {
                $this->setKundengruppe(CustomerGroup::getDefaultGroupID());
            }
            $this->isOk            = true;
            $this->tempFileName    = 'tmp_' . $this->cDateiname;
            $this->kWaehrung       = (int)$this->kWaehrung;
            $this->kSprache        = (int)$this->kSprache;
            $this->kKundengruppe   = (int)$this->kKundengruppe;
            $this->kPlugin         = (int)$this->kPlugin;
            $this->kExportformat   = (int)$this->kExportformat;
            $this->kKampagne       = (int)$this->kKampagne;
            $this->nSpecial        = (int)$this->nSpecial;
            $this->nSplitgroesse   = (int)$this->nSplitgroesse;
            $this->nUseCache       = (int)$this->nUseCache;
            $this->nVarKombiOption = (int)$this->nVarKombiOption;
        }

        return $this;
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
        $ins->nFehlerhaft      = self::SYNTAX_NOT_CHECKED;

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
        $upd->nFehlerhaft      = self::SYNTAX_NOT_CHECKED;

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
     * @param int $exportID
     * @return $this
     */
    public function setExportformat(int $exportID): self
    {
        $this->kExportformat = $exportID;

        return $this;
    }

    /**
     * @param int $customerGroupID
     * @return $this
     */
    public function setKundengruppe(int $customerGroupID): self
    {
        $this->kKundengruppe = $customerGroupID;

        return $this;
    }

    /**
     * /**
     * @param int $languageID
     * @return $this
     */
    public function setSprache(int $languageID): self
    {
        $this->kSprache = $languageID;

        return $this;
    }

    /**
     * @param int $currencyID
     * @return $this
     */
    public function setWaehrung(int $currencyID): self
    {
        $this->kWaehrung = $currencyID;

        return $this;
    }

    /**
     * @param int $campaignID
     * @return $this
     */
    public function setKampagne(int $campaignID): self
    {
        $this->kKampagne = $campaignID;

        return $this;
    }

    /**
     * @param int $pluginID
     * @return $this
     */
    public function setPlugin(int $pluginID): self
    {
        $this->kPlugin = $pluginID;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->cName = $name;

        return $this;
    }

    /**
     * @param string $fileName
     * @return $this
     */
    public function setDateiname(string $fileName): self
    {
        $this->cDateiname = $fileName;

        return $this;
    }

    /**
     * @param string $header
     * @return $this
     */
    public function setKopfzeile(string $header): self
    {
        $this->cKopfzeile = $header;

        return $this;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->cContent = $content;

        return $this;
    }

    /**
     * @param string $footer
     * @return $this
     */
    public function setFusszeile(string $footer): self
    {
        $this->cFusszeile = $footer;

        return $this;
    }

    /**
     * @param string $encoding
     * @return $this
     */
    public function setKodierung(string $encoding): self
    {
        $this->cKodierung = $encoding;

        return $this;
    }

    /**
     * @param int $special
     * @return $this
     */
    public function setSpecial(int $special): self
    {
        $this->nSpecial = $special;

        return $this;
    }

    /**
     * @param int $varcombiOption
     * @return $this
     */
    public function setVarKombiOption(int $varcombiOption): self
    {
        $this->nVarKombiOption = $varcombiOption;

        return $this;
    }

    /**
     * @param int $splitSize
     * @return $this
     */
    public function setSplitgroesse(int $splitSize): self
    {
        $this->nSplitgroesse = $splitSize;

        return $this;
    }

    /**
     * @param string $lastCreated
     * @return $this
     */
    public function setZuletztErstellt($lastCreated): self
    {
        $this->dZuletztErstellt = $lastCreated;

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
     * @return int
     */
    public function getExportProductCount(): int
    {
        $sql = $this->getExportSQL();
        $cid = 'xp_' . \md5($sql);
        if (($count = Shop::Container()->getCache()->get($cid)) !== false) {
            return $count ?? 0;
        }
        $count = (int)$this->db->query($this->getExportSQL(true), ReturnType::SINGLE_OBJECT)->nAnzahl;
        Shop::Container()->getCache()->set($cid, $count, [\CACHING_GROUP_CORE], 120);

        return $count;
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
        return false;
    }

    /**
     * @return bool|string
     * @deprecated since 5.0.1 - do syntax check only with io-method because smarty syntax check can throw fatal error
     */
    public function checkSyntax()
    {
        return false;
    }

    /**
     * @return bool|string
     * @deprecated since 5.0.1 - do syntax check only with io-method because smarty syntax check can throw fatal error
     */
    public function doCheckSyntax()
    {
        return false;
    }

    /**
     * @return array
     * @deprecated since 5.0.1 - do syntax check only with io-method because smarty syntax check can throw fatal error
     */
    public function checkAll(): array
    {
        return [];
    }
}
