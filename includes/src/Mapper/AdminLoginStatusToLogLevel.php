<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Mapper;

use Monolog\Logger;

/**
 * Class AdminLoginStatusToLogLevel
 * @package Mapper
 */
class AdminLoginStatusToLogLevel
{
    /**
     * @param int $code
     * @return int
     */
    public function map($code): int
    {
        switch ($code) {
            case \AdminLoginStatus::LOGIN_OK:
                return Logger::INFO;
            case \AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED:
                return Logger::ALERT;
            case \AdminLoginStatus::ERROR_NOT_AUTHORIZED:
            case \AdminLoginStatus::ERROR_INVALID_PASSWORD:
            case \AdminLoginStatus::ERROR_USER_NOT_FOUND:
            case \AdminLoginStatus::ERROR_USER_DISABLED:
            case \AdminLoginStatus::ERROR_LOGIN_EXPIRED:
            case \AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED:
            case \AdminLoginStatus::ERROR_UNKNOWN:
            default:
                return Logger::WARNING;
        }
    }

    /**
     * @param int $code
     * @return int
     */
    public function mapToJTLLog($code): int
    {
        switch ($code) {
            case \AdminLoginStatus::LOGIN_OK:
            case Logger::INFO:
                return \JTLLOG_LEVEL_NOTICE;
            case \AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED:
            case Logger::ALERT:
            case \AdminLoginStatus::ERROR_NOT_AUTHORIZED:
            case \AdminLoginStatus::ERROR_INVALID_PASSWORD:
            case \AdminLoginStatus::ERROR_USER_NOT_FOUND:
            case \AdminLoginStatus::ERROR_USER_DISABLED:
            case \AdminLoginStatus::ERROR_LOGIN_EXPIRED:
            case \AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED:
            case \AdminLoginStatus::ERROR_UNKNOWN:
            case Logger::WARNING:
            default:
                return \JTLLOG_LEVEL_ERROR;
        }
    }
}
