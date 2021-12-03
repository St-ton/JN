<?php declare(strict_types=1);

namespace JTL\Export;

use Exception;
use JTL\Smarty\ExportSmarty;

/**
 * Interface ExportWriterInterface
 * @package JTL\Export
 */
interface ExportWriterInterface
{
    /**
     * @param ExportSmarty $smarty
     * @param Model        $model
     * @param array        $config
     */
    public function __construct(ExportSmarty $smarty, Model $model, array $config);

    /**
     * @throws Exception
     */
    public function start(): void;

    /**
     * @return int
     */
    public function writeHeader(): int;

    /**
     * @return int
     * @throws \SmartyException
     */
    public function writeFooter(): int;

    /**
     * @param string $data
     * @return int
     */
    public function writeContent(string $data): int;

    /**
     * @return bool
     */
    public function close(): bool;

    /**
     * @return bool
     */
    public function finish(): bool;

    public function deleteOldExports(): void;

    public function deleteOldTempFile(): void;

    /**
     * @return ExportWriterInterface
     */
    public function split(): ExportWriterInterface;
}
