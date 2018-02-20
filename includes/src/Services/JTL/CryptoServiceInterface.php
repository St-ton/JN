<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;


interface CryptoServiceInterface
{
    /**
     * @param $bytesAmount
     * @return mixed
     * @throws \Exception
     */
    public function randomBytes($bytesAmount);

    /**
     * @param int $bytesAmount
     * @return string
     * @throws \Exception
     */
    public function randomString($bytesAmount);

    /**
     * @param int $min
     * @param int $max
     * @return int
     * @throws \Exception
     */
    public function randomInt($min, $max);
}