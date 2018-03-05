<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use JTL\ProcessingHandler\NiceDBHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

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
