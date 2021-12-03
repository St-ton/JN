<?php declare(strict_types=1);

namespace JTL\Export;

use Exception;
use InvalidArgumentException;
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
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExportWriterInterface|null
     */
    private $writer;

    /**
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
     * @param int $exportID
     * @return ExporterInterface
     */
    public function getExporter(int $exportID): ExporterInterface
    {
        $exporter = new FormatExporter($this->db, $this->logger, $this->writer);

        \executeHook(\HOOK_EXPORT_FACTORY_GET_EXPORTER, [
            'exportID' => $exportID,
            'exporter' => &$exporter
        ]);
        $exporter->setDB($this->db);
        $exporter->setLogger($this->logger);
        $exporter->setWriter($this->writer);
        try {
            $model = Model::load(['id' => $exportID], $this->db, Model::ON_NOTEXISTS_FAIL);
            $exporter->setModel($model);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Cannot find export with id ' . $exportID);
        }

        return $exporter;
    }
}
