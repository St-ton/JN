<?php declare(strict_types=1);

namespace JTL\Export;

use JTL\Cron\QueueEntry;
use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\Smarty\ExportSmarty;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractExporter
 * @package JTL\Export
 */
abstract class AbstractExporter implements ExporterInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var ExportSmarty
     */
    protected $smarty;

    /**
     * @var QueueEntry
     */
    protected $queue;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ExportWriterInterface
     */
    protected $writer;

    /**
     * @var float
     */
    protected $startedAt;

    /**
     * FormatExporter constructor.
     * @param DbInterface                $db
     * @param LoggerInterface            $logger
     * @param ExportWriterInterface|null $writer
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, ?ExportWriterInterface $writer = null)
    {
        $this->db     = $db;
        $this->logger = $logger;
        $this->writer = $writer;
    }

    /**
     * @param AsyncCallback $cb
     */
    public function syncReturn(AsyncCallback $cb): void
    {
        \header(
            'Location: ' . Shop::getAdminURL() . '/exportformate.php?action=exported&token='
            . $_SESSION['jtl_token']
            . '&kExportformat=' . $this->getModel()->getId()
            . '&max=' . $cb->getProductCount()
            . '&hasError=' . (int)($cb->getError() !== '' && $cb->getError() !== null)
        );
    }

    /**
     * @param AsyncCallback $cb
     */
    public function syncContinue(AsyncCallback $cb): void
    {
        \header(
            'Location: ' . Shop::getAdminURL() . '/do_export.php'
            . '?e=' . (int)$this->getQueue()->jobQueueID
            . '&back=admin&token=' . $_SESSION['jtl_token']
            . '&max=' . $cb->getProductCount()
        );
    }

    /**
     * @param bool $countOnly
     * @return string
     */
    public function getExportSQL(bool $countOnly = false): string
    {
        $where = '';
        $join  = '';
        $limit = '';
        switch ($this->getModel()->getVarcombOption()) {
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
            $join .= ' JOIN tpreis ON tpreis.kArtikel = tartikel.kArtikel
                          AND tpreis.kKundengruppe = ' . $this->getModel()->getCustomerGroupID() . '
                       JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                          AND tpreisdetail.nAnzahlAb = 0
                          AND tpreisdetail.fVKNetto > 0';
        }

        if ($this->config['exportformate_beschreibung'] === 'Y') {
            $where .= " AND tartikel.cBeschreibung != ''";
        }

        $condition = 'AND (tartikel.dErscheinungsdatum IS NULL OR NOT (DATE(tartikel.dErscheinungsdatum) > CURDATE()))';
        $conf      = Shop::getSettings([\CONF_GLOBAL]);
        if (($conf['global']['global_erscheinende_kaeuflich'] ?? 'N') === 'Y') {
            $condition = "AND (
                tartikel.dErscheinungsdatum IS NULL 
                OR NOT (DATE(tartikel.dErscheinungsdatum) > CURDATE())
                OR (
                    DATE(tartikel.dErscheinungsdatum) > CURDATE()
                    AND (tartikel.cLagerBeachten = 'N' 
                        OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = 'Y')
                )
            )";
        }

        if ($countOnly === true) {
            $select = 'COUNT(*) AS nAnzahl';
        } else {
            $queue  = $this->getQueue();
            $select = 'tartikel.kArtikel';
            $limit  = ' ORDER BY tartikel.kArtikel';
            if ($queue !== null) {
                $limit     .= ' LIMIT ' . $queue->taskLimit;
                $condition .= ' AND tartikel.kArtikel > ' . $this->getQueue()->lastProductID;
            }
        }

        return 'SELECT ' . $select . "
            FROM tartikel
            LEFT JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . \FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            " . $join . '
            LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = ' . $this->getModel()->getCustomerGroupID() . '
            WHERE tartikelattribut.kArtikelAttribut IS NULL' . $where . '
                AND tartikelsichtbarkeit.kArtikel IS NULL ' . $condition . $limit;
    }

    /**
     * @inheritdoc
     */
    public function init(int $exportID): void
    {
    }

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @inheritdoc
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function setQueue(QueueEntry $queue): void
    {
        $this->queue = $queue;
    }

    /**
     * @inheritdoc
     */
    public function getQueue(): ?QueueEntry
    {
        return $this->queue;
    }

    /**
     * @inheritdoc
     */
    public function getSmarty(): ExportSmarty
    {
        return $this->smarty;
    }

    /**
     * @inheritdoc
     */
    public function setSmarty(ExportSmarty $smarty): void
    {
        $this->smarty = $smarty;
    }

    /**
     * @inheritdoc
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @inheritdoc
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getWriter(): ExportWriterInterface
    {
        return $this->writer;
    }

    /**
     * @inheritdoc
     */
    public function setWriter(?ExportWriterInterface $writer): void
    {
        $this->writer = $writer;
    }

    /**
     * @inheritdoc
     */
    public function getStartedAt(): ?float
    {
        return $this->startedAt;
    }

    /**
     * @inheritdoc
     */
    public function setStartedAt(float $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @inheritdoc
     */
    public function update(): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function setZuletztErstellt($lastCreated): ExporterInterface
    {
        return $this;
    }
}
