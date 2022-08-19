<?php declare(strict_types=1);

namespace JTL\ProcessingHandler;

use JTL\DB\DbInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class NiceDBHandler
 * @package JTL\ProcessingHandler
 */
class NiceDBHandler extends AbstractProcessingHandler
{
    /**
     * NiceDBHandler constructor.
     * @param DbInterface $db
     * @param int         $level
     * @param bool        $bubble
     */
    public function __construct(private DbInterface $db, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write(array $record): void
    {
        $context = isset($record['context'][0]) && \is_numeric($record['context'][0])
            ? (int)$record['context'][0]
            : 0;
        if (!$this->db->isConnected()) {
            $this->db->reInit();
        }

        $this->db->insert(
            'tjtllog',
            (object)[
                'cKey'      => $record['channel'],
                'nLevel'    => $record['level'],
                'cLog'      => $record['formatted'],
                'kKey'      => $context,
                'dErstellt' => $record['datetime']->format('Y-m-d H:i:s'),
            ]
        );
    }
}
