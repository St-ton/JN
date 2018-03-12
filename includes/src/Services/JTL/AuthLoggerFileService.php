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

/**
 * Class AuthLoggerFileService
 * @package Services\JTL
 */
class AuthLoggerFileService extends AuthLoggerService
{
    const LOGFILE = PFAD_LOGFILES . 'auth.log';

    /**
     * AuthLoggerService constructor.
     */
    public function __construct()
    {
        $handler      = (new StreamHandler(self::LOGFILE, Logger::INFO))
            ->setFormatter(new LineFormatter(null, null, false, true));
        $this->logger = new Logger('auth', [$handler], [new PsrLogMessageProcessor()]);
    }
}
