<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Interface AuthLoggerServiceInterface
 *
 * @package Services\JTL
 */
interface AuthLoggerServiceInterface
{
    /**
     * @return bool
     */
    public function log() : bool;

    /**
     * @param string $ip
     * @return $this
     */
    public function setIP(string $ip);

    /**
     * @param string $user
     * @return $this
     */
    public function setUser(string $user);

    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code);
}
