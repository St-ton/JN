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
    public function generate($length);

    /**
     * @param string $password
     * @return string
     * @throws \Exception
     */
    public function hash($password);

    /**
     * @param string $password
     * @param string $hash
     * @return mixed
     * @throws \Exception
     */
    public function verify($password, $hash);

    /**
     * @param string $hash
     * @return bool
     * @throws \Exception
     */
    public function needsRehash($hash);

    /**
     * @param $hash
     * @return mixed
     */
    public function getInfo($hash);
}