<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AdminLoginStatus
 */
abstract class AdminLoginStatus
{
    const __default = self::ERROR_UNKNOWN;

    const LOGIN_OK = 1;

    const ERROR_NOT_AUTHORIZED = 0;

    const ERROR_INVALID_PASSWORD = -1;

    const ERROR_INVALID_PASSWORD_LOCKED = -2;

    const ERROR_USER_NOT_FOUND = -3;

    const ERROR_USER_DISABLED = -4;

    const ERROR_LOGIN_EXPIRED = -5;

    const ERROR_TWO_FACTOR_AUTH_EXPIRED = -6;

    const ERROR_UNKNOWN = -7;
}
