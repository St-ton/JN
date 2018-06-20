<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Interface PasswordServiceInterface
 * @package Services\JTL
 */
interface PasswordServiceInterface
{
    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public function generate($length): string;

    /**
     * @param string $password
     * @return string
     * @throws \Exception
     */
    public function hash($password): string;

    /**
     * @param string $password
     * @param string $hash
     * @return string|bool
     * @throws \Exception
     */
    public function verify($password, $hash);

    /**
     * @param string $hash
     * @return bool
     * @throws \Exception
     */
    public function needsRehash($hash): bool;

    /**
     * @param string $hash
     * @return mixed
     */
    public function getInfo($hash): array;
}
