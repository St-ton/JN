<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\ProcessingHandler;

use DB\DbInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class NiceDBHandler
 * @package Services\JTL
 */
class NiceDBHandler extends AbstractProcessingHandler
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * NiceDBHandler constructor.
     * @param DbInterface $db
     * @param int         $level
     * @param bool        $bubble
     */
    public function __construct(DbInterface $db, $level = Logger::DEBUG, $bubble = true)
    {
        $this->db = $db;
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $this->db->insert(
            'tjtllog',
            (object)[
                'cKey'      => $record['channel'],
                'nLevel'    => $record['level'],
                'cLog'      => $record['formatted'],
                'dErstellt' => $record['datetime']->format('Y-m-d H:i:s'),
            ]);
    }
}
