<?php declare(strict_types=1);

namespace JTL\Mapper;

use JTL\Backend\AdminLoginStatus;
use Monolog\Logger;

/**
 * Class AdminLoginStatusToLogLevel
 * @package JTL\Mapper
 */
class AdminLoginStatusToLogLevel
{
    /**
     * @param int $code
     * @return int
     */
    public function map(int $code): int
    {
        return match ($code) {
            AdminLoginStatus::LOGIN_OK                      => Logger::INFO,
            AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED => Logger::ALERT,
            default                                         => Logger::WARNING,
        };
    }

    /**
     * @param int $code
     * @return int
     */
    public function mapToJTLLog(int $code): int
    {
        return match ($code) {
            AdminLoginStatus::LOGIN_OK, Logger::INFO => \JTLLOG_LEVEL_NOTICE,
            default                                  => \JTLLOG_LEVEL_ERROR,
        };
    }
}
