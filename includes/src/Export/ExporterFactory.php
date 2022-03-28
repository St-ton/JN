<?php declare(strict_types=1);

namespace JTL\Export;

use Exception;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ExporterFactory
 * @package JTL\Export
 */
class ExporterFactory
{
    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var JTLCacheInterface
     */
    private JTLCacheInterface $cache;

    /**
     * @var ExportWriterInterface|null
     */
    private ?ExportWriterInterface $writer;

    /**
     * @param DbInterface                $db
     * @param LoggerInterface            $logger
     * @param JTLCacheInterface          $cache
     * @param ExportWriterInterface|null $writer
     */
    public function __construct(
        DbInterface $db,
        LoggerInterface $logger,
        JTLCacheInterface $cache,
        ?ExportWriterInterface $writer = null
    ) {
        $this->db     = $db;
        $this->logger = $logger;
        $this->cache  = $cache;
        $this->writer = $writer;
    }

    /**
     * @param int $exportID
     * @return ExporterInterface
     */
    public function getExporter(int $exportID): ExporterInterface
    {
        $exporter = new FormatExporter($this->db, $this->logger, $this->cache, $this->writer);
        try {
            $model = Model::load(['id' => $exportID], $this->db, Model::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Cannot find export with id ' . $exportID);
        }

        \executeHook(\HOOK_EXPORT_FACTORY_GET_EXPORTER, [
            'exportID' => $exportID,
            'exporter' => &$exporter,
            'model'    => $model
        ]);
        $exporter->setDB($this->db);
        $exporter->setCache($this->cache);
        $exporter->setLogger($this->logger);
        $exporter->setWriter($this->writer);
        $exporter->setModel($model);

        return $exporter;
    }
}
