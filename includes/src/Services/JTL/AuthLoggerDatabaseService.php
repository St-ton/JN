<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class AuthLoggerDatabaseService
 * @package Services\JTL
 */
class AuthLoggerDatabaseService extends AuthLoggerService
{
    const LOGFILE = PFAD_LOGFILES . 'auth.log';

    /**
     * AuthLoggerService constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger('auth');
        $handler      = (new NiceDBHandler(\Shop::DB(), Logger::INFO))
            ->setFormatter(new LineFormatter(null, null, false, true));
        $this->logger->pushHandler($handler)
                     ->pushProcessor(new PsrLogMessageProcessor());
    }
}

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