<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\ProcessingHandler;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class NiceDBHandler
 * @package Services\JTL
 */
class NiceDBHandler extends AbstractProcessingHandler
{
    /**
     * @var \NiceDB
     */
    private $db;

    /**
     * NiceDBHandler constructor.
     * @param \NiceDB $db
     * @param int     $level
     * @param bool    $bubble
     */
    public function __construct(\NiceDB $db, $level = Logger::DEBUG, $bubble = true)
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
